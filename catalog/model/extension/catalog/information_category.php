<?php
class ModelExtensionCatalogInformationCategory extends Model {
	public function getInformation($information_id) {
		$query = $this->db->query("SELECT DISTINCT i.*, id.*, i2c.information_category_id, icd.name, ic.information_id as info FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) LEFT JOIN " . DB_PREFIX . "information_to_category i2c ON (i.information_id = i2c.information_id) LEFT JOIN " . DB_PREFIX . "information_category ic ON (i2c.information_category_id = ic.information_category_id) LEFT JOIN " . DB_PREFIX . "information_category_description icd ON (i2c.information_category_id = icd.information_category_id AND icd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE i.information_id = '" . (int)$information_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

		return $query->row;
	}

	public function getInformations($filter = '') {
		$sql = "SELECT *, i.sort_order AS sort_order, i.fa AS fa, i.information_id AS information_id, i2c.information_category_id AS information_category_id FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) LEFT JOIN " . DB_PREFIX . "information_to_category i2c ON (i.information_id = i2c.information_id) LEFT JOIN " . DB_PREFIX . "information_category ic ON (i2c.information_category_id = ic.information_category_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'";

		if ($filter == 'top') {
			$sql .= " AND i.top = 1 AND NOT EXISTS (SELECT i2c2.information_category_id FROM " . DB_PREFIX . "information_to_category i2c2 LEFT JOIN " . DB_PREFIX . "information_category ic2 ON (ic2.information_category_id = i2c2.information_category_id) WHERE i2c2.information_id = i.information_id AND ic2.top = '1')";
		}
		
		if ($filter == 'menu') {
			$sql .= " AND i.menu = 1 AND NOT EXISTS (SELECT i2c2.information_category_id FROM " . DB_PREFIX . "information_to_category i2c2 LEFT JOIN " . DB_PREFIX . "information_category ic2 ON (ic2.information_category_id = i2c2.information_category_id) WHERE i2c2.information_id = i.information_id AND ic2.menu = '1')";
		}
		
		if ($filter == 'bottom') {
			$sql .= " AND i.bottom = 1 AND NOT EXISTS (SELECT i2c2.information_category_id FROM " . DB_PREFIX . "information_to_category i2c2 LEFT JOIN " . DB_PREFIX . "information_category ic2 ON (ic2.information_category_id = i2c2.information_category_id) WHERE i2c2.information_id = i.information_id AND ic2.bottom = '1')";
		}
		
		if ($filter == 'extra') {
			$sql .= " AND i.extra = 1";
		}
		
		if ($filter == 'service') {
			$sql .= " AND i.service = 1";
		}
		
		if ($filter == 'account') {
			$sql .= " AND i.account = 1";
		} 
		
		$sql .= " ORDER BY i.sort_order, LCASE(id.title) ASC";
		
		$query = $this->db->query($sql);
		
		return $query->rows;
	}
	
	public function getInformationsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) LEFT JOIN " . DB_PREFIX . "information_to_category i2c ON (i.information_id = i2c.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i2c.information_category_id = '" . (int)$category_id . "' AND i.status = '1' ORDER BY i.sort_order, LCASE(id.title) ASC");

		return $query->rows;
	}
	
	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "information_category c LEFT JOIN " . DB_PREFIX . "information_category_description cd ON (c.information_category_id = cd.information_category_id) LEFT JOIN " . DB_PREFIX . "information_category_to_store c2s ON (c.information_category_id = c2s.information_category_id) WHERE c.information_category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

		return $query->row;
	}

	public function getCategories($parent_id = 0) {
		$sql = "SELECT * FROM " . DB_PREFIX . "information_category c LEFT JOIN " . DB_PREFIX . "information_category_description cd ON (c.information_category_id = cd.information_category_id) LEFT JOIN " . DB_PREFIX . "information_category_to_store c2s ON (c.information_category_id = c2s.information_category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$query = $this->db->query($sql);
		
		return $query->rows;
	}

	public function getInformationCategoryLayoutId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_category_to_layout WHERE information_category_id = '" . (int)$category_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getTotalCategoriesByCategoryId($parent_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_category c LEFT JOIN " . DB_PREFIX . "information_category_to_store c2s ON (c.information_category_id = c2s.information_category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

		return $query->row['total'];
	}

	public function getUncategorizedInformations() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND NOT EXISTS (SELECT i2c.information_id FROM " . DB_PREFIX . "information_to_category i2c WHERE i2c.information_id = i.information_id) AND i.status = '1' ORDER BY i.sort_order, LCASE(id.title) ASC");

		return $query->rows;
	}
}