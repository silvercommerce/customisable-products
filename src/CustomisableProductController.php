<?php

namespace SilverCommerce\CustomisableProducts;

use Exception;
use ProductController;
use SilverStripe\ORM\ValidationResult;
use SilverCommerce\ShoppingCart\ShoppingCartFactory;

class CustomisableProductController extends ProductController
{
    private static $allowed_actions = [
        "AddToCartForm"
    ];

    /**
     * Overwrite the default AddToCartForm to add customisation options
     *
     * @return Form
     */
    public function AddToCartForm()
    {
        $form = parent::AddToCartForm();
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

        $this->extend('updateCustomisableAddToCartForm', $form);

        return $form;
    }

    public function doAddItemToCart($data, $form)
    {
        $classname = $data["ClassName"];
        $id = $data["ID"];
        $object = $classname::get()->byID($id);
        $cart = ShoppingCartFactory::create();
        $customisations = array();

        if (!empty($object)) {
            foreach ($data as $key => $value) {
                if (!(strpos($key, 'customise') === false) && $value) {
                    $custom_data = explode("_", $key);

                    if ($custom_item = ProductCustomisation::get()->byID($custom_data[1])) {
                        $modify_price = 0;

                        // Deal with checkbox set fields to ensure data is a string
                        if (is_array($value)) {
                            $custom_value = implode(",", $value);
                        } else {
                            $custom_value = $value;
                        }

                        // Check if the current selected option has a price modification
                        if ($custom_item->Options()->exists()) {
                            $options = $custom_item
                                ->Options()
                                ->filter("Title", $value);

                            // If dealing with multiple results collect them, or return a single value
                            if ($options->exists() && $options->count() > 1) {
                                $custom_value = "";
                                foreach ($options as $option) {
                                    $modify_price = $modify_price + $option->ModifyPrice;
                                    $custom_value .= $option->Title;
                                }
                            } elseif ($options->exists()) {
                                $option = $options->first();
                                $modify_price = $option->ModifyPrice;
                            }
                        }

                        $customisations[] = [
                            "Title" => $custom_item->Title,
                            "Value" => $custom_value,
                            "BasePrice" => $modify_price
                        ];
                    }
                }
            }

            $lock = (isset($object->Locked)) ? $object->Locked : false;

            // Try and add item to cart, return any exceptions raised
            // as a message
            try {
                $cart->addItem($object, $data['Quantity'], $lock, $customisations);
                $cart->write();

                $message = _t(
                    'ShoppingCart.AddedItemToCart',
                    'Added "{item}" to your shopping cart',
                    ["item" => $object->Title]
                );

                $form->sessionMessage(
                    $message,
                    ValidationResult::TYPE_GOOD
                );
            } catch (Exception $e) {
                $form->sessionMessage(
                    $e->getMessage()
                );
            }
        } else {
            $form->sessionMessage(
                _t("ShoppingCart.ErrorAddingToCart", "Error adding item to cart")
            );
        }

        return $this->redirectBack();
    }
}
