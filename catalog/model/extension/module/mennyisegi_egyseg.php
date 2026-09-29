<?php
class ModelExtensionModuleMennyisegiEgyseg extends Model {

    public function getMennyisegiEgysegName($mennyisegi_egyseg_id) {

        $this->db->query("CREATE TABLE if not exists " . DB_PREFIX . "mennyisegi_egyseg (
                     	 	mennyisegi_egyseg_id       int(11) AUTO_INCREMENT,
                            language_id             int(11),
                            `name`                  varchar(32),
             PRIMARY KEY (mennyisegi_egyseg_id, language_id) )  engine=MyISAM default charset=UTF8");

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mennyisegi_egyseg WHERE mennyisegi_egyseg_id = '" . (int)$mennyisegi_egyseg_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return !empty($query->row['name']) ? $query->row['name'] : '';

    }


}
?>