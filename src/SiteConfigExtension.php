<?php

namespace SilverCommerce\CustomisableProducts;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

class SiteConfigExtension extends DataExtension
{
    private static $has_many = [
        "ProductCustomisationLists" => ProductCustomisationList::class
    ];
    
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldTotab(
            "Root.Shop",
            ToggleCompositeField::create(
                "CustomisableProductsSettings",
                _t("CustomisableProducts.CustomisableProducts", "Customisable Products"),
                [
                    GridField::create(
                        'ProductCustomisationLists',
                        '',
                        $this->owner->ProductCustomisationLists(),
                        GridFieldConfig_RecordEditor::create()
                    )
                ]
            )
        );
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();

        // Clean up customisations
        foreach ($this->ProductCustomisationLists() as $customisation) {
            $customisation->delete();
        }
    }
}