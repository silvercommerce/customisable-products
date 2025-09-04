<?php

namespace SilverCommerce\CustomisableProducts;

use Product;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverCommerce\OrdersAdmin\Factory\LineItemFactory;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverCommerce\CustomisableProducts\ProductCustomisation;

/**
 * A product that can be customised by the user, either
 * through text entry or selection of options.
 *
 * @property int CustomisationListID
 *
 * @method ProductCustomisationList CustomisationList
 * @method HasManyList Customisations
 * @method HasManyList Variations
 * @method ManyManyList CustomisationGroups
 */
class CustomisableProduct extends Product
{
    private static $table_name = "CustomisableProduct";

    private static $singular_name = 'Customisable Product';

    private static $plural_name = 'Customisable Products';

    private static $description = "A product that can be modified by the customer";

    private static $has_one = [
        "CustomisationList" => ProductCustomisationList::class
    ];

    private static $has_many = [
        "Customisations" => ProductCustomisation::class,
        "Variations" => CustomisableProductVariant::class
    ];

    private static $many_many = [
        'CustomisationGroups' => ProductCustomisationGroup::class
    ];

    private static $owns = [
        "Customisations"
    ];

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(
            function ($fields) {
                $fields->removeByName(
                    [
                    "CustomisationListID",
                    "Customisations",
                    "Root.Customisations"
                    ]
                );

                // Only add fields if the object exists
                if ($this->ID) {
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
                        new GridFieldDetailForm(),
                        new GridFieldOrderableRows('Sort')
                    );

                    $fields->addFieldsToTab(
                        'Root.Customisations',
                        [
                            GridField::create(
                                'CustomisationGroups',
                                'Setup your customisations',
                                $this->CustomisationGroups(),
                                $custom_config
                            ),
                            /*DropdownField::create(
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
                            )*/
                        ]
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
