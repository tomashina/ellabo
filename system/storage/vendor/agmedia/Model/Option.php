<?php
/**
 * Created by /* fj.agmedia.hr
 * Date: 20/06/2017
 * Time: 14:27
 */

namespace Agmedia\Model;

use Agmedia\Database\Database;
use Agmedia\Log\Log;

class Option
{

    /**
     * @var array $product
     */
    public static $options;
    /**
     * @var array $options_keys
     */
    public static $option_keys = ['color', 'flavor', 'size'];


    /**
     * Get all options.
     *
     * @return mixed
     * @throws \Exception
     */
    public static function all()
    {
        $db = new Database(DB_DATABASE);

        foreach (static::$option_keys as $option_key) {
            $q = $db->query("SELECT id, REPLACE(name, '\'', '') as name, code FROM " . DB_PREFIX . "product_ag_" . $option_key . " ORDER BY name");
            $options[$option_key] = $q->rows;
        }

        return $options;
    }


    /**
     * Add new option.
     * @param $request
     * @return bool|\stdClass
     */
    public static function add($request)
    {
        $db = new Database(DB_DATABASE);
        $q = false;

        foreach (static::$option_keys as $option_key) {
            if ($request->option == $option_key) {
                $options = static::all();
                $code = (count($options[$option_key]) * 2) + 1;

                try {
                    $q = $db->query("INSERT INTO " . DB_PREFIX . "product_ag_" . $option_key . " SET name = '" . $db->escape($request->option_value) . "', code = '" . $code . "', type = 'select', image = '', created_at = NOW(), updated_at = NOW()");
                } catch (\Exception $e) {
                    Log::write($e, 'options_exceptions');
                }
            }
        }

        return $q;
    }


    /**
     * Delete the option.
     *
     * @param $request
     * @return bool|\stdClass
     */
    public static function delete($request)
    {
        $db = new Database(DB_DATABASE);
        $q = false;

        foreach (static::$option_keys as $option_key) {
            if ($request->table == $option_key) {
                try {
                    $q = $db->query("DELETE FROM " . DB_PREFIX . "product_ag_" . $option_key . " WHERE code = '" . $db->escape($request->code) . "'");
                } catch (\Exception $e) {
                    Log::write($e, 'options_exceptions');
                }
            }
        }

        return $q;
    }


    /**
     * Get the Option name by code and column name.
     *
     * @param $code
     * @param $column
     * @return mixed
     * @throws \Exception
     */
    public static function getName($code, $column)
    {
        $db = new Database(DB_DATABASE);
        $option = $db->query("SELECT name FROM " . DB_PREFIX . "product_ag_" . $column . " WHERE code = '" . $code . "'");

        return $option->row['name'];
    }


}