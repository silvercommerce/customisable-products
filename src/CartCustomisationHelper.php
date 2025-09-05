<?php

namespace SilverCommerce\CustomisableProducts;

use LogicException;
use SilverCommerce\OrdersAdmin\Factory\LineItemFactory;
use SilverCommerce\OrdersAdmin\Interfaces\LineItemPricable;
use SilverCommerce\OrdersAdmin\Interfaces\LineItemCustomisable;

class CartCustomisationHelper implements LineItemPricable, LineItemCustomisable
{
    protected function extractCustomisationID(string $key): int
    {
        $data = explode("_", $key);
        return $data[1];
    }

    protected function isDataCustomisation(string $key): bool
    {
        return !(strpos($key, 'customise') === false);
    }

    protected function getProductCustomisation(string $key): ProductCustomisation
    {
        $id = $this->extractCustomisationID($key);
        $item = ProductCustomisation::get()
            ->byID($id);

        if (empty($item)) {
            throw new LogicException('Invalid customisation');
        }

        return $item;
    }

    public function modifyItemPrice(
        LineItemFactory $factory,
        array $data = []
    ): void {
        $item = $factory->getItem();

        if (empty($item)) {
            return;
        }

        $product = $item->findStockItem();

        if (!$product instanceof CustomisableProduct) {
            return;
        }

        foreach ($data as $key => $value) {
            if (!$this->isDataCustomisation($key)) {
                continue;
            }

            $custom_item = $this->getProductCustomisation($key);
            $modify = 0;

            $option = $custom_item
                ->Options()
                ->find("Title", $value);

            if (!empty($option)) {
                $modify = $option->ModifyPrice;
            }

            if (!(round($modify, 2) === 0.00)) {
                $factory->modifyPrice(
                    $custom_item->Title,
                    (float)$option->ModifyPrice,
                    $option
                );
            }
        }

        return;
    }

    public function customiseLineItem(
        LineItemFactory $factory,
        array $data = []
    ): void {
        $item = $factory->getItem();
        $product = $item->findStockItem();

        if (!$product instanceof CustomisableProduct) {
            return;
        }

        foreach ($data as $key => $value) {
            if (!$this->isDataCustomisation($key)) {
                continue;
            }

            $custom_item = $this->getProductCustomisation($key);
            $option = null;

            if ($custom_item->DisplayAs === ProductCustomisation::TEXT_FIELD) {
                $custom_value = $value;
            } elseif (is_array($value)) {
                $custom_value = implode(",", $value);
            } else {
                $option = $custom_item
                    ->Options()
                    ->find("Title", $value);

                if (empty($option)) {
                    throw new LogicException('Invalid customisation option');
                }

                $custom_value = $option->Title;
            }

            $factory->customise(
                $custom_item->Title,
                $custom_value,
                [],
                $option
            );
        }

        return;
    }
}
