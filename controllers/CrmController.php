<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Contact;
use app\models\Deal;

class CrmController extends Controller
{
    /**
     * Список / детальная
     */
    public function actionIndex($type = null, $id = null)
    {
        $items  = [];
        $current = null;

        if ($type === 'contacts') {
            $items   = Contact::loadAll();           // массив записей (array of arrays)
            $current = $id ? Contact::findById($id) : null; // модель Contact или null
        } elseif ($type === 'deals') {
            $items   = Deal::loadAll();
            $current = $id ? Deal::findById($id) : null;    // модель Deal или null
        }

        return $this->render('index', [
            'type'    => $type,
            'items'   => $items,
            'current' => $current,
        ]);
    }

    /**
     * Создать (render form или сохранить)
     * URL: /crm/create?type=contacts  или ?type=deals
     */
    public function actionCreate($type)
    {
        if ($type !== 'contacts' && $type !== 'deals') {
            throw new NotFoundHttpException('Unknown type');
        }

        $model = $type === 'contacts' ? new Contact() : new Deal();

        // Подготовка опций для множественного выбора
        [$contactOptions, $dealOptions] = $this->getOptions();

        // Предзаполнить множественные поля пустыми массивами (чтобы чекбоксы корректно работали)
        if ($type === 'contacts') {
            $model->deals = [];
        } else {
            $model->contacts = [];
        }

        if ($model->load(Yii::$app->request->post())) {
            // нормализуем массивы ID
            if ($type === 'contacts') {
                $model->deals = array_map('intval', (array)$model->deals);
                $ok = $model->save();
                if ($ok) {
                    // после создания — добавить обратные ссылки в сделки
                    $this->syncContactDeals((int)$model->id, (array)$model->deals, []);
                }
            } else {
                $model->contacts = array_map('intval', (array)$model->contacts);
                $ok = $model->save();
                if ($ok) {
                    // после создания — добавить обратные ссылки в контакты
                    $this->syncDealContacts((int)$model->id, (array)$model->contacts, []);
                }
            }

            if (!empty($ok)) {
                return $this->redirect(['crm/index', 'type' => $type, 'id' => $model->id]);
            }
        }

        // Рендерим форму: у тебя есть _form_contact.php и _form_deal.php, используем их
        return $this->render($type === 'contacts' ? '_form_contact' : '_form_deal', [
            'model' => $model,
            'type' => $type,
            'contactOptions' => $contactOptions,
            'dealOptions' => $dealOptions,
        ]);
    }

    /**
     * Редактировать
     * URL: /crm/update?type=contacts&id=15
     */
    public function actionUpdate($type, $id)
    {
        if ($type !== 'contacts' && $type !== 'deals') {
            throw new NotFoundHttpException('Unknown type');
        }

        // получим "сырые" массивы, чтобы взять предыдущие связи (prevLinks)
        if ($type === 'contacts') {
            $all = Contact::loadAll();
            $row = $this->findInArrayById($all, $id);
            if (!$row) throw new NotFoundHttpException('Contact not found');
            $model = Contact::findById($id);
            // гарантируем, что в модели лежат сырые ID (в некоторых старых реализациях findById мог менять это)
            $model->deals = (array)($row['deals'] ?? []);
            $prevLinks = (array)$row['deals'];
        } else {
            $all = Deal::loadAll();
            $row = $this->findInArrayById($all, $id);
            if (!$row) throw new NotFoundHttpException('Deal not found');
            $model = Deal::findById($id);
            $model->contacts = (array)($row['contacts'] ?? []);
            $prevLinks = (array)$row['contacts'];
        }

        [$contactOptions, $dealOptions] = $this->getOptions();

        if ($model->load(Yii::$app->request->post())) {
            if ($type === 'contacts') {
                $model->deals = array_map('intval', (array)$model->deals);
                $ok = $model->save();
                if ($ok) {
                    $this->syncContactDeals((int)$model->id, (array)$model->deals, (array)$prevLinks);
                }
            } else {
                $model->contacts = array_map('intval', (array)$model->contacts);
                $ok = $model->save();
                if ($ok) {
                    $this->syncDealContacts((int)$model->id, (array)$model->contacts, (array)$prevLinks);
                }
            }

            if (!empty($ok)) {
                return $this->redirect(['crm/index', 'type' => $type, 'id' => $model->id]);
            }
        }

        return $this->render($type === 'contacts' ? '_form_contact' : '_form_deal', [
            'model' => $model,
            'type' => $type,
            'contactOptions' => $contactOptions,
            'dealOptions' => $dealOptions,
        ]);
    }

