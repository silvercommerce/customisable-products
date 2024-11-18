<?php

namespace SilverCommerce\CustomisableProducts;

use ProductController;
use SilverCommerce\ShoppingCart\Forms\AddToCartForm;
use SilverCommerce\ShoppingCart\ShoppingCartFactory;

class CustomisableProductController extends ProductController
{
    private static $allowed_actions = [
        "AddToCartForm"
    ];

    public function AddToCartForm()
    {
        $form = AddToCartForm::create(
            $this->owner,
            "AddToCartForm"
        );
        $object = $this->dataRecord;
        $list = $object->CustomisationList();

        $form
            ->setProductClass($object->ClassName)
            ->setProductID($object->ID);

        // First add customisations from global lists
        if ($list->exists()) {
            foreach ($list->Customisations() as $customisation) {
                $field = $customisation->Field();
                $form
                    ->Fields()
                    ->insertBefore($field, "Quantity");

                // Check if field required
                if ($customisation->Required) {
                    // Manualy make field required (as SS seems to ignore this step)
                    $field
                        ->setAttribute("required", true)
                        ->addExtraClass("required");

                    $form
                        ->getValidator()
                        ->addRequiredField($field->getName());
                }
            }
        }

        // If product colour customisations are set, add them to the item form
        if ($object->Customisations()->exists()) {
            foreach ($object->Customisations() as $customisation) {
                $field = $customisation->Field();
                $form
                    ->Fields()
                    ->insertBefore($field, "Quantity");

                // Check if field required
                if ($customisation->Required) {
                    // Manualy make field required (as SS seems to ignore this step)
                    $field
                        ->setAttribute("required", true)
                        ->addExtraClass("required");

                    $form
                        ->getValidator()
                        ->addRequiredField($field->getName());
                }
            }
        }

        return $form;
    }
}
