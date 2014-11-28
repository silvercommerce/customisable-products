<?php


class CustomisableProductShoppingCart extends Extension {

    /**
     * Augment setup to get item price
     *
     */
    public function augmentSetup() {
        foreach($this->owner->Items as $item) {
            if($item->BasePrice && $item->Customisations && is_array($item->Customisations)) {
                $base_price = $item->BasePrice;
                $customisations = ArrayList::create();

                foreach($item->Customisations as $customisation) {
                    if($customisation['Price'])
                        $base_price += $customisation['Price'];

                    $customisations->add($customisation);
                }

                $item->Customisations = $customisations;
                $item->Price->setValue($base_price);
            }
            
            // Calculate the discount
            $item->Discount = new Currency("Discount");
            
            if($item->Price && $this->owner->discount) {
                if($item->Price->RAW() && $this->owner->discount->Type == "Fixed" && $this->owner->discount->Amount)
                    $item->Discount->setValue($this->owner->discount->Amount / $this->owner->Items->count());
                elseif($item->Price && $this->owner->discount->Type == "Percentage" && $this->owner->discount->Amount)
                    $item->Discount->setValue(($item->Price->RAW() / 100) * $this->owner->discount->Amount);
            } else
                $item->Discount->setValue(0);

            // If tax rate set work out tax
            if($item->TaxRate) {
                $item->Tax = new Currency("Tax");
                $item->Tax->setValue((($item->Price->RAW() - $item->Discount->RAW()) / 100) * $item->TaxRate);
            }
        }
    }

    public function onBeforeSave($items) {
        // Convert customisations back to an array
        foreach($this->owner->items as $item) {
            if($item->Customisations && is_object($item->Customisations)) {
                $customisations = array();

                foreach($item->Customisations as $customisation) {
                    $customisations[] = array(
                        "Title" => $customisation->Title,
                        "Value" => $customisation->Value,
                        "Price" => $customisation->Price
                    );
                }

                $item->Customisations = $customisations;
            }
        }
    }
}
