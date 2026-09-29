<?php
/**
 * Created by fj.agmedia.hr
 * Date: 15/11/2017
 * Time: 15:30
 */

namespace Agmedia\Helpers;


class OptionHelper
{

    public static function checkOptionType($option)
    {
        if (in_array($option, self::colors())) { return 'color'; }
        if (in_array($option, self::flavours())) { return 'flavor'; }
        if (in_array($option, self::sizes())) { return 'size'; }
    }

    public static function colors()
    {
        return array(
            'Bandaže',
            'Boja',
            'Boju',
            'Guma za zube',
            'Polleo Bidon 1',
            'Polleo Bidon 2',
            'Resistance tube 2',
            'Shaker',
            'Shaker 1',
            'Shaker 2',
            'Shaker 3',
            'Shaker Wave+',
            'Štitnik za zube',
            'svoj Fenix 3',
            'Torba',
            'Torba 1',
            'Torba 2',
            'Torba akcija',
            'Traka',
            'Traka 1',
            'Traka 2',
            'Tuba 1',
            'Tuba 2',
            'Tuba 3',
            'Tube GRATIS',
            'UA Hustle Backpack',
            'Vivosmart HR+ boje',
        );
    }


    public static function flavours()
    {
        return array(
            '100% Whey Gold 450g',
            'Amino Energy 90 g',
            'Amino Energy okus',
            'Amino X okus',
            'BCAA okus',
            'Brownie okus',
            'Casein Protein okus',
            'Čokoladica 1',
            'Čokoladica 2',
            'Cookie okus',
            'Gold Whey 4,5 kg',
            'Gold Whey 908 g',
            'Gold Whey, 180 g',
            'IWP 1 kg',
            'IWP 5 kg',
            'Lean Whey',
            'Light Digest Whey 500g',
            'NO Xplode okus',
            'Okus',
            'Okus 1',
            'Okus 2',
            'Okus 3',
            'Okus Casein',
            'Okus NO Xplode',
            'Okus soka',
            'okus Syntha-6',
            'Okus Whey',
            'Okusi',
            'Peanut Butter',
            'Peanut Butter 450 g',
            'Peanut Butter 900 g',
            'Pre Workout 330 g',
            'Protein Bar okus',
            'Protein Cream okus',
            'Serious Mass okus',
            'Superbar',
            'Whipped Bites okus',
        );
    }


    public static function sizes()
    {
        return array(
            'Cage Muscle Vest',
            'Charm Leggings',
            'Coco Dress',
            'Cosmic Jacket',
            'Crossfit T-Shirt',
            'Debljina',
            'Debljina/opterećenje',
            'Divine Leggings',
            'Fly High Leggings',
            'Fly Leggings',
            'Lopta',
            'Lust Leggings',
            'Majica 1',
            'Majica 2',
            'Player Leggings',
            'Player T-shirt',
            'Polar A360',
            'Polar A370',
            'Polar Smart remen',
            'Power Leggings',
            'Rapid crne tenisice',
            'Rapid crvene tenisice',
            'Rapid plave tenisice',
            'Rukavice',
            'Sense Leggings',
            'She Leggings',
            'Tajice',
            'Tenisice',
            'Top',
            'Wanted Sport Bra',
            'Warrior Leggings',
            'ZOE T-Shirt',
        );
    }
}