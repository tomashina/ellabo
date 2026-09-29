<?php
// catalog/controller/extension/module/ag_specials_cron.php

use Agmedia\Services\ImportXML;
use Agmedia\Helpers\Log;

class ControllerExtensionModuleAgSpecialsCron extends Controller
{
    /**
     * /index.php?route=extension/module/ag_specials_cron/updateSpecialPricesUnified&key=YOUR_KEY
     */
    public function updateSpecialPricesUnified()
    {
        // Jednostavna API zaštita preko ključa (isti kao u adminu)
        if (!$this->validateKey($this->request->get)) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['error' => 'Unauthorized!']));
            return;
        }

        // oba feeda odjednom
        $feeds = [
            defined('IMPORT_XML_STOCK_URL') ? IMPORT_XML_STOCK_URL : null,
            defined('IMPORT_XML_STOCK_URL_DVA') ? IMPORT_XML_STOCK_URL_DVA : null,
        ];

        // izbaciti null-ove ako koji constant nije definiran
        $feeds = array_values(array_filter($feeds));

        $result = $this->runUnifiedSpecialsUpdate($feeds);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    /**
     * CORE: Atomic update specials iz više feedova, uz pravilo popusta ≥5% (New < Old)
     * - TEMPORARY staging tablice (bez TRUNCATE)
     * - product.price = OldPrice; product_special.price = NewPrice (ako ispunjava ≥5%)
     */
    private function runUnifiedSpecialsUpdate(array $feeds)
    {
        if (empty($feeds)) {
            return ['error' => 'No feeds configured'];
        }

        $stageTable = DB_PREFIX . 'stage_sp_' . uniqid('', true);
        $pidTable   = $stageTable . '_pid';

        try {
            $this->db->query("START TRANSACTION");

            // TEMPORARY staging s obje cijene
            $this->db->query("
                CREATE TEMPORARY TABLE `{$stageTable}` (
                    `Sifra`        VARCHAR(64)   NOT NULL,
                    `Raspolozivo`  INT           NOT NULL DEFAULT 0,
                    `NewPrice`     DECIMAL(15,4) NOT NULL DEFAULT 0, -- MlpOsnovica (nova)
                    `OldPrice`     DECIMAL(15,4) NOT NULL DEFAULT 0, -- MlpOsnovicaOld (stara)
                    PRIMARY KEY (`Sifra`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $batchSize = 1000;

            foreach ($feeds as $url) {
                $xml = new ImportXML(simplexml_load_file($url));
                $rows = $xml->setSpecialCodes(); // očekuje: Sifra, Raspolozivo, MlpOsnovica (new), MlpOsnovicaOld (old)

                $batch = [];
                foreach ($rows as $row) {
                    $sifra       = $this->db->escape((string)$row['Sifra']);
                    $raspolozivo = (int)$row['Raspolozivo'];
                    $new         = (float)str_replace(',', '.', (string)$row['MlpOsnovica']);
                    $old         = (float)str_replace(',', '.', (string)$row['MlpOsnovicaOld']);

                    $batch[] = "('{$sifra}',{$raspolozivo},{$new},{$old})";

                    if (count($batch) >= $batchSize) {
                        $values = implode(',', $batch);
                        $this->db->query("
                            INSERT INTO `{$stageTable}` (`Sifra`,`Raspolozivo`,`NewPrice`,`OldPrice`)
                            VALUES {$values}
                            ON DUPLICATE KEY UPDATE
                                `Raspolozivo` = VALUES(`Raspolozivo`),
                                `NewPrice`    = VALUES(`NewPrice`),
                                `OldPrice`    = VALUES(`OldPrice`)
                        ");
                        $batch = [];
                    }
                }

                if (!empty($batch)) {
                    $values = implode(',', $batch);
                    $this->db->query("
                        INSERT INTO `{$stageTable}` (`Sifra`,`Raspolozivo`,`NewPrice`,`OldPrice`)
                        VALUES {$values}
                        ON DUPLICATE KEY UPDATE
                            `Raspolozivo` = VALUES(`Raspolozivo`),
                            `NewPrice`    = VALUES(`NewPrice`),
                            `OldPrice`    = VALUES(`OldPrice`)
                    ");
                }
            }

            // Update product (osnovna = stara cijena)
            $this->db->query("
                UPDATE `" . DB_PREFIX . "product` p
                INNER JOIN `{$stageTable}` s ON p.model = s.Sifra
                SET p.quantity = s.Raspolozivo,
                    p.price    = s.OldPrice
                WHERE s.OldPrice > 0
            ");

            // Privremena tablica s product_id
            $this->db->query("
                CREATE TEMPORARY TABLE `{$pidTable}` (
                    `product_id` INT NOT NULL,
                    PRIMARY KEY (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $this->db->query("
                INSERT INTO `{$pidTable}` (`product_id`)
                SELECT p.product_id
                FROM `" . DB_PREFIX . "product` p
                INNER JOIN `{$stageTable}` s ON p.model = s.Sifra
            ");

            // Obriši specials samo za pogođene proizvode
            $this->db->query("
                DELETE ps FROM `" . DB_PREFIX . "product_special` ps
                INNER JOIN `{$pidTable}` x ON x.product_id = ps.product_id
            ");

            // Uvjet: New < Old i popust ≥ 5%
            $discountWhere = "
                s.OldPrice > 0
                AND s.NewPrice < s.OldPrice
                AND ((s.OldPrice - s.NewPrice) / s.OldPrice) >= 0.05
            ";

            // Specials (grupa 1)
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "product_special`
                    (product_id, customer_group_id, priority, price, date_start, date_end)
                SELECT p.product_id, 1, 1, s.NewPrice, NULL, NULL
                FROM `" . DB_PREFIX . "product` p
                INNER JOIN `{$stageTable}` s ON p.model = s.Sifra
                WHERE {$discountWhere}
            ");

            // Specials (grupa 2)
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "product_special`
                    (product_id, customer_group_id, priority, price, date_start, date_end)
                SELECT p.product_id, 2, 1, s.NewPrice, NULL, NULL
                FROM `" . DB_PREFIX . "product` p
                INNER JOIN `{$stageTable}` s ON p.model = s.Sifra
                WHERE {$discountWhere}
            ");

            $this->db->query("COMMIT");

            return ['status' => 'ok', 'updated' => true];

        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
            if (class_exists('Log') && method_exists('Log', 'stockPile')) {
                Log::stockPile('ERR specials unified (catalog): ' . $e->getMessage(), 'specs_error');
            }
            return ['error' => 'Update failed', 'msg' => $e->getMessage()];
        }
    }

    /** Jednostavna zaštita ključa (koristi IMPORT_XML_KEY iz configa) */
    private function validateKey($key)
    {
        return (isset($key['key']) && defined('IMPORT_XML_KEY') && $key['key'] == IMPORT_XML_KEY);
    }
}
