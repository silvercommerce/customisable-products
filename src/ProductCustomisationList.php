<?php

namespace SilverCommerce\CustomisableProducts;

use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;

class ProductCustomisationList extends DataObject
{
    /**
     * Table to create in DB
     * 
     * @var string
     * @config
     */
    private static $table_name = "ProductCustomisationList";

    private static $db = [
        "Title" => "Varchar"
    ];

    private static $has_one = [
        "SiteConfig" => SiteConfig::class
    ];

    private static $has_many = [
        "Customisations" => ProductCustomisation::class
    ];

    public function onBeforeDelete() {
        parent::onBeforeDelete();

        // Clean up customisations
        foreach ($this->Customisations() as $customisation) {
            $customisation->delete();
        }
    }
}