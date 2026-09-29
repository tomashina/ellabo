<?php
/**
 * Created by /* fj.agmedia.hr
 * Date: 20/06/2017
 * Time: 14:27
 */

namespace Agmedia\Model;

use Agmedia\Database\Database;
use Agmedia\Log\Log;

class Customer
{

    /**
     * Update the quantity from stock list
     * 
     * @param $sku
     * @param $qty
     * @return bool|\stdClass
     * @throws \Exception
     */
    public static function updateReferenceID($email, $ref_id)
    {
        $db = new Database(DB_DATABASE);
        if (isset($email) && $email != '')
        {
            $customer = $db->query("UPDATE " . DB_PREFIX . "customer SET reference_id = '" . $db->escape($ref_id) . "' WHERE email = '" . $email . "'");
        }

        return $customer;
    }
    
    
    /**
     * @param $data
     *
     * @return array
     */
    public static function structureRegisterData($data)
    {
        return [
            'customer_group_id' => 2,
            'firstname' => $data['firstName'],
            'lastname' => $data['lastName'],
            'email' => $data['email'],
            'telephone' => $data['phoneNumber'],
            'custom_field' => [
                'account' => [
                    1 => $data['gender'] == 'Male' ? 1 : 2,
                    3 => $data['dateOfBirth']
                ]
            ],
            'fax' => '0',
            'company' => '',
            'address_1' => $data['address'],
            'city' => $data['city'],
            'postcode' => $data['zipCode'],
            'country_id' => 53,
            'zone_id' => 0,
            'password' => $data['referenceNumber'],
            'confirm' => $data['referenceNumber'],
            'newsletter' => $data['gdpr_privola_email'],
            'agree' => $data['termsOfUse'],
            'pravila' => $data['termsOfUse'],
            'gdpr' => $data['termsOfUse'],
        ];
    }

}