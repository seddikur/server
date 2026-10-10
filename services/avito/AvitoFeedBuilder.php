<?php

declare(strict_types=1);

namespace app\services\avito;

use app\models\AvitoService;
use yii\base\Component;

/**
 * Генератор XML-фида Автозагрузки для услуг Avito.
 *
 * Один <Ad> на одну запись AvitoService.
 */
class AvitoFeedBuilder extends Component
{
    /** Срок действия объявления в фиде (дней) */
    public int $datePeriodDays = 30;

    /**
     * @param AvitoService[] $services
     */
    public function build(array $services): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $ads = $document->createElement('Ads');
        $ads->setAttribute('formatVersion', '3');
        $ads->setAttribute('target', 'Avito.ru');
        $document->appendChild($ads);

        $dateBegin = date('Y-m-d');
        $dateEnd = date('Y-m-d', strtotime('+' . $this->datePeriodDays . ' days'));

        foreach ($services as $service) {
            $ads->appendChild($this->buildAd($document, $service, $dateBegin, $dateEnd));
        }

        return (string) $document->saveXML();
    }

    private function buildAd(
        \DOMDocument $document,
        AvitoService $service,
        string $dateBegin,
        string $dateEnd,
    ): \DOMElement {
        $ad = $document->createElement('Ad');

        $this->appendText($document, $ad, 'Id', $service->autoload_item_id);
        $this->appendText($document, $ad, 'DateBegin', $dateBegin);
        $this->appendText($document, $ad, 'DateEnd', $dateEnd);

        $this->appendText($document, $ad, 'Category', 'Услуги');
        $this->appendText($document, $ad, 'ServiceType', 'Психология');
        $this->appendText($document, $ad, 'ServiceSubtype', 'Семейный психолог');

        $this->appendText($document, $ad, 'Title', $service->title);
        $this->appendCdata($document, $ad, 'Description', $service->description);

        $this->appendText($document, $ad, 'Price', (string) $service->price);
        $this->appendText($document, $ad, 'Currency', 'RUB');

        $this->appendOptional($document, $ad, 'Workplace', $service->workplace);
        $this->appendOptional($document, $ad, 'ProviderGender', $service->provider_gender);
        $this->appendOptional($document, $ad, 'ClientGender', $service->client_gender);
        $this->appendOptional($document, $ad, 'ConsultationType', $service->consultation_type);
        $this->appendOptional($document, $ad, 'Approach', $service->approach);
        $this->appendOptional($document, $ad, 'Experience', $service->experience);

        $this->appendText($document, $ad, 'Address', (string) ($service->region ?? $service->city));
        $this->appendText($document, $ad, 'City', $service->city);

        return $ad;
    }

    private function appendText(\DOMDocument $document, \DOMElement $parent, string $tag, string $value): void
    {
        $parent->appendChild($document->createElement($tag, htmlspecialchars($value, ENT_XML1)));
    }

    private function appendCdata(\DOMDocument $document, \DOMElement $parent, string $tag, string $value): void
    {
        $child = $document->createElement($tag);
        $child->appendChild($document->createCDATASection($value));
        $parent->appendChild($child);
    }

    private function appendOptional(\DOMDocument $document, \DOMElement $parent, string $tag, ?string $value): void
    {
        if ($value !== null && $value !== '') {
            $this->appendText($document, $parent, $tag, $value);
        }
    }
}
