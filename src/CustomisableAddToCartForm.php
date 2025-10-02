<?php

namespace SilverCommerce\CustomisableProducts;

use Dom\Text;
use SilverCommerce\ShoppingCart\Forms\AddToCartForm;
use SilverCommerce\ShoppingCart\ShoppingCartFactory;
use SilverStripe\Forms\TextField;

class CustomisableAddToCartForm extends AddToCartForm
{


    public function __construct(
        CustomisableProductController $controller,
        $name = self::DEFAULT_NAME,
        ?CustomisableProductVariant $variant = null
    ) {
        parent::__construct($controller, $name);

        /** @var CustomisableProduct $object */
        $product = $controller->dataRecord;
        $link = $controller->getCustomisationLink();
        $list = $product->getCompiledCustomisations();

        $this->setHTMLID('AddToCartForm_AddToCartForm');
        $this->setProductClass($product->ClassName);
        $this->setProductID($product->ID);
        $this->setAttribute(
            'data-customisation-link',
            $link
        );
        $this->addExtraClass("product-customisation-form");

        foreach ($list as $customisation) {
            $field = $customisation
                ->Field()
                ->addExtraClass("product-customisation-field");

            if (!empty($variant && !is_a($field, TextField::class))) {
                $field->setValue(
                    $variant->getCustomisationValue($customisation)
                );
            }

            $this
                ->Fields()
                ->insertBefore("Quantity", $field);

            // Check if field required
            if ($customisation->Required) {
                // Manualy make field required (as SS can ignore this step)
                $field
                    ->setAttribute("required", true)
                    ->addExtraClass("required");

                $this
                    ->getValidator()
                    ->addRequiredField($field->getName());
            }
        }

        $this->extend("updateCustomisableAddToCartForm");

        return;
    }
}