    /**
     * Удалить (только POST — ссылки в index() уже настроены с data-method=post)
     */
    public function actionDelete($type, $id)
    {
        if (Yii::$app->request->method !== 'POST') {
            throw new NotFoundHttpException('Invalid method');
        }

        if ($type === 'contacts') {
            $row = $this->findInArrayById(Contact::loadAll(), $id);
            if (!$row) throw new NotFoundHttpException('Contact not found');

            $model = Contact::findById($id);
            if ($model) {
                // убрать контакт из всех сделок перед удалением
                $this->syncContactDeals((int)$model->id, [], (array)($row['deals'] ?? []));
                $model->delete();
            }
        } elseif ($type === 'deals') {
            $row = $this->findInArrayById(Deal::loadAll(), $id);
            if (!$row) throw new NotFoundHttpException('Deal not found');

            $model = Deal::findById($id);
            if ($model) {
                // убрать сделку из всех контактов перед удалением
                $this->syncDealContacts((int)$model->id, [], (array)($row['contacts'] ?? []));
                $model->delete();
            }
        } else {
            throw new NotFoundHttpException('Unknown type');
        }

        return $this->redirect(['crm/index', 'type' => $type]);
    }

    /* ================= ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ================= */

    /**
     * Подготовить опции для чекбоксов/списков: [id => "id — Имя Фамилия"] и [id => "id — title"]
     */
    private function getOptions(): array
    {
        $contactsRaw = Contact::loadAll();
        $dealsRaw    = Deal::loadAll();

        $contactOptions = [];
        foreach ($contactsRaw as $c) {
            $contactOptions[(int)$c['id']] = (($c['id'] ?? '') . ' — ' . ($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
        }

        $dealOptions = [];
        foreach ($dealsRaw as $d) {
            $dealOptions[(int)$d['id']] = (($d['id'] ?? '') . ' — ' . ($d['title'] ?? ($d['name'] ?? '(без названия)')));
        }

        return [$contactOptions, $dealOptions];
    }

    /**
     * Найти в "сырых" массивах элемент по id и вернуть строку (массив)
     */
    private function findInArrayById(array $all, $id): ?array
    {
        foreach ($all as $row) {
            if ((int)($row['id'] ?? 0) === (int)$id) return $row;
        }
        return null;
    }

    /**
     * Синхронизация: контакт поменял(а) свои deals.
     * $newDealIds — итоговый список сделок у контакта
     * $prevDealIds — предыдущий список (если известен) — для корректного удаления/добавления
     */
    private function syncContactDeals(int $contactId, array $newDealIds, array $prevDealIds = []): void
    {
        $new  = array_unique(array_map('intval', $newDealIds));
        $prev = array_unique(array_map('intval', $prevDealIds));

        $toAdd    = array_diff($new, $prev);
        $toRemove = array_diff($prev, $new);

        // Добавляем контакт в новые сделки
        foreach ($toAdd as $dealId) {
            $deal = Deal::findById($dealId);
            if ($deal) {
                $current = array_map('intval', (array)$deal->contacts);
                if (!in_array($contactId, $current, true)) {
                    $current[] = $contactId;
                    $deal->contacts = array_values($current);
                    $deal->save();
                }
            }
        }

        // Убираем контакт из сделок, от которых отказались
        foreach ($toRemove as $dealId) {
            $deal = Deal::findById($dealId);
            if ($deal) {
                $deal->contacts = array_values(array_diff(array_map('intval', (array)$deal->contacts), [$contactId]));
                $deal->save();
            }
        }
    }

    /**
     * Синхронизация: сделка поменяла свои contacts.
     * $newContactIds — итоговый список контактов у сделки
     * $prevContactIds — предыдущий список
     */
    private function syncDealContacts(int $dealId, array $newContactIds, array $prevContactIds = []): void
    {
        $new  = array_unique(array_map('intval', $newContactIds));
        $prev = array_unique(array_map('intval', $prevContactIds));

        $toAdd    = array_diff($new, $prev);
        $toRemove = array_diff($prev, $new);

        // Добавляем сделку в контакты
        foreach ($toAdd as $contactId) {
            $contact = Contact::findById($contactId);
            if ($contact) {
                $current = array_map('intval', (array)$contact->deals);
                if (!in_array($dealId, $current, true)) {
                    $current[] = $dealId;
                    $contact->deals = array_values($current);
                    $contact->save();
                }
            }
        }

        // Убираем сделку из контактов
        foreach ($toRemove as $contactId) {
            $contact = Contact::findById($contactId);
            if ($contact) {
                $contact->deals = array_values(array_diff(array_map('intval', (array)$contact->deals), [$dealId]));
                $contact->save();
            }
        }
    }
}
