<?php

class CustomisableProduct_Controller extends Product_Controller
{

    public static $allowed_actions = array(
        'Form'
    );

    public function Form()
    {
        $form = parent::Form();
        $object = $this->dataRecord;

        $requirements = new RequiredFields(array("Quantity"));

        // First add customisations from global lists
        if ($object->CustomisationListID) {
            foreach ($object->CustomisationList()->Customisations() as $customisation) {
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

    public function doAddItemToCart($data, $form)
    {
        $classname = $data["ClassName"];
        $id = $data["ID"];
        $customisations = array();
        $cart = ShoppingCart::get();

        if ($object = $classname::get()->byID($id)) {
            $price = $object->Price;
        
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

                        $customisations[] = array(
                            "Title" => $custom_item->Title,
                            "Value" => $custom_value,
                            "Price" => $modify_price
                        );
                    }
                }
            }

            if ($object->TaxRateID && $object->TaxRate()->Amount) {
                $tax_rate = $object->TaxRate()->Amount;
            } else {
                $tax_rate = 0;
            }

            $item_to_add = array(
                "Key" => (int)$data['ID'] . ':' . base64_encode(json_encode($customisations)),
                "Title" => $object->Title,
                "Content" => $object->Content,
                "BasePrice" => $price,
                "TaxRate" => $tax_rate,
                "CustomisationArray" => $customisations,
                "Image" => $object->Images()->first(),
                "StockID" => $object->StockID,
                "ID" => $object->ID,
                "ClassName" => $object->ClassName,
                "Stocked" => $object->Stocked
            );

            // Try and add item to cart, return any exceptions raised
            // as a message
            try {
                $cart->add($item_to_add, $data['Quantity']);
                $cart->save();
                
                $message = _t('Commerce.AddedItemToCart', 'Added item to your shopping cart');
                $message .= ' <a href="'. $cart->Link() .'">';
                $message .= _t('Commerce.ViewCartNow', 'View cart now');
                $message .= '</a>';

                $this->setSessionMessage(
                    "success",
                    $message
                );
            } catch(ValidationException $e) {
                $this->setSessionMessage(
                    "bad",
                    $e->getMessage()
                );
            } catch(Exception $e) {
                $this->setSessionMessage(
                    "bad",
                    $e->getMessage()
                );
            }
        } else {
            $this->setSessionMessage(
                "bad",
                _t("Checkout.ThereWasAnError", "There was an error")
            );
        }

        return $this->redirectBack();
    }
}
