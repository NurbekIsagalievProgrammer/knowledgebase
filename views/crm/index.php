<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Contact;
use app\models\Deal;
?>

<h1>CRM Панель</h1>

<!-- Меню -->
<ul>
    <li><a href="<?= Url::to(['crm/index', 'type' => 'contacts']) ?>">Контакты</a></li>
    <li><a href="<?= Url::to(['crm/index', 'type' => 'deals']) ?>">Сделки</a></li>
</ul>

<div style="display: flex; gap: 20px; margin-top: 20px;">

    <?php if (!empty($type)): ?>
        <!-- Список элементов -->
        <div style="width: 30%; border-right: 1px solid #ccc; padding-right: 10px;">
            <h3><?= ucfirst($type) ?></h3>

            <!-- Кнопка Создать -->
            <p>
                <?= Html::a('➕ Создать', ['crm/create', 'type' => $type], ['class' => 'btn btn-success']) ?>
            </p>

            <ul>
                <?php foreach($items as $item): ?>
                    <?php $id = is_array($item) ? ($item['id'] ?? null) : ($item->id ?? null); ?>
                    <li>
                        <a href="<?= Url::to(['crm/index', 'type' => $type, 'id' => $id]) ?>">
                            <?= $id ?> - 
                            <?php if($type === 'contacts'): ?>
                                <?= is_array($item) ? ($item['first_name'] ?? '') : ($item->first_name ?? '') ?> 
                                <?= is_array($item) ? ($item['last_name'] ?? '') : ($item->last_name ?? '') ?>
                            <?php else: ?>
                                <?= is_array($item) ? ($item['title'] ?? '(без названия)') : ($item->title ?? '(без названия)') ?>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Детали элемента -->
        <div style="width: 70%; padding-left: 10px;">
            <?php if($current): ?>
                <h3>Детали <?= $type === 'contacts' ? 'контакта' : 'сделки' ?></h3>

                <!-- Кнопки редактирования и удаления -->
                <p>
                    <?= Html::a('✏️ Редактировать', ['crm/update', 'type' => $type, 'id' => $current->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('🗑 Удалить', ['crm/delete', 'type' => $type, 'id' => $current->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Точно удалить этот элемент?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </p>

                <table border="1" cellpadding="5">
                    <?php foreach($current->attributes as $field => $value): ?>
                        <tr>
                            <td><b><?= $field ?></b></td>
                            <td>
                                <?php
                                if ($field === 'deals') {
                                    $titles = [];
                                    foreach ((array)$value as $dealId) {
                                        $deal = Deal::findById($dealId);
                                        if ($deal) {
                                            $titles[] = $deal['title'];
                                        }
                                    }
                                    echo implode(', ', $titles);
                                } elseif ($field === 'contacts') {
                                    $names = [];
                                    foreach ((array)$value as $contactId) {
                                        $contact = Contact::findById($contactId);
                                        if ($contact) {
                                            $names[] = $contact['first_name'] . ' ' . $contact['last_name'];
                                        }
                                    }
                                    echo implode(', ', $names);
                                } else {
                                    echo is_array($value) ? implode(', ', $value) : $value;
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
