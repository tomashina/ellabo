<?php
/**
 * Created by /* fj.agmedia.hr
 * Date: 17/06/2017
 * Time: 00:33
 */

namespace Agmedia\Helpers;

use Agmedia\Helpers;
use Agmedia\Log\Log;

class Sort
{




    /**
     *  Return sorted category data from raw database query
     *
     * @param $data
     * @return array
     */
    public static function oldCategories($row, $name)
    {
        return array(
            'name'              => $row['name'],
            'image'             => $row['image'],
            'sort_order'        => $row['sort_order'],
            'description'       => $row['description'],
            'meta_description'  => $row['meta_description'],
            'meta_title'        => $row['name'],
            'meta_keyword'      => $row['meta_keyword'],
            'keyword'           => StringHelper::slugify( $row['name'] ),
            'column'            => $row['column'],
            'parent_id'         => $row['parent_id'],
            'parent_name'       => $name,
            'language_id'       => 1,
            'top'               => $row['top'],
            'manufacturer_store'=> array(0),
        );
    }


    /**
     *  Return sorted options data from raw database query
     *
     * @param $data
     * @return array
     */
    public static function oldOptions($row, $values)
    {
        $arr = array();

        foreach ($values as $item) {
            $arr[] = array(
                'option_value_id'   => $item['option_value_id'],
                'option_id'         => $item['option_id'],
                'image'             => $item['image'],
                'sort_order'        => $item['sort_order'],
                'language_id'       => 1,
                'name'              => $item['name'],
            );
        }

        return array(
            'option_id'     => $row['option_id'],
            'type'          => $row['type'],
            'sort_order'    => $row['sort_order'],
            'language_id'   => 1,
            'name'          => $row['name'],
            'option_value'  => $arr,
        );
    }


    /**
     *  Return sorted products data from raw database query
     *
     * @param $data
     * @return array
     */
    public static function oldProducts($row, $manufacturer, $images, $options, $categories, $specials)
    {
        $imgs = array();
        foreach ($images as $item) {
            $imgs[] = array(
                'image'         => $item['image'],
                'sort_order'    => $item['sort_order'],
            );
        }

        $opts = array();
        foreach ($options as $item) {
            $opts[] = $item;
        }

        $cats = array();
        foreach ($categories as $item) {
            $cats[] = $item;

            //Log::write($item, 'products');
        }


        //Log::write($cats, 'products');

        $specs = array();
        foreach ($specials as $item) {
            $specs[] = array(
                'product_id'    => $item['product_id'],
                'price'         => $item['price'],
                'date_start'    => $item['date_start'],
                'date_end'      => $item['date_end'],
            );
        }

        $row['manufacturer_name'] = $manufacturer;
        $row['product_images'] = $imgs;
        $row['product_options'] = $opts;
        $row['categories'] = StringHelper::distinct($cats);
        $row['specials'] = $specs;
        $row['meta_title'] = StringHelper::slugify($row['name']);
        $row['keyword'] = StringHelper::slugify($row['name']);

        return $row;
    }


    /**
     *  Return sorted customers data from raw database query
     *
     * @param $data
     * @return array
     */
    public static function oldCustomers($customer, $addresses)
    {
        $arr = array();

        foreach ( $addresses as $row ) {
            $customer['addresses'] = $addresses;
            $arr = $customer;
        }

        return $arr;
    }


    /**
     *  Return sorted reviews data from raw database query
     *
     * @param $data
     * @return array
     */
    public static function oldReviews($review, $product_name, $customer_email)
    {
        $review['product_name'] = $product_name;
        $review['customer_email'] = $customer_email;

        return $review;
    }


    /**
     *  Return sorted informations data from raw database query
     *
     * @param $data
     * @return array
     */
    public static function oldInformations($data)
    {
        $arr = array();

        foreach ( $data as $row ) {
            $arr[] = $row;
        }

        return $arr;
    }
}