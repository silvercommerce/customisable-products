<?php

class CustomisableProduct extends Product
{

    /**
     * @config
     */
    private static $description = "A product that can be modified by the customer";

    private static $has_one = array(
        "CustomisationList" => "ProductCustomisationList"
    );

    private static $has_many = array(
        "Customisations" => "ProductCustomisation"
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName("Root.Customisations");

        // Only add fields if the object exists
        if($this->ID) {
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
            $add_button->setButtonName(_t(
                "CustomisableProduct.AddCustomisation",
                "Add Customisation"
            ));

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

            $fields->addFieldsToTab(
                'Root.Customisations',
                array(
                    DropdownField::create(
                        "CustomisationListID",
                        _t("CustomisableProduct.UseCustomisationList", "Use a Customisation List"),
                        ProductCustomisationList::get()->map()
                    )->setEmptyString(_t(
                        "CustomisableProduct.SelectList",
                        "Select List"
                    )),
                    GridField::create(
                        'Customisations',
                        '',
                        $this->Customisations(),
                        $custom_config
                    )
                )
            );
        }

        return $fields;
    }

    public function onBeforeDelete() {
        parent::onBeforeDelete();

        // Clean up customisations
        foreach ($this->Customisations() as $customisation) {
            $customisation->delete();
        }
    }
}
