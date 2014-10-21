<?php

class CustomisableProduct_Controller extends Product_Controller {

    static $allowed_actions = array(
        'Form'
    );

    public function Form() {
        $form = parent::Form();
        $object = $this->owner->dataRecord;

        $requirements = new RequiredFields(array("Quantity"));

        // If product colour customisations are set, add them to the item form
        if($object->Customisations()->exists()) {
            foreach($object->Customisations() as $customisation) {
                $field = $customisation->Field();
                $form->Fields()->insertBefore($field, "Quantity");

                // Check if field required
                if($customisation->Required) {
                    $form
                        ->getValidator()
                        ->addRequiredField($field->getName());
                }
            }
        }

        return $form;
    }

    public function doAddItemToCart($data, $form) {
        $classname = $data["ClassName"];
        $id = $data["ID"];
        $customisations = array();

        $cart = ShoppingCart::get();

        if($object = $classname::get()->byID($id)) {
            foreach($data as $key => $value) {
                if(!(strpos($key, 'customise') === false) && $value) {
                    $custom_data = explode("_",$key);

                    if($custom_item = ProductCustomisation::get()->byID($custom_data[1])) {
                        $modify_price = 0;

                        // Check if the current selected option has a price modification
                        if($custom_item->Options()->exists()) {
                            $option = $custom_item
                                ->Options()
                                ->filter("Title",$value)
                                ->first();
                            $modify_price = ($option) ? $option->ModifyPrice : 0;
                        }

                        $customisations[] = array(
                            "Title" => $custom_item->Title,
                            "Value" => $value,
                            "Price" => $modify_price,
                        );
                    }

                }
            }

            if($object->TaxRateID && $object->TaxRate()->Amount)
                $tax_rate = $object->TaxRate()->Amount;
            else
                $tax_rate = 0;

            $item_to_add = new ArrayData(array(
                "Title" => $object->Title,
                "Content" => $object->Content,
                "BasePrice" => $object->Price(),
                "Price" => $object->Price(),
                "TaxRate" => $tax_rate,
                "Customisations" => $customisations,
                "Image" => $object->Images()->first(),
                "StockID" => $object->StockID,
                "ID" => $object->ID,
                "ClassName" => $object->ClassName
            ));

            $cart->add($item_to_add, $data['Quantity']);
            $cart->save();

            $message = _t('Checkout.AddedItemToCart', 'Added item to your shopping cart');
            $message .= ' <a href="'. $cart->Link() .'">';
            $message .= _t('Checkout.ViewCart', 'View cart');
            $message .= '</a>';

            $this->setSessionMessage(
                "success",
                $message
            );
        } else {
            $this->owner->setSessionMessage(
                "bad",
                _t("Checkout.ThereWasAnError", "There was an error")
            );
        }

        return $this->redirectBack();
    }
}

