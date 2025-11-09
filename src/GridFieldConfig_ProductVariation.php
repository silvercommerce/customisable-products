<?php

namespace SilverCommerce\CustomisableProducts;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

class GridFieldConfig_ProductVariation extends GridFieldConfig
{
    public function __construct()
    {
        parent::__construct();

        $columns = new GridFieldEditableColumns();
        $columns->setDisplayFields([
            'Title' => [
                'title' => _t(
                    "CustomisableProduct.Title",
                    "Title"
                ),
                'field' => ReadonlyField::class
            ],
            'ImageID' => [
                'title' => _t(
                    "CustomisableProduct.Image",
                    "Image"
                ),
                'callback' => function ($record, $column, $grid) {
                    return DropdownField::create(
                        $column,
                        null,
                        $record
                            ->Parent()
                            ->Images()
                            ->map('ID', 'Title')
                            ->toArray(),
                    );
                },
            ],
            'StockID' => [
                'title' => _t(
                    "CustomisableProduct.StockID",
                    "StockID"
                ),
                'field' => TextField::class
            ],
            'BasePrice' => [
                'title' => _t(
                    "CustomisableProduct.Price",
                    "Price"
                ),
                'callback' => function ($record, $column, $grid) {
                    return $record
                        ->dbObject('BasePrice')
                        ->scaffoldFormField();
                }
            ]
        ]);

        $this->addComponents(
            new GridFieldToolbarHeader(),
            new GridFieldSortableHeader(),
            $columns,
            new GridFieldDetailForm(),
            new GridFieldDeleteAction()
        );
    }
}
