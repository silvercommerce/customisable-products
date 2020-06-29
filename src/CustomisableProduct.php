<?php

namespace SilverCommerce\CustomisableProducts;

use Product;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;

class CustomisableProduct extends Product
{
    /**
     * Table to create in DB
     * 
     * @var    string
     * @config
     */
    private static $table_name = "CustomisableProduct";

    /**
     * Human-readable singular name.
     * @var string
     * @config
     */
    private static $singular_name = 'Customisable Product';

    /**
     * Human-readable plural name
     * @var string
     * @config
     */
    private static $plural_name = 'Customisable Products';

    /**
     * @config
     */
    private static $description = "A product that can be modified by the customer";

    private static $has_one = [
        "CustomisationList" => ProductCustomisationList::class
    ];

    private static $has_many = [
        "Customisations" => ProductCustomisation::class
    ];

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(
            function ($fields) {
                $fields->removeByName("Root.Customisations");

                // Only add fields if the object exists
                if($this->ID) {
                    // Deal with customisations
                    $add_button = new GridFieldAddNewButton('toolbar-header-left');
                    $add_button->setButtonName(
                        _t(
                            "CustomisableProduct.AddCustomisation",
                            "Add Customisation"
                        )
                    );

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
                        )->setEmptyString(
                            _t(
                                "CustomisableProduct.SelectList",
                                "Select List"
                            )
                        ),
                        GridField::create(
                            'Customisations',
                            '',
                            $this->Customisations(),
                            $custom_config
                        )
                        )
                    );
                }
            }
        );

        return parent::getCMSFields();
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();

        // Clean up customisations
        foreach ($this->Customisations() as $customisation) {
            $customisation->delete();
        }
    }
}
