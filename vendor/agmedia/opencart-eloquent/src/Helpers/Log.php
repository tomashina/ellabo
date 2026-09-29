<?php
/**
 * User: fj.agmedia.hr
 * Date: 16/06/2017
 * Time: 14:36
 */

namespace Agmedia\Helpers;


class Log {

    /**
     * Logs the data with month folders and
     * day concat to file name.
     *
     * @param $message
     * @param string $filename
     */
    public static function info($message, $filename = 'info') {
        $month = date('m');
        $day = date('d');
        
        if (!is_dir(DIR_LOGS . $month)) {
            mkdir(DIR_LOGS . $month, 0755, true);
        }
        
        $handle = fopen(DIR_LOGS . $month . '/' . $filename . '_' . $day . '.log', 'a');
        
        fwrite($handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
        fclose($handle);
    }
    
    
    /**
     * @param        $message
     * @param string $filename
     */
    public static function stockPile($message, $filename = 'info') {
        $handle = fopen(DIR_LOGS . $filename . '.log', 'a');
        
        fwrite($handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
        fclose($handle);
    }
}