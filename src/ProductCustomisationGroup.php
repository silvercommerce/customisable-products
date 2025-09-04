<?php

namespace SilverCommerce\CustomisableProducts;

use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\SiteConfig\SiteConfig;
use SilverCommerce\CustomisableProducts\ProductCustomisation;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\DataObject;

/**
 * Generic container for customisations that can be applied
 * to products and globally to SiteConfig.
 *
 * Attaching a group to a propduct will result in your
 * customisations being built into the product on save for
 * editing.
 *
 * @property string Title
 * @property int Sort
 *
 * @method SiteConfig SiteConfig
 * @method HasManyList Customisations
 * @method ManyManyList Products
 */
class ProductCustomisationGroup extends DataObject
{
    private static $table_name = "ProductCustomisationGroup";

    private static $db = [
        "Title" => "Varchar",
        "Sort" => "Int"
    ];

    private static $has_one = [
        "SiteConfig" => SiteConfig::class
    ];

    private static $has_many = [
        "Customisations" => ProductCustomisation::class
    ];

    private static $belongs_many_many = [
        "Products" => CustomisableProduct::class
    ];

    private static $owns = [
        "Customisations"
    ];

    public function getSortedCustomisations()
    {
        return $this
            ->Customisations()
            ->sort('Sort ASC, Title ASC');
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(
            function ($fields) {
                $fields->removeByName("Sort");
                $fields->replaceField(
                    "SiteConfigID",
                    HiddenField::create('SiteConfigID')
                );

                // Move customisations to main tab
                $customisations = $fields
                    ->dataFieldByName("Customisations");

                if (!empty($customisations)) {
                    $fields->addFieldToTab(
                        "Root.Main",
                        $customisations
                    );
                }
            }
        );

        return parent::getCMSFields();
    }

    public function getGroupedOptionsArray(): array
    {
        $customisations = $this
            ->getSortedCustomisations()
            ->columnUnique('ID');

        $options = ProductCustomisationOption::get()
            ->filter('Parent.ID', $customisations);
        
        if (!$options->exists()) {
            return [];
        }

        $grouped = [];

        foreach ($options as $option) {
            $parentID = $option->ParentID;

            if (!isset($grouped[$parentID])) {
                $grouped[$parentID] = [];
            }

            $grouped[$parentID][] = $option;
        }

        return $grouped;
    }

    public function generateVariations(
        CustomisableProduct $product
    ): array {
        $grouped = $this->getGroupedOptionsArray();

        if (count($grouped) === 0) {
            return [];
        }

        $results = $this->generateObjects($product, $grouped);

        return $results;
    }

    /**
     * Generate Variation Objects for the attached product
     */
    protected function generateObjects(
        CustomisableProduct $product,
        array $customisations,
        $options = [],
        $index = 0
    ): array {
        // Base case: if we have reached the end of the array, create an object
        if ($index == count($customisations)) {
            return [$this->findOrCreateVariation($options, $product)];
        }

        $key = array_keys($customisations)[$index];
        $values = $customisations[$key];
        $results = [];

        foreach ($values as $value) {
            $options[$key] = $value;
            $subResults = $this->generateObjects(
                $product,
                $customisations,
                $options,
                $index + 1
            );

            foreach ($subResults as $subResult) {
                $results[] = $subResult;
            }
        }

        return $results;
    }

    /**
     * Find an existing variation with the given options,
     * or create a new one
     */
    protected function findOrCreateVariation(
        array $options,
        CustomisableProduct $product
    ) {
        $existing = $product
            ->Variations();

        // Ensure that we look for an existing variation
        // with the provided combination of options
        foreach ($options as $option) {
            $existing = $existing->addFilter(
                [
                "Options.ID" => $option->ID
                ]
            );
        }

        $existing = $existing->first();

        if (!empty($existing)) {
            return $existing;
        }

        $variation = CustomisableProductVariant::create();
        $variation->ParentID = $product->ID;
        $variation->write();

        foreach ($options as $option) {
            $variation
                ->Options()
                ->add($option);
        }

        return $variation;
    }

    /**
     * Once this is written, generate customisations
     * for the attached products
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if (!$this->Products()->exists()) {
            return;
        }

        foreach ($this->Products() as $product) {
            $this->generateVariations($product);
        }
    }
}
