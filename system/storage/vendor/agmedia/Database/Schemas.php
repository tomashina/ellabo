<?php
/**
 * Created by /* fj.agmedia.hr
 * Date: 18/06/2017
 * Time: 22:06
 */
namespace Agmedia\Database;

use Agmedia\Database\Database;
use Agmedia\Log\Log;

class Schemas
{

    /**
     * @var $db private
     */
    private $db;

    /**
     * Schemas constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->db = new Database($name);
    }


    /**
     * @throws \Exception
     */
    public function correctProductOptionTable()
    {
        $po = $this->db->query("DESCRIBE " . DB_PREFIX . "product_option_value");
        $a = false;
        $e = false;

        foreach ($po->rows as $column) {
            if ($column['Field'] == 'sku') {
                $a = true;
            }
            if ($column['Field'] == 'ean') {
                $e = true;
            }
        }

        if (!$a) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "product_option_value ADD sku VARCHAR(55) NULL");
        }
        if (!$e) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "product_option_value ADD ean VARCHAR(55) NULL");
        }
    }


    /**
     * @throws \Exception
     */
    public function correctCustomerTable()
    {
        $c = $this->db->query("DESCRIBE " . DB_PREFIX . "customer");
        $b = 0;
        $s = 0;

        foreach ($c->rows as $column) {
            if ($column['Field'] == 'birthday') {
                $b = true;
            }
            if ($column['Field'] == 'sex') {
                $s = true;
            }
        }

        if (!$b) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD birthday VARCHAR(50) NULL");
        }
        if (!$s) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "customer ADD sex VARCHAR(50) NULL");
        }
    }


    public function correctOrderTable()
    {
        $m = $this->db->query("DESCRIBE " . DB_PREFIX . "order");
        $a = false;
        $t = false;

        //Log::write($m, 'base');

        foreach ($m->rows as $column) {
            if ($column['Field'] == 'shipped') {
                $a = true;
            }
            if ($column['Field'] == 'invoice_no' && $column['Type'] == 'int(11)') {
                $t = true;
            }
        }

        if (!$a) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "order ADD shipped INT(11) NULL");
            $this->db->query("ALTER TABLE " . DB_PREFIX . "order ADD shipped_pending INT(11) NULL");
        }
        if ($t) {
            //Log::write($t, 'order');
            $this->db->query("ALTER TABLE " . DB_PREFIX . "order MODIFY invoice_no VARCHAR(50) NOT NULL default '0'");
        }
    }
}