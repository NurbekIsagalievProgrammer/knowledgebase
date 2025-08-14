<?php
namespace app\models;

use Yii;
use yii\base\Model;

class Contact extends Model
{
    public $id;
    public $first_name;
    public $last_name;
    public $deals = []; // массив ID сделок

    // === файл данных и кэш ===
    protected static $dataFile = '@app/runtime/data/contacts.json';
    protected static $cache = null;

    public function rules()
    {
        return [
            [['first_name'], 'required'],      // по ТЗ имя — обязательное
            [['last_name'], 'safe'],
            [['id'], 'integer'],
            [['deals'], 'each', 'rule' => ['integer']],
        ];
    }

    /** Загрузка всех контактов (один раз за запрос) */
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

    /** Найти контакт по ID и вернуть МОДЕЛЬ */
    public static function findById($id): ?self
    {
        foreach (self::loadAll() as $row) {
            if ((int)($row['id'] ?? 0) === (int)$id) {
                $m = new self();
                $m->setAttributes([
                    'id'         => $row['id'] ?? null,
                    'first_name' => $row['first_name'] ?? null,
                    'last_name'  => $row['last_name'] ?? null,
                    'deals'      => $row['deals'] ?? [],
                ], false);
                return $m;
            }
        }
        return null;
    }

    /** Сохранить ВСЕ контакты разом */
    public static function saveAll(array $contacts): void
    {
        $path = Yii::getAlias(self::$dataFile);
        file_put_contents($path, json_encode(array_values($contacts), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        self::$cache = array_values($contacts);
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

    /** Удобный доступ: сделки контакта с названиями/суммами */
 public function getDeals()
{
    $result = [];
    if (!empty($this['deals'])) {
        $dealIds = is_array($this['deals']) ? $this['deals'] : explode(',', $this['deals']);
        foreach ($dealIds as $id) {
            $deal = Deal::findOne(trim($id));
            if ($deal) {
                $result[] = $deal['title']; // название сделки
            }
        }
    }
    return $result;
}


    /** Что пишем обратно в JSON */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id'         => (int)$this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'deals'      => array_values(array_map('intval', (array)$this->deals)),
        ];
    }
}
