<?php
/**
 * Created by /* fj.agmedia.hr
 * Date: 24/06/2017
 * Time: 14:12
 */

namespace Agmedia\Helpers;

use Agmedia\Model\Order;
use Agmedia\Log\Log;

class OrderHelper
{

    public static function getStatusId($name)
    {
        $order = new Order();

        return $order->getStatusIdByName($name);
    }
    
}