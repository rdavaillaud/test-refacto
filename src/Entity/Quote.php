<?php

class Quote implements TemplateDataSourceInterface
{
    public $id;
    public $siteId;
    public $destinationId;
    public $dateQuoted;

    public function __construct($id, $siteId, $destinationId, $dateQuoted)
    {
        $this->id = $id;
        $this->siteId = $siteId;
        $this->destinationId = $destinationId;
        $this->dateQuoted = $dateQuoted;
    }

    public static function renderHtml(Quote $quote)
    {
        return '<p>' . $quote->id . '</p>';
    }

    public static function renderText(Quote $quote)
    {
        return (string) $quote->id;
    }

    public function toTemplateDataSource($tagPrefix)
    {
        $usefulObject = SiteRepository::getInstance()->getById($this->siteId);
        $destination = DestinationRepository::getInstance()->getById($this->destinationId);
        $destinationLink = isset($destination) ? $usefulObject->url . '/' . $destination->countryName . '/quote/' . $this->id : '';

        return array(
            "[$tagPrefix:summary_html]" => Quote::renderHtml($this),
            "[$tagPrefix:summary]" => Quote::renderText($this),
            "[$tagPrefix:destination_name]" => $destination->countryName,
            "[$tagPrefix:destination_link]" => $destinationLink,
            "[$tagPrefix:date]" => $this->dateQuoted
        );
    }
}
