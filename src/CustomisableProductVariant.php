<?php

namespace SilverCommerce\CustomisableProducts;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverCommerce\CustomisableProducts\ProductCustomisationOption;

/**
 * A specific variant of a customisable product, defined by
 * a single option from each customisation attached to the
 * parent product.
 *
 * @property string StockID
 * @property float Baseprice
 *
 * @method CustomisableProduct Parent
 * @method ProductCustomisation Customisation
 * @method ManyManyList Options
 */
class CustomisableProductVariant extends DataObject
{
    private static $table_name = "CustomisableProductVariant";

    private static $db = [
        'StockID' => 'Varchar',
        'Baseprice' => 'Decimal(9,3)'
    ];

    private static $has_one = [
        'Parent' => CustomisableProduct::class
    ];

    private static $many_many = [
        'Options' => ProductCustomisationOption::class
    ];

    private static $summary_fields = [
        'Title',
        'StockID',
        'BasePrice'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }
}
