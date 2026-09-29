<?php
/**
 * Created by PhpStorm.
 * User: aliveUser
 * Date: 24/06/2017
 * Time: 14:12
 */

namespace Agmedia\Helpers;

use Agmedia\Log\Log;

class ProductHelper
{

    /**
     * Check if the delivery is free.
     *
     * @param $product_price
     * @return string
     */
    public static function isFreeDelivery($product_price, $special_price)
    {
        if ($special_price != '' && $special_price < $product_price) {
            $product_price = $special_price;
        }

        $price = intval(preg_replace("/([^0-9])/i", "", $product_price));
        $price = number_format($price, 2, '.', '') / 100;

        return $price > AG_FREE_SHIPPING_TRESHOLD ? 'Besplatna dostava' : '';
    }

    /**
     * Change prices dots and commas
     *
     * @param $price
     * @return mixed
     */
    public static function escapePrice($price)
    {
        return str_replace(',', '.', $price);
    }
}