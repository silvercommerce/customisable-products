<?php

namespace SilverCommerce\CustomisableProducts;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;

class SiteConfigExtension extends DataExtension
{
    private static $has_many = [
        "ProductCustomisationLists" => ProductCustomisationList::class
    ];
    
    public function updateCMSFields(FieldList $fields)
    {
        // Deal with customisations
        $add_button = new GridFieldAddNewButton('toolbar-header-left');
        $add_button->setButtonName(_t(
            "CustomisableProduct.AddCustomisationList",
            "Add Customisation List"
        ));

        $custom_config = GridFieldConfig::create()->addComponents(
            new GridFieldToolbarHeader(),
            $add_button,
            new GridFieldSortableHeader(),
            new GridFieldDataColumns(),
            new GridFieldPaginator(20),
            new GridFieldEditButton(),
            new GridFieldDeleteAction(),
            new GridFieldDetailForm()
        );

        $fields->addFieldTotab(
            "Root.CustomProducts",
            GridField::create(
                'ProductCustomisationLists',
                '',
                $this->owner->ProductCustomisationLists(),
                $custom_config
            )
        );
    }

    public function onBeforeDelete() {
        parent::onBeforeDelete();

        // Clean up customisations
        foreach ($this->ProductCustomisationLists() as $customisation) {
            $customisation->delete();
        }
    }
}