<?php
class ModelCatalogPublisher extends Model {
	public function addpublisher($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "publisher SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$publisher_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "publisher SET image = '" . $this->db->escape($data['image']) . "' WHERE publisher_id = '" . (int)$publisher_id . "'");
		}

		if (isset($data['publisher_store'])) {
			foreach ($data['publisher_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "publisher_to_store SET publisher_id = '" . (int)$publisher_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
				
		// SEO URL
		if (isset($data['publisher_seo_url'])) {
			foreach ($data['publisher_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'publisher_id=" . (int)$publisher_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		$this->cache->delete('publisher');

		return $publisher_id;
	}

	public function editpublisher($publisher_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "publisher SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE publisher_id = '" . (int)$publisher_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "publisher SET image = '" . $this->db->escape($data['image']) . "' WHERE publisher_id = '" . (int)$publisher_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "publisher_to_store WHERE publisher_id = '" . (int)$publisher_id . "'");

		if (isset($data['publisher_store'])) {
			foreach ($data['publisher_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "publisher_to_store SET publisher_id = '" . (int)$publisher_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'publisher_id=" . (int)$publisher_id . "'");

		if (isset($data['publisher_seo_url'])) {
			foreach ($data['publisher_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'publisher_id=" . (int)$publisher_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('publisher');
	}

	public function deletepublisher($publisher_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "publisher` WHERE publisher_id = '" . (int)$publisher_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "publisher_to_store` WHERE publisher_id = '" . (int)$publisher_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'publisher_id=" . (int)$publisher_id . "'");

		$this->cache->delete('publisher');
	}

	public function getpublisher($publisher_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "publisher WHERE publisher_id = '" . (int)$publisher_id . "'");

		return $query->row;
	}

	public function getpublishers($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "publisher";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
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
	}

	public function getpublisherStores($publisher_id) {
		$publisher_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "publisher_to_store WHERE publisher_id = '" . (int)$publisher_id . "'");

		foreach ($query->rows as $result) {
			$publisher_store_data[] = $result['store_id'];
		}

		return $publisher_store_data;
	}
	
	public function getpublisherSeoUrls($publisher_id) {
		$publisher_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'publisher_id=" . (int)$publisher_id . "'");

		foreach ($query->rows as $result) {
			$publisher_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $publisher_seo_url_data;
	}
	
	public function getTotalpublishers() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "publisher");

		return $query->row['total'];
	}
}
