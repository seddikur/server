<?php

declare(strict_types=1);

namespace app\services\avito;

use app\exceptions\avito\AvitoException;
use app\models\AvitoLocation;
use Yii;
use yii\base\Component;

/**
 * Синхронизация справочника локаций Avito из catalog-locations.xml.
 */
class AvitoLocationSyncService extends Component
{
    /** URL справочника локаций Avito */
    public string $catalogUrl = 'https://www.avito.st/s/openapi/catalog-locations.xml';

    /**
     * Скачивает и синхронизирует локации.
     *
     * @return int Количество обработанных записей
     */
    public function sync(?string $source = null): int
    {
        $xmlString = $source !== null
            ? $this->loadFromFile($source)
            : $this->downloadCatalog();

        return $this->parseAndUpsert($xmlString);
    }

    private function downloadCatalog(): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->catalogUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $raw === '') {
            throw new AvitoException('Не удалось скачать справочник локаций Avito: ' . $error);
        }

        return (string) $raw;
    }

    private function loadFromFile(string $path): string
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new AvitoException('Файл справочника локаций не найден или недоступен: ' . $path);
        }

        return (string) file_get_contents($path);
    }

    /**
     * Разбирает XML и делает upsert по avito_id.
     */
    private function parseAndUpsert(string $xmlString): int
    {
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString);
        libxml_use_internal_errors($previous);

        if ($xml === false) {
            throw new AvitoException('Некорректный XML справочника локаций Avito');
        }

        $count = 0;
        $now = time();

        foreach ($xml->Location as $node) {
            $avitoId = (int) $node['Id'];
            if ($avitoId <= 0) {
                continue;
            }

            $name = trim((string) $node->Name);
            $parentId = isset($node['ParentId']) ? (int) $node['ParentId'] : null;
            $type = $this->resolveType($parentId, (string) ($node['Type'] ?? ''));

            $location = AvitoLocation::findOne(['avito_id' => $avitoId]) ?? new AvitoLocation();
            $location->avito_id = $avitoId;
            $location->name = $name;
            $location->full_name = $this->buildFullName($node, $name);
            $location->type = $type;
            $location->parent_id = $parentId;
            $location->region_avito_id = $type === AvitoLocation::TYPE_REGION ? $avitoId : $parentId;
            $location->slug = $location->slug !== '' && $location->slug !== null
                ? $location->slug
                : $this->transliterate($name);
            $location->is_active = true;
            $location->updated_at = $now;
            $location->save(false);

            $count++;
        }

        return $count;
    }

    private function resolveType(?int $parentId, string $rawType): string
    {
        $rawType = strtolower(trim($rawType));

        if ($rawType !== '' && in_array($rawType, [
            AvitoLocation::TYPE_REGION,
            AvitoLocation::TYPE_CITY,
            AvitoLocation::TYPE_DISTRICT,
        ], true)) {
            return $rawType;
        }

        return $parentId === null || $parentId === 0
            ? AvitoLocation::TYPE_REGION
            : AvitoLocation::TYPE_CITY;
    }

    private function buildFullName(\SimpleXMLElement $node, string $name): string
    {
        $region = isset($node->Region) ? trim((string) $node->Region) : '';

        if ($region !== '' && $region !== $name) {
            return $region . ', ' . $name;
        }

        return $name;
    }

    /**
     * Транслитерация кириллицы в латиницу для slug.
     */
    public function transliterate(string $value): string
    {
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ];

        $value = mb_strtolower($value, 'UTF-8');
        $value = strtr($value, $map);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;

        return trim($value, '-');
    }
}
