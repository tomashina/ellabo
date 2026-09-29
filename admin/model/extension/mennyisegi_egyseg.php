<?php
class ModelExtensionMennyisegiEgyseg extends Model {
    public function addMennyisegiEgyseg($data) {
        foreach ($data['mennyisegi_egyseg'] as $language_id => $value) {
            if (isset($mennyisegi_egyseg_id)) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "mennyisegi_egyseg SET mennyisegi_egyseg_id = '" . (int)$mennyisegi_egyseg_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
            } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "mennyisegi_egyseg SET language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");

                $mennyisegi_egyseg_id = $this->db->getLastId();
            }
        }

        $this->cache->delete('mennyisegi_egyseg');

        return $mennyisegi_egyseg_id;
    }

    public function editMennyisegiEgyseg($mennyisegi_egyseg_id, $data) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "mennyisegi_egyseg WHERE mennyisegi_egyseg_id = '" . (int)$mennyisegi_egyseg_id . "'");

        foreach ($data['mennyisegi_egyseg'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "mennyisegi_egyseg SET mennyisegi_egyseg_id = '" . (int)$mennyisegi_egyseg_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
        }

        $this->cache->delete('mennyisegi_egyseg');
    }

    public function deleteMennyisegiEgyseg($mennyisegi_egyseg_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "mennyisegi_egyseg WHERE mennyisegi_egyseg_id = '" . (int)$mennyisegi_egyseg_id . "'");

        $this->cache->delete('mennyisegi_egyseg');
    }

    public function getMennyisegiEgyseg($mennyisegi_egyseg_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mennyisegi_egyseg WHERE mennyisegi_egyseg_id = '" . (int)$mennyisegi_egyseg_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getMennyisegiEgysegek($data = array()) {

        $this->db->query("CREATE TABLE if not exists " . DB_PREFIX . "mennyisegi_egyseg (
                     	 	mennyisegi_egyseg_id       int(11) AUTO_INCREMENT,
                            language_id             int(11),
                            `name`                  varchar(32),
             PRIMARY KEY (mennyisegi_egyseg_id, language_id) )  engine=MyISAM default charset=UTF8");


        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "mennyisegi_egyseg WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'";

            $sql .= " ORDER BY name";

            if (isset($data['megyseg']) && ($data['megyseg'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);

            return $query->rows;
        } else {
            $mennyisegi_egyseg_data = $this->cache->get('mennyisegi_egyseg.' . (int)$this->config->get('config_language_id'));

            if (!$mennyisegi_egyseg_data) {
                $query = $this->db->query("SELECT mennyisegi_egyseg_id, name FROM " . DB_PREFIX . "mennyisegi_egyseg WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name");

                $mennyisegi_egyseg_data = $query->rows;

                $this->cache->set('mennyisegi_egyseg.' . (int)$this->config->get('config_language_id'), $mennyisegi_egyseg_data);
            }

            return $mennyisegi_egyseg_data;
        }
    }

    public function getMennyisegiEgysegDescriptions($mennyisegi_egyseg_id) {
        $mennyisegi_egyseg_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mennyisegi_egyseg WHERE mennyisegi_egyseg_id = '" . (int)$mennyisegi_egyseg_id . "'");

        foreach ($query->rows as $result) {
            $mennyisegi_egyseg_data[$result['language_id']] = array('name' => $result['name']);
        }

        return $mennyisegi_egyseg_data;
    }

    public function getTotalMennyisegiEgysegek() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "mennyisegi_egyseg WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row['total'];
    }

    public function getTotalByMennyisegiEgysegId($mennyisegi_egyseg_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product` WHERE mennyisegi_egyseg_id = '" . (int)$mennyisegi_egyseg_id . "'");

        return $query->row['total'];
    }


}