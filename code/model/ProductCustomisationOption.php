<?php

class ProductCustomisationOption extends DataObject
{
    private static $db = array(
        'Title'         => 'Varchar',
        'ModifyPrice'   => 'Decimal',
        'Sort'          => 'Int',
        'Default'       => 'Boolean'
    );

    private static $has_one = array(
        "Parent"        => 'ProductCustomisation'
    );

    private static $casting = array(
        'ItemSummary'   => 'Varchar'
    );

    private static $summary_fields = array(
        'Title',
        'ModifyPrice',
        'Default'
    );

    private static $field_types = array(
        'Title'         => 'TextField',
        'Sort'          => 'Int',
        'ModifyPrice'   => 'TextField',
        'Default'       => 'CheckboxField'
    );

    private static $default_sort = "\"Sort\" ASC";

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
        $price = new Currency();
        $tax_id = $this->Parent()->Parent()->TaxRateID;

        if ($tax_id && Catalogue::config()->price_includes_tax) {
            $modify_price += ($modify_price / 100) * $this->Parent()->Parent()->TaxRate()->Amount;
        }

        $price->setValue($modify_price);

        if ($price->RAW() > 0) {
            $summary = $this->Title . ' +' . $price->nice();
        } elseif ($price->RAW() < 0) {
            $summary = $this->Title . ' -' . str_replace(array("(",")"), "", $price->nice());
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

    public function canCreate($member = null)
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
