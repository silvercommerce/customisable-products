<?php

namespace SilverCommerce\CustomisableProducts\Tests;

use ReflectionClass;
use SilverStripe\Dev\SapphireTest;
use SilverCommerce\CustomisableProducts\CustomisableProduct;
use SilverCommerce\CustomisableProducts\ProductCustomisationGroup;
use SilverCommerce\CustomisableProducts\CustomisableProductVariant;
use SilverCommerce\CustomisableProducts\ProductCustomisationOption;

class ProductCustomisationGroupTest extends SapphireTest
{
    protected static $fixture_file = 'CustomisableProducts.yml';

    public function testGenerateVariations()
    {
        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group1'
        );
        $product = $this->objFromFixture(
            CustomisableProduct::class,
            'product1'
        );

        $variations = $group->generateVariations($product);
        $this->assertCount(6, $variations);

        $variations = CustomisableProductVariant::get()
            ->filter('Parent.ID', $product->ID)
            ->toArray();
        $this->assertCount(6, $variations);

        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group2'
        );
        $product = $this->objFromFixture(
            CustomisableProduct::class,
            'product2'
        );

        $variations = $group->generateVariations($product);
        $this->assertCount(8, $variations);

        $variations = CustomisableProductVariant::get()
            ->filter('Parent.ID', $product->ID)
            ->toArray();
        $this->assertCount(8, $variations);

        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group3'
        );
        $product = $this->objFromFixture(
            CustomisableProduct::class,
            'product3'
        );

        $variations = $group->generateVariations($product);
        $this->assertCount(27, $variations);

        $variations = CustomisableProductVariant::get()
            ->filter('Parent.ID', $product->ID)
            ->toArray();
        $this->assertCount(27, $variations);
    }

    public function testGetGroupedOptionsArray()
    {
        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group1'
        );

        $options = $group->getGroupedOptionsArray();
        $this->assertCount(2, $options);
        $this->assertArrayHasKey(1, $options);
        $this->assertArrayHasKey(2, $options);
        $this->assertCount(3, $options[1]);
        $this->assertCount(2, $options[2]);

        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group2'
        );

        $options = $group->getGroupedOptionsArray();
        $this->assertCount(3, $options);
        $this->assertArrayHasKey(3, $options);
        $this->assertArrayHasKey(4, $options);
        $this->assertArrayHasKey(5, $options);
        $this->assertCount(2, $options[3]);
        $this->assertCount(2, $options[4]);
        $this->assertCount(2, $options[5]);

        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group3'
        );

        $options = $group->getGroupedOptionsArray();
        $this->assertCount(3, $options);
        $this->assertArrayHasKey(6, $options);
        $this->assertArrayHasKey(7, $options);
        $this->assertArrayHasKey(8, $options);
        $this->assertCount(3, $options[6]);
        $this->assertCount(3, $options[7]);
        $this->assertCount(3, $options[8]);
    }

    public function testGenerateObjects()
    {
        $reflection = new ReflectionClass(ProductCustomisationGroup::class);
        $method = $reflection->getMethod('generateObjects');
        $method->setAccessible(true);

        $product = $this->objFromFixture(
            CustomisableProduct::class,
            'product1'
        );
        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group1'
        );
        $options = $group->getGroupedOptionsArray();
        $results = $method->invokeArgs(
            $group,
            [$product, $options]
        );

        $this->assertCount(6, $results);

        foreach ($results as $result) {
            $this->assertInstanceOf(CustomisableProductVariant::class, $result);
        }

        $product = $this->objFromFixture(
            CustomisableProduct::class,
            'product2'
        );
        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group2'
        );
        $options = $group->getGroupedOptionsArray();
        $results = $method->invokeArgs(
            $group,
            [$product, $options]
        );

        $this->assertCount(8, $results);

        foreach ($results as $result) {
            $this->assertInstanceOf(CustomisableProductVariant::class, $result);
        }

        $product = $this->objFromFixture(
            CustomisableProduct::class,
            'product3'
        );
        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group3'
        );
        $options = $group->getGroupedOptionsArray();
        $results = $method->invokeArgs(
            $group,
            [$product, $options]
        );

        $this->assertCount(27, $results);

        foreach ($results as $result) {
            $this->assertInstanceOf(CustomisableProductVariant::class, $result);
        }
    }

    public function testFindOrCreateVariation()
    {
        $reflection = new ReflectionClass(ProductCustomisationGroup::class);
        $method = $reflection->getMethod('findOrCreateVariation');
        $method->setAccessible(true);

        $product = $this->objFromFixture(
            CustomisableProduct::class,
            'product1'
        );
        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group1'
        );
        $option1 = $this->objFromFixture(
            ProductCustomisationOption::class,
            'option11'
        );
        $option2 = $this->objFromFixture(
            ProductCustomisationOption::class,
            'option21'
        );
        $options = [$option1, $option2];

        $variation = $method->invokeArgs(
            $group,
            [$options, $product]
        );

        $this->assertInstanceOf(
            CustomisableProductVariant::class,
            $variation
        );
        $this->assertEquals($product->ID, $variation->ParentID);
        $this->assertCount(2, $variation->Options());

        $product = $this->objFromFixture(
            CustomisableProduct::class,
            'product3'
        );
        $group = $this->objFromFixture(
            ProductCustomisationGroup::class,
            'group3'
        );
        $option1 = $this->objFromFixture(
            ProductCustomisationOption::class,
            'option61'
        );
        $option2 = $this->objFromFixture(
            ProductCustomisationOption::class,
            'option71'
        );
        $option3 = $this->objFromFixture(
            ProductCustomisationOption::class,
            'option81'
        );
        $options = [$option1, $option2, $option3];

        $variation = $method->invokeArgs(
            $group,
            [$options, $product]
        );

        $this->assertInstanceOf(
            CustomisableProductVariant::class,
            $variation
        );
        $this->assertEquals($product->ID, $variation->ParentID);
        $this->assertCount(3, $variation->Options());
    }
}
