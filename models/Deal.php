<?php
namespace app\models;

use Yii;
use yii\base\Model;

class Deal extends Model
{
    public $id;
    public $title;      // по ТЗ "Наименование"
    public $amount;     // сумма
    public $status;     // можно: new | in_progress | closed (или свои значения)
    public $contacts = []; // массив ID контактов

    // === файл данных и кэш ===
    protected static $dataFile = '@app/runtime/data/deals.json';
    protected static $cache = null;

    public function rules()
    {
        return [
            [['title'], 'required'],      // по ТЗ наименование — обязательное
            [['amount'], 'number'],
            [['status'], 'safe'],
           [['contacts'], 'each', 'rule' => ['integer']], 
            [['id'], 'integer'],
        ];
    }

    /** Загрузка всех сделок (один раз за запрос) */
    public static function loadAll(): array
    {
        if (self::$cache === null) {
            $path = Yii::getAlias(self::$dataFile);
            self::$cache = file_exists($path)
                ? (json_decode(file_get_contents($path), true) ?: [])
                : [];
        }
        return self::$cache;
    }

    /** Найти сделку по ID и вернуть МОДЕЛЬ */
    public static function findById($id): ?self
    {
        foreach (self::loadAll() as $row) {
            if ((int)($row['id'] ?? 0) === (int)$id) {
                $m = new self();
                $m->setAttributes([
                    'id'       => $row['id'] ?? null,
                    'title'    => $row['title'] ?? ($row['name'] ?? null), // на случай старого ключа 'name'
                    'amount'   => $row['amount'] ?? null,
                    'status'   => $row['status'] ?? null,
                    'contacts' => $row['contacts'] ?? [],
                ], false);
                return $m;
            }
        }
        return null;
    }

    /** Сохранить ВСЕ сделки разом */
    public static function saveAll(array $deals): void
    {
        $path = Yii::getAlias(self::$dataFile);
        file_put_contents($path, json_encode(array_values($deals), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        self::$cache = array_values($deals);
    }

    /** Сохранить текущую модель в JSON (создать/обновить) */
    public function save($runValidation = true, $attributeNames = null): bool
    {
        if ($runValidation && !$this->validate()) return false;

        $all = self::loadAll();
        $updated = false;

        // обновление
        foreach ($all as &$row) {
            if ((int)($row['id'] ?? 0) === (int)$this->id) {
                $row = $this->toArray();
                $updated = true;
                break;
            }
        }
        unset($row);

        // создание
        if (!$updated) {
            if (!$this->id) {
                $this->id = count($all) ? (max(array_column($all, 'id')) + 1) : 1;
            }
            $all[] = $this->toArray();
        }

        self::saveAll($all);
        return true;
    }

    /** Удалить текущую модель из JSON */
    public function delete(): bool
    {
        $all = array_filter(self::loadAll(), fn($r) => (int)($r['id'] ?? 0) !== (int)$this->id);
        self::saveAll(array_values($all));
        return true;
    }

    /** Удобный доступ: контакты сделки с именами/фамилиями */
   public function getContacts()
{
    $result = [];
    if (!empty($this['contacts'])) {
        $contactIds = is_array($this['contacts']) ? $this['contacts'] : explode(',', $this['contacts']);
        foreach ($contactIds as $id) {
            $contact = Contact::findById(trim($id));
            if ($contact) {
                $result[] = $contact['first_name'] . ' ' . $contact['last_name']; // ФИО
            }
        }
    }
    return $result;
}


    /** Что пишем обратно в JSON */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id'       => (int)$this->id,
            'title'    => $this->title, // храним в JSON именно 'title'
            'amount'   => $this->amount,
            'status'   => $this->status,
            'contacts' => array_values(array_map('intval', (array)$this->contacts)),
        ];
    }
}
