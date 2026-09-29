<?php
/**
 * Created by /* fj.agmedia.hr
 * Date: 16/06/2017
 * Time: 21:04
 */

namespace Agmedia\Helpers;


class StringHelper
{

    /**
     * @param $slug
     * @return string
     */
    public static function slugify($slug)
    {
        // replace non letter or digits by -
        $slug = preg_replace('~[^\pL\d]+~u', '-', $slug);
        // transliterate
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        // remove unwanted characters
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        // trim
        $slug = trim($slug, '-');
        // remove duplicate -
        $slug = preg_replace('~-+~', '-', $slug);
        // lowercase
        $slug = strtolower($slug);

        if (empty($slug)) {
            return 'n-a';
        }

        return $slug;
    }


    public static function distinct($items) {
        $result = [];
        for($i = 0; $i < sizeof($items); $i++)
            if (!array_key_exists($items[$i]['category_name'], $result)) {
                $result[$items[$i]['category_name']] = $items[$i];
            }

        return $result;
    }
}