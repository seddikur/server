<?php

declare(strict_types=1);

namespace app\modules\setting\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Ключ-значение настроек сайта.
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string|null $description
 * @property string $created_at
 * @property string $updated_at
 */
class Setting extends ActiveRecord
{
    public const KEY_YANDEX_METRIKA_COUNTER = 'yandex_metrika_counter';
    public const KEY_YANDEX_WEBMASTER_VERIFICATION = 'yandex_webmaster_verification';
    public const KEY_GEOIP_ENABLED = 'geoip_enabled';
    public const KEY_GEOIP_DB_PATH = 'geoip_db_path';
    public const KEY_NARODMON_API_KEY = 'narodmon_api_key';
    public const KEY_NARODMON_APP_UUID = 'narodmon_app_uuid';
    public const KEY_NARODMON_NEARBY_LAT = 'narodmon_nearby_lat';
    public const KEY_NARODMON_NEARBY_LON = 'narodmon_nearby_lon';
    public const KEY_NARODMON_NEARBY_RADIUS = 'narodmon_nearby_radius';
    public const KEY_NARODMON_PUBS_LIMIT = 'narodmon_pubs_limit';

    public const CATEGORY_GENERAL = 'general';

    /** @var string[] ключи, которые в форме — checkbox 0/1 */
    private const BOOLEAN_KEYS = [
        self::KEY_GEOIP_ENABLED,
    ];

    public static function tableName(): string
    {
        return '{{%settings}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            ['key', 'required'],
            ['key', 'string', 'max' => 255],
            ['key', 'unique'],
            [['value'], 'string'],
            [['description'], 'string', 'max' => 512],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'key' => 'Ключ',
            'value' => 'Значение',
            'description' => 'Описание',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    public function getLabel(): string
    {
        if ($this->description !== null && $this->description !== '') {
            return $this->description;
        }

        return $this->key;
    }

    public function isBoolean(): bool
    {
        return in_array($this->key, self::BOOLEAN_KEYS, true);
    }

    /** @return Setting[] */
    public static function findAllOrdered(): array
    {
        if (!static::tableExists()) {
            return [];
        }

        return static::find()->orderBy(['key' => SORT_ASC])->all();
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (!static::tableExists()) {
            return $default;
        }

        $row = static::findOne(['key' => $key]);

        return $row !== null ? $row->value : $default;
    }

    public static function setValue(string $key, mixed $value, ?string $description = null): void
    {
        $row = static::findOne(['key' => $key]) ?? new static(['key' => $key]);
        $row->value = $value === null ? null : (string) $value;
        if ($description !== null) {
            $row->description = $description;
        }
        $row->save(false);
    }

    public static function tableExists(): bool
    {
        try {
            return Yii::$app->db->schema->getTableSchema(static::tableName(), true) !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
