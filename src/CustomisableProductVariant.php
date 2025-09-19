<?php

namespace SilverCommerce\CustomisableProducts;

use SilverStripe\i18n\i18n;
use SilverStripe\View\HTML;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;
use SilverStripe\SiteConfig\SiteConfig;
use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverCommerce\TaxAdmin\Traits\Taxable;
use SilverCommerce\TaxAdmin\Interfaces\TaxableProvider;
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
class CustomisableProductVariant extends DataObject implements TaxableProvider
{
    use Taxable;

    private static $table_name = "CustomisableProductVariant";

    private static $db = [
        'StockID' => 'Varchar',
        'BasePrice' => 'Decimal(9,3)'
    ];

    private static $has_one = [
        'Image' => Image::class,
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

    private static $casting = [
        'OptionsList' => 'Varchar',
        'CMSThumbnailHTML' => 'HTMLText'                            
    ];

    private static $extensions = [
        Versioned::class . '.versioned'
    ];

    public function getTitle(): string
    {
        return (string)$this->getOptionsList();
    }

    public function getBasePrice()
    {
        return $this
            ->dbObject('BasePrice')
            ->getValue();
    }

    public function getShowPriceWithTax(): bool
    {
        return $this->Parent()->getShowPriceWithTax();
    }

    public function getShowTaxString(): bool
    {
        return $this->Parent()->getShowTaxString();
    }

    public function getTaxRate(): TaxRate
    {
        return $this->Parent()->getTaxRate();
    }

    public function getLocale(): string
    {
        return $this->Parent()->getLocale();
    }

    public function getOptionsList(): string
    {
        $options = $this
            ->Options()
            ->sort('Title ASC')
            ->column('Title');

        return implode(', ', $options);
    }

    /**
     * Stub method to get the site config, unless the current class can provide an alternate.
     *
     * @return SiteConfig
     */
    public function getSiteConfig()
    {
        if ($this->hasMethod('alternateSiteConfig')) {
            $altConfig = $this->alternateSiteConfig();
            if ($altConfig) {
                return $altConfig;
            }
        }

        return SiteConfig::current_site_config();
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }
}
