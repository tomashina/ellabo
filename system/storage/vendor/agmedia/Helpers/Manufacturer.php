<?php
/**
 * Created by /* fj.agmedia.hr
 * Date: 20/06/2017
 * Time: 11:40
 */

namespace Agmedia\Helpers;

use Agmedia\Database\Database;
use Agmedia\Log\Log;

class Manufacturer
{

    /**
     *  Returns the name of manufacturer based on given product ID
     *
     * @param $product_id
     * @return mixed
     * @throws \Exception
     */
    public static function getNameFromProductID($product_id)
    {
        $conn = new Database(DB_DATABASE);
        $product = $conn->query('SELECT p.manufacturer_id FROM ' . DB_PREFIX . 'product p WHERE p.product_id = ' . (int)$product_id);

        if (isset($product->row['manufacturer_id'])) {
            $manufacturer = $conn->query('SELECT m.name FROM ' . DB_PREFIX . 'manufacturer m WHERE m.manufacturer_id = ' . (int)$product->row['manufacturer_id']);

            return isset($manufacturer->row['name']) ? $manufacturer->row['name'] : 'Unknown!';
        }

        return 'Unknown!';
    }

    /**
     *  Returns the name of manufacturer based on given manufacturer name
     *
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public static function getIdFromName($name)
    {
        $db = new Database(DB_DATABASE);
        $manufacturer = $db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE name = '" . $name . "'");

        return isset($manufacturer->row['manufacturer_id']) ? $manufacturer->row['manufacturer_id'] : false;
    }

    /**
     *  Return sorted manufacturer data from raw database query
     *
     * @param $data
     * @return array
     */
    public static function sort($data)
    {
        $arr = array();

        foreach ( $data as $row ) {
            $arr[] = array(
                'name'              => $row['name'],
                'image'             => $row['image'],
                'sort_order'        => $row['sort_order'],
                'description'       => $row['description'],
                'meta_description'  => $row['meta_description'],
                'meta_keyword'      => $row['meta_keyword'],
                'keyword'           => StringHelper::slugify( $row['name'] ),
                'manufacturer_store'=> array(0),
            );
        }

        return $arr;
    }
}