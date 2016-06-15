<?php

class CustomisableProductSiteConfig extends DataExtension
{
    private static $has_many = array(
        "ProductCustomisationLists" => "ProductCustomisationList"
    );
    
    public function updateCMSFields(FieldList $fields) {
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
}