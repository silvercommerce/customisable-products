<?php

namespace SilverCommerce\CustomisableProducts;

use ProductController;
use SilverStripe\Control\HTTPRequest;
use SilverCommerce\ShoppingCart\Forms\AddToCartForm;
use SilverCommerce\CustomisableProducts\CustomisableProduct;
use SilverStripe\View\Requirements;

class CustomisableProductController extends ProductController
{
    private static $allowed_actions = [
        "customisationdata",
        "AddToCartForm"
    ];

    public function init()
    {
        parent::init();

        Requirements::javascript(
            'silvercommerce/customisable-products: client/dist/js/main.bundle.js'
        );
    }

    public function getCustomisationLink()
    {
        return $this->Link('customisationdata');
    }

    public function customisationdata(HTTPRequest $request)
    {
        /** @var CustomisableProduct */
        $record = $this->dataRecord;
        $ids = $request->getVar('o');

        if (empty($ids)) {
            return $this->httpError(500);
        }

        $ids = explode(',', $ids);
        $options = [];

        if (count($ids) === 0) {
            return $this->httpError(500);
        }

        $options = ProductCustomisationOption::get()
            ->filter('ID', $ids)
            ->toArray();

        $variant = $record->findVariationByOptions($options);

        if (empty($variant)) {
            return $this->httpError(404);
        }

        $image_url = null;
        $image_id = null;

        if ($variant->Image()->exists()) {
            $image = $variant->Image();
            $image_url = $image->Pad(1200,1200)->getURL();
            $image_id = $image->ID;
        }

        $data = [
            'id' => $variant->ID,
            'iid' => $image_id,
            'isrc' => $image_url,
            'price' => (string)$variant->getNicePrice(),
            'stockid' => $variant->StockID,
        ];

        $response = $this
            ->getResponse()
            ->addHeader('Content-Type', 'application/json')
            ->setStatusCode(200)
            ->setBody(json_encode($data));

        return $response;
    }

    public function AddToCartForm()
    {
        return CustomisableAddToCartForm::create(
            $this,
            'AddToCartForm'
        );
    }
}
