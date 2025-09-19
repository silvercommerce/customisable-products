<?php

namespace SilverCommerce\CustomisableProducts\Tests;

use ReflectionClass;
use SilverStripe\Dev\SapphireTest;
use SilverCommerce\CustomisableProducts\CustomisableProduct;
use SilverCommerce\CustomisableProducts\ProductCustomisationGroup;
use SilverCommerce\CustomisableProducts\CustomisableProductVariant;
use SilverCommerce\CustomisableProducts\ProductCustomisationOption;

class CustomisableProductTest extends SapphireTest
{
    protected static $fixture_file = 'CustomisableProducts.yml';

    public function testFindVariationByOptions()
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

        $options = [
            $this->objFromFixture(
                ProductCustomisationOption::class,
                'option11'
            ),
            $this->objFromFixture(
                ProductCustomisationOption::class,
                'option21'
            )
        ];
        $variation = $product->findVariationByOptions($options);
        $this->assertInstanceOf(
            CustomisableProductVariant::class,
            $variation
        );

        $options = [
            $this->objFromFixture(
                ProductCustomisationOption::class,
                'option11'
            ),
            $this->objFromFixture(
                ProductCustomisationOption::class,
                'option22'
            )
        ];
        $variation = $product->findVariationByOptions($options);
        $this->assertInstanceOf(
            CustomisableProductVariant::class,
            $variation
        );

        $options = [
            $this->objFromFixture(
                ProductCustomisationOption::class,
                'option12'
            ),
            $this->objFromFixture(
                ProductCustomisationOption::class,
                'option21'
            )
        ];
        $variation = $product->findVariationByOptions($options);
        $this->assertInstanceOf(
            CustomisableProductVariant::class,
            $variation
        );

        $options = [
            $this->objFromFixture(
                ProductCustomisationOption::class,
                'option12'
            ),
            $this->objFromFixture(
                ProductCustomisationOption::class,
                'option31'
            )
        ];
        $variation = $product->findVariationByOptions($options);
        $this->assertEmpty($variation);
    }
}
