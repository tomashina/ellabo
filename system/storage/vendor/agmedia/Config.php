<?php
/**
 * Created by fj.agmedia.hr
 * Date: 02/05/2018
 * Time: 10:23
 */

namespace Agmedia;


class Config
{

    /**
     * @var int shipping threshold. Amount above shipping is free
     */
    protected static $shipping_threshold = 40;


    /**
     * Get shipping threshold.
     * Amount above shipping is FREE.
     * 
     * @return int
     */
    public static function getShippingThreshold()
    {
        return self::$shipping_threshold;
    }
}