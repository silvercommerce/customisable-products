<?php

namespace SilverCommerce\CustomisableProducts;

use Product;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverCommerce\CustomisableProducts\ProductCustomisation;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;

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
        // Legacy support for single customisation list
        "CustomisationList" => ProductCustomisationList::class
    ];

    private static $has_many = [
        "Customisations" => ProductCustomisation::class, //Legacy
        "Variations" => CustomisableProductVariant::class
    ];

    private static $many_many = [
        'CustomisationGroups' => ProductCustomisationGroup::class
    ];

    private static $owns = [
        "Customisations",
        "Variations"
    ];

    private static $cascade_deletes = [
        "Customisations",
        "Variations"
    ];

    public function getSortedCustomisationGroups(): ManyManyList
    {
        return $this
            ->CustomisationGroups()
            ->sort('Sort ASC');
    }

    public function getCompiledCustomisations(): SS_List
    {
        $list = ArrayList::create();
        $groups = $this->getSortedCustomisationGroups();

        if ($groups->count()) {
            foreach ($groups as $group) {
                $list->merge($group->getSortedCustomisations());
            }
        }

        return $list;
    }

    public function getBasePrice()
    {
        $variation = $this->getChosenVariation();

        if (!empty($variation)) {
            return $variation->getBasePrice();
        }

        return $this->dbObject('BasePrice')->getValue();
    }

    public function PrimaryImage()
    {
        $variation = $this->getChosenVariation();

        if (!empty($variation) && $variation->Image()->exists()) {
            return $variation->Image();
        }

        return parent::PrimaryImage();
    }

    public function getChosenVariation(): ?CustomisableProductVariant
    {
        $request = Injector::inst()->get(HTTPRequest::class);
        $ids = $request->getVar('o');

        if (empty($ids)) {
            return null;
        }

        $ids = explode(',', $ids);
        $options = [];

        if (count($ids) === 0) {
            return null;
        }

        $options = ProductCustomisationOption::get()
            ->filter('ID', $ids)
            ->toArray();

        return $this->findVariationByOptions($options);
    }

    public function findVariationByOptions(array $options): ?CustomisableProductVariant
    {
        if (count($options) === 0) {
            return null;
        }

        $ids = array_map(
            function ($item) {
                return $item->ID;
            },
            $options
        );

        // SilverStripe does not always return variants consistently
        // via has_many, so get them manually.
        $variations = CustomisableProductVariant::get()
            ->filter('ParentID', $this->ID);

        $existing = $variations
            ->filterByCallback(function ($item) use ($ids) {
                /** @var CustomisableProductVariant $item */
                $option_ids = $item->Options()->column('ID');

                $diff_one = array_diff($ids, $option_ids);
                $diff_two = array_diff($option_ids, $ids);
                
                if (count($diff_one) > 0 || count($diff_two) > 0) {
                    return false;
                }

                return true;
            })->first();

        if (empty($existing)) {
            return null;
        }

        return $existing;
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(
            function ($fields) {
                $fields->removeByName([
                    "CustomisationGroups",
                    "CustomisationListID",
                    "Customisations",
                    "Root.Customisations"
                ]);

                // Only add fields if the object exists
                if ($this->ID) {
                    // Deal with customisations
                    $add_button = new GridFieldAddNewButton('buttons-before-left');
                    $add_button->setButtonName(
                        _t(
                            "CustomisableProduct.AddCustomisation",
                            "Add New Customisation"
                        )
                    );

                    $custom_config = GridFieldConfig_RelationEditor::create();
                    
                    $custom_config
                        ->removeComponentsByType(GridFieldAddNewButton::class)
                        ->addComponents(
                            $add_button,
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
                            )
                        ]
                    );
                }

                $variations_field = $fields
                    ->dataFieldByName('Variations');

                if (!empty($variations_field)) {
                    $variations_field
                        ->setConfig(GridFieldConfig_ProductVariation::create());

                    $fields->addFieldToTab(
                        'Root.Customisations',
                        $variations_field
                    );
                }

                $fields->removeByName('Variations');
            }
        );

        return parent::getCMSFields();
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // Build all customisation variations
        foreach ($this->CustomisationGroups() as $group) {
            $group->generateVariations($this);
        }
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
