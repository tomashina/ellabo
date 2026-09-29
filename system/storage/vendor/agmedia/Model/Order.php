<?php
/**
 * Created by fj.agmedia.hr
 * Date: 31/07/2017
 * Time: 09:34
 */

namespace Agmedia\Model;


use Agmedia\Database\Database;
use Agmedia\Log\Log;

class Order
{

    /**
     * Get the Order products.
     * Attach regular price, SKU & option name to each.
     *
     * @param $order_id
     * @return array
     * @throws \Exception
     */
    public static function getProducts($order_id)
    {
        $db = new Database(DB_DATABASE);
        
        $products = $db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
        $_prods = [];

        foreach ($products->rows as $product) {
            $prod = $db->query("SELECT sku, price FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product['product_id'] . "'");

            $product['regular_price'] = $prod->row['price'];
            $product['sku'] = $prod->row['sku'];
            $product['option_name'] = Product::getOptionName($product['product_id']);

            $_prods[] = $product;
        }

        Log::write('Order::getProducts() returned.', 'gath_ws_process');
        Log::write($_prods, 'gath_ws_process');
        
        return $_prods;
    }


    /**
     * Get Gath order data
     *
     * @param $order_id
     * @return mixed
     * @throws \Exception
     */
    public static function getGathData($order_id)
    {
        $db = new Database(DB_DATABASE);

        $data = $db->query("SELECT * FROM " . DB_PREFIX . "order_gath WHERE order_id = '" . (int)$order_id . "'");

        return $data->row;
    }


    /**
     * Get Gath order reservations data
     *
     * @param $order_id
     * @return mixed
     * @throws \Exception
     */
    public static function getGathReservationData($order_id)
    {
        $db = new Database(DB_DATABASE);

        $data = $db->query("SELECT * FROM " . DB_PREFIX . "order_gath_reservations WHERE order_id = '" . (int)$order_id . "'");

        return $data->row;
    }


    /**
     * Get Gath product reservations data
     *
     * @param $order_id
     * @return mixed
     * @throws \Exception
     */
    public static function getGathProductReservationData($order_id, $product_id)
    {
        $db = new Database(DB_DATABASE);

        $data = $db->query("SELECT * FROM " . DB_PREFIX . "order_gath_reservations WHERE order_id = '" . (int)$order_id . "' AND product_id = '" . (int)$product_id . "'");

        return $data->row;
    }


    /**
     * Set Gath order WebShop reservation data
     *
     * @param $order_id
     * @return mixed
     * @throws \Exception
     */
    public static function setGathWSReservationData($order_id, $products)
    {
        $db = new Database(DB_DATABASE);

        foreach ($products as $product) {
            $db->query("INSERT INTO " . DB_PREFIX . "order_gath_reservations SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', reservation = '01', response = 'none', value = 'Reserved in WebShop.', status = 1, date_added = NOW(), date_modified = NOW()");
        }

        return true;
    }


    /**
     * Set Gath order Single Store reservation data
     *
     * @param $order_id
     * @return mixed
     * @throws \Exception
     */
    public static function setGathSingleStoreReservationData($order_id, $products, $reservation)
    {
        $db = new Database(DB_DATABASE);

        foreach ($products as $product) {
            $db->query("INSERT INTO " . DB_PREFIX . "order_gath_reservations SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', reservation = '" . $reservation['store_id'] . "', value = 'Reserved in " . $reservation['store'] . "', date_added = NOW(), date_modified = NOW()");
        }

        return true;
    }


    /**
     * Set Gath order WebShop reservation data
     *
     * @param $order_id
     * @return mixed
     * @throws \Exception
     */
    public static function saveGathReservationResponse($order_id, $response, $products = false)
    {
        $db = new Database(DB_DATABASE);

        $reservation = new \SimpleXMLElement($response);

        if ($products) {
            foreach ($products as $product) {
                if ($reservation->BrojPNK) {
                    $db->query("UPDATE " . DB_PREFIX . "order_gath_reservations SET response = '" . $reservation->BrojPNK . "', status = 1 WHERE order_id = '" . (int)$order_id . "' AND product_id = '" . (int)$product['product_id'] . "'");
                    $db->query("UPDATE " . DB_PREFIX . "order_gath SET reservations = 1 WHERE order_id = '" . (int)$order_id . "'");
                } else {
                    $db->query("UPDATE " . DB_PREFIX . "order_gath_reservations SET response = '" . $reservation->Message . "' WHERE order_id = '" . (int)$order_id . "' AND product_id = '" . (int)$product['product_id'] . "'");
                }
            }
        } else {
            if ($reservation->BrojPNK) {
                $db->query("UPDATE " . DB_PREFIX . "order_gath_reservations SET response = '" . $reservation->BrojPNK . "', status = 1 WHERE order_id = '" . (int)$order_id . "'");
                $db->query("UPDATE " . DB_PREFIX . "order_gath SET reservations = 1 WHERE order_id = '" . (int)$order_id . "'");
            } else {
                $db->query("UPDATE " . DB_PREFIX . "order_gath_reservations SET response = '" . $reservation->Message . "' WHERE order_id = '" . (int)$order_id . "'");
            }
        }

        return true;
    }
}