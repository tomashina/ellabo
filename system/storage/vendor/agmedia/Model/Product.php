<?php
/**
 * Created by /* fj.agmedia.hr
 * Date: 20/06/2017
 * Time: 14:27
 */

namespace Agmedia\Model;

use Agmedia\Database\Database;
use Agmedia\Log\Log;

class Product
{

    /**
     * @var array $product
     */
    public static $product;

    public static $option_keys = ['flavor', 'color', 'size'];

    /**
     * Add's the product from exported data from old database.
     * Receives the options boolean and imports the product
     * accorindinly. With or without options.
     *
     * @param array $product
     * @param bool $options
     */
    public static function add($product, $options = false)
    {
        //Log::write($options, 'options');
        
        if ($options) {
            self::$product = (new \Agmedia\Builder\Product())->addOptionProduct($product);
        } else {
            self::$product = (new \Agmedia\Builder\Product())->addNonOptionProduct($product);;
        }
    }

    /**
     * Get option name
     *
     * @param $product_id
     * @return array|bool
     * @throws \Exception
     */
    public static function getOptionName($product_id)
    {
        $db = new Database(DB_DATABASE);

        $_product = $db->query("SELECT product_id, panid, sku, panc, pans, panf, quantity FROM " . DB_PREFIX . "product WHERE product_id = '" . intval($product_id) . "'");
        $product = $_product->row;
        $options = [];

        if ($product['panc'] != '000') {
            $_options = $db->query("SELECT id, REPLACE(name, '\'', '') as name, code FROM " . DB_PREFIX . "product_ag_color WHERE code = '" . $product['panc'] . "'");
            $options = $_options->row;
            $options['type'] = 'color';
        }
        if ($product['pans'] != '000') {
            $_options = $db->query("SELECT id, REPLACE(name, '\'', '') as name, code FROM " . DB_PREFIX . "product_ag_size WHERE code = '" . $product['pans'] . "'");
            $options = $_options->row;
            $options['type'] = 'size';
        }
        if ($product['panf'] != '000') {
            $_options = $db->query("SELECT id, REPLACE(name, '\'', '') as name, code FROM " . DB_PREFIX . "product_ag_flavor WHERE code = '" . $product['panf'] . "'");
            $options = $_options->row;
            $options['type'] = 'flavor';
        }

        $options['quantity']    = $_product->row['quantity'];
        $options['sku'] = $_product->row['sku'];

        return $options;
    }

    /**
     * Get options
     *
     * @param $product_id
     * @return array|bool
     * @throws \Exception
     */
    public static function getOptions($product_id, $status = false)
    {
        $db = new Database(DB_DATABASE);

        $_product = $db->query("SELECT product_id, panid, sku, panc, pans, panf FROM " . DB_PREFIX . "product WHERE product_id = '" . intval($product_id) . "'");
        $product = $_product->row;

        //Log::write($product, 'testing_options');

        if ($product['panc'] != '000' || $product['pans'] != '000' || $product['panf'] != '000') {
            $options = [];
            if ($status) {
                $products = $db->query("SELECT product_id, sku, panc, pans, panf, quantity FROM " . DB_PREFIX . "product WHERE panid = '" . $product['panid'] . "'");
            } else {
                $products = $db->query("SELECT product_id, sku, panc, pans, panf, quantity FROM " . DB_PREFIX . "product WHERE panid = '" . $product['panid'] . "' AND status = 1");
            }

            foreach ($products->rows as $item) {
                if ($item['panc'] != '000') {
                    $_options = $db->query("SELECT id, REPLACE(name, '\'', '') as name, code FROM " . DB_PREFIX . "product_ag_color WHERE code = '" . $item['panc'] . "'");
                    $_options->row['product_id']    = $item['product_id'];
                    $_options->row['sku']           = $item['sku'];
                    $_options->row['quantity']      = $item['quantity'];

                    $options['color'][] = $_options->row;
                }
                if ($item['pans'] != '000') {
                    $_options = $db->query("SELECT id, REPLACE(name, '\'', '') as name, code FROM " . DB_PREFIX . "product_ag_size WHERE code = '" . $item['pans'] . "'");
                    $_options->row['product_id']    = $item['product_id'];
                    $_options->row['sku']           = $item['sku'];
                    $_options->row['quantity']      = $item['quantity'];

                    $options['size'][] = $_options->row;
                }
                if ($item['panf'] != '000') {
                    $_options = $db->query("SELECT id, REPLACE(name, '\'', '') as name, code FROM " . DB_PREFIX . "product_ag_flavor WHERE code = '" . $item['panf'] . "'");
                    $_options->row['product_id']    = $item['product_id'];
                    $_options->row['sku']           = $item['sku'];
                    $_options->row['quantity']      = $item['quantity'];

                    $options['flavor'][] = $_options->row;
                }
            }

            return $options;
        }

        return false;
    }
    
    
    /**
     * Get options
     *
     * @param $product_id
     * @return array|bool
     * @throws \Exception
     */
    public static function getComboOptions($product_id, $combos)
    {
        $db = new Database(DB_DATABASE);
        
        $_product = $db->query("SELECT product_id, panid, sku, panc, pans, panf FROM " . DB_PREFIX . "product WHERE product_id = '" . intval($product_id) . "'");
        $product = $_product->row;
        
        //Log::write($product, 'testing_options');
        
        if ($product['panc'] != '000' || $product['pans'] != '000' || $product['panf'] != '000') {
            $options = [];
            
            foreach ($combos as $combo_id) {
                $combo_prod = $db->query("SELECT product_id, sku, panc, pans, panf, quantity FROM " . DB_PREFIX . "product WHERE panid = '" . $product['panid'] . "' AND product_id = '" . $combo_id . "' AND status = 1");
                $combo_product = $combo_prod->row;
                
                if ($combo_product['panc'] != '000') {
                    $_options = $db->query("SELECT id, REPLACE(name, '\'', '') as name, code FROM " . DB_PREFIX . "product_ag_color WHERE code = '" . $combo_product['panc'] . "'");
                    $_options->row['product_id']    = $combo_product['product_id'];
                    $_options->row['sku']           = $combo_product['sku'];
                    $_options->row['quantity']      = $combo_product['quantity'];
                    
                    $options['color'][] = $_options->row;
                }
                if ($combo_product['pans'] != '000') {
                    $_options = $db->query("SELECT id, REPLACE(name, '\'', '') as name, code FROM " . DB_PREFIX . "product_ag_size WHERE code = '" . $combo_product['pans'] . "'");
                    $_options->row['product_id']    = $combo_product['product_id'];
                    $_options->row['sku']           = $combo_product['sku'];
                    $_options->row['quantity']      = $combo_product['quantity'];
                    
                    $options['size'][] = $_options->row;
                }
                if ($combo_product['panf'] != '000') {
                    $_options = $db->query("SELECT id, REPLACE(name, '\'', '') as name, code FROM " . DB_PREFIX . "product_ag_flavor WHERE code = '" . $combo_product['panf'] . "'");
                    $_options->row['product_id']    = $combo_product['product_id'];
                    $_options->row['sku']           = $combo_product['sku'];
                    $_options->row['quantity']      = $combo_product['quantity'];
                    
                    $options['flavor'][] = $_options->row;
                }
            }
            
            return $options;
        }
        
        return false;
    }
    

