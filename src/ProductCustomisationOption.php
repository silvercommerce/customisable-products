<?php

namespace SilverCommerce\CustomisableProducts;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\SiteConfig\SiteConfig;

class ProductCustomisationOption extends DataObject
{
    private static $db = [
        'Title'         => 'Varchar',
        'ModifyPrice'   => 'Decimal',
        'Sort'          => 'Int',
        'Default'       => 'Boolean'
    ];

    private static $has_one = [
        "Parent"        => ProductCustomisation::class
    ];

    private static $casting = [
        'ItemSummary'   => 'Varchar'
    ];

    private static $summary_fields = [
        'Title',
        'ModifyPrice',
        'Default'
    ];

    private static $field_types = [
        'Title'         => 'TextField',
        'Sort'          => 'Int',
        'ModifyPrice'   => 'TextField',
        'Default'       => 'CheckboxField'
    ];

    private static $default_sort = [
        'Sort' => 'ASC'
    ];

    /**
     * Use this method to get a full list of field types
     * (for use in table fields)
     *
     * @return Array of field names and types
     */
    public function getFieldTypes()
    {
        $fields = self::$field_types;

        $this->extend('updateFieldTypes', $fields);

        return $fields;
    }

    public function getItemSummary()
    {
        $modify_price = $this->ModifyPrice;
        $config = SiteConfig::current_site_config();
        $price = new DBCurrency();
        $tax = $this->Parent()->Parent()->getTaxFromCategory();

        if (isset($tax) && $config->ShowPriceAndTax) {
            $modify_price += ($modify_price / 100) * $tax->Rate;
        }

        $price->setValue($modify_price);

        if ($price->RAW() > 0) {
            $summary = $this->Title . ' +' . $price->Nice();
        } elseif ($price->RAW() < 0) {
            $summary = $this->Title . ' -' . str_replace(array("(",")"), "", $price->Nice());
        } else {
            $summary = $this->Title;
        }

        $this->extend('updateItemSummary', $summary);

        return $summary;
    }

    public function canView($member = false)
    {
        return $this->Parent()->canView($member);
    }

    public function canCreate($member = null, $context = [])
    {
        return $this->Parent()->canCreate($member);
    }

    public function canEdit($member = null)
    {
        return $this->Parent()->canEdit($member);
    }

    public function canDelete($member = null)
    {
        return $this->Parent()->canDelete($member);
    }
}
