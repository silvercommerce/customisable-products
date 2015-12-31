<?php

class CustomisableProduct extends Product
{

    /**
     * @config
     */
    private static $description = "A product that can be modified by the customer";

    private static $has_many = array(
        "Customisations"=> "ProductCustomisation"
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName("Root.Customisations");

        $fields->addFieldToTab(
            'Root',
            new Tab(
                'Customisations',
                'Customisations'
            ),
            'Settings'
        );

        // Deal with customisations
        $add_button = new GridFieldAddNewButton('toolbar-header-left');
        $add_button->setButtonName('Add Customisation');

        $custom_config = GridFieldConfig::create()->addComponents(
            new GridFieldToolbarHeader(),
            $add_button,
            new GridFieldSortableHeader(),
            new GridFieldDataColumns(),
            new GridFieldPaginator(20),
            new GridFieldEditButton(),
            new GridFieldDeleteAction(),
            new GridFieldDetailForm(),
            new GridFieldOrderableRows('Sort')
        );

        $custom_field = GridField::create('Customisations', '', $this->Customisations(), $custom_config);

        $fields->addFieldToTab('Root.Customisations', $custom_field);

        return $fields;
    }
}