    /**
     * Destroy product
     *
     * @param $product_id
     * @throws \Exception
     */
    public static function destroy($product_id)
    {
        $db = new Database(DB_DATABASE);

        $db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "product_recurring WHERE product_id = " . (int)$product_id);
        $db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "'");
        $db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE product_id = '" . (int)$product_id . "'");
        
    }

    /**
     * Return option keys
     *
     * @return array
     */
    public static function getOptionKeys()
    {
        return static::$option_keys;
    }
    
    /**
     * Get the product by SKU
     *
     * @param $sku
     * @return bool|\stdClass
     * @throws \Exception
     */
    public static function getProductBySku($sku)
    {
        $db = new Database(DB_DATABASE);
        
        return $db->query("SELECT * FROM " . DB_PREFIX . "product WHERE sku = '" . $sku . "'");
    }
    
    
    /**
     * Get the product SKU by id
     *
     * @param $sku
     * @return bool|\stdClass
     * @throws \Exception
     */
    public static function getProductSkuFromID($id)
    {
        $db = new Database(DB_DATABASE);
    
        $query = $db->query("SELECT sku FROM " . DB_PREFIX . "product WHERE product_id = '" . $id . "'");
    
        return $query->row['sku'];
    }
    

    /**
     * Get the product by SKU
     *
     * @param $sku
     * @return bool|\stdClass
     * @throws \Exception
     */
    public static function getProductsByPANID($id)
    {
        $db = new Database(DB_DATABASE);

        $query = $db->query("SELECT * FROM " . DB_PREFIX . "product WHERE panid = '" . $id . "'");

        return $query->rows;
    }

    /**
     * Return PAN id from product SKU
     *
     * @param $product_id
     * @return mixed
     */
    public static function getPANid($sku)
    {
        $db = new Database(DB_DATABASE);
        $product = $db->query("SELECT product_id, panid, sku FROM " . DB_PREFIX . "product WHERE sku = '" . $sku . "'");

        return $product->row['panid'];
    }

    /**
     * Return PAN id from product ID
     *
     * @param $product_id
     * @return mixed
     */
    public static function getPANfromID($id)
    {
        $db = new Database(DB_DATABASE);
        $product = $db->query("SELECT panid FROM " . DB_PREFIX . "product WHERE product_id = '" . $id . "'");

        return $product->row['panid'];
    }

    /**
     * Get product regular price
     * 
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public static function getRegularPrice($id)
    {
        $db = new Database(DB_DATABASE);
        $product = $db->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id = '" . $id . "'");

        return $product->row['price'];
    }

    /**
     * Update the quantity from stock list
     * 
     * @param $sku
     * @param $qty
     * @return bool|\stdClass
     * @throws \Exception
     */
    public static function updateQty($sku, $qty)
    {
        $db = new Database(DB_DATABASE);
        if (isset($sku) && $sku != '')
        {
            $product = $db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . $qty . "' WHERE sku = '" . $sku . "'");
        }

        return $product;
    }

    /**
     * Update the price from list
     *
     * @param $sku
     * @param $qty
     * @return bool|\stdClass
     * @throws \Exception
     */
    public static function updatePrice($sku, $price)
    {
        $db = new Database(DB_DATABASE);
        $product = $db->query("UPDATE " . DB_PREFIX . "product SET price = '" . $price . "' WHERE sku = '" . $sku . "'");

        return $price;
    }

    /**
     * Update the action price from list
     *
     * @param $sku
     * @param $qty
     * @return bool|\stdClass
     * @throws \Exception
     */
    public static function updateActionPrice($sku, $discount, $duration)
    {
        $db = new Database(DB_DATABASE);
        $product = $db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE sku = '" . $sku . "'");

        if (isset($product->row['product_id'])) {
            $db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $product->row['product_id'] . "'");

            $duration = date('d.m.Y', strtotime($duration. ' + 1 days'));
            $date = date_create_from_format('d.m.Y', $duration);
    
            $db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product->row['product_id'] . "', customer_group_id = 1, priority = 0, price = " . $discount . ", date_start = NOW(), date_end = '" . date_format($date, 'Y-m-d') . "';");
            $db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product->row['product_id'] . "', customer_group_id = 2, priority = 0, price = " . $discount . ", date_start = NOW(), date_end = '" . date_format($date, 'Y-m-d') . "';");
        }

        return $discount;
    }

    /**
     * Delete the action price from database
     *
     * @param $sku
     * @return mixed
     * @throws \Exception
     */
    public static function deleteActionPrice($sku)
    {
        $db = new Database(DB_DATABASE);
        $product = $db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE sku = '" . $sku . "'");

        $deleted = false;
        if (isset($product->row['product_id'])) {
            $deleted = $db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $product->row['product_id'] . "'");
        }

        return $deleted;
    }

    /**
     * Get product name
     *
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public static function getName($id)
    {
        $db = new Database(DB_DATABASE);
        $product = $db->query("SELECT name FROM " . DB_PREFIX . "product_description WHERE product_id = '" . $id . "'");

        return $product->row['name'];
    }

    /**
     * Get all products list
     *
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public static function getlist()
    {
        $db = new Database(DB_DATABASE);
        $products = $db->query("SELECT sku FROM " . DB_PREFIX . "product");
        $prods = [];

        foreach ($products->rows as $row) {
            array_push($prods, $row['sku']);
        }

        return $prods;
    }

    /**
     * Helper function to sort Sku's from combo string
     *
     * @param $str
     * @return array
     */
    public static function explodeCombo($str)
    {
        $str = str_replace('[', '', $str);
        $str = str_replace(']', '', $str);
        $str = str_replace('"', '', $str);

        return explode(',', $str);
    }

    /**
     * Calculate discount between two prices
     *
     * @param $regular_price
     * @param $action_price
     * @return float
     */
    public static function calculateComboDiscount($regular_price, $action_price)
    {
        return (($regular_price - $action_price) / $regular_price) * 100;
    }

    /**
     * Check if Product is on action
     * and return necessery data.
     *
     * @param $product_id
     * @param $actions
     * @return array|bool
     */
    public static function checkIfOnAction($product_id, $actions)
    {
        if (isset($actions))
        {
            foreach ($actions as $action) {
                if (in_array($product_id, explode(',',$action['products']))) {
                    return [
                        'value' => true,
                        'name' => $action['name'],
                        'path' => $action['icon']
                    ];
                }
            }
        }

        return false;
    }

    /**
     * Resolves 1+1 action
     * and saves it to database.
     *
     * @param $panid
     * @param $active
     * @return bool|\stdClass
     * @throws \Exception
     */
    public static function resolvePlusOneAction($panid, $active)
    {
        $db = new Database(DB_DATABASE);

        if ($active == 'true') {
            return $db->query("UPDATE " . DB_PREFIX . "product SET plus_one = 1 WHERE panid = '" . $panid . "'");
        }

        return $db->query("UPDATE " . DB_PREFIX . "product SET plus_one = 0 WHERE panid = '" . $panid . "'");
    }
    
    
    /**
     * @param $price
     * @param $sku
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public static function saveLoyaltyPrice($price, $sku)
    {
        $db = new Database(DB_DATABASE);
        
        return $db->query("UPDATE " . DB_PREFIX . "product SET loyalty_price = '" . $price . "' WHERE sku = '" . $sku . "'");
    }
}