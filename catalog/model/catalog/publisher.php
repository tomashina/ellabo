<?php
class ModelCatalogPublisher extends Model {
	public function getPublisher($publisher_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "publisher m LEFT JOIN " . DB_PREFIX . "publisher_to_store m2s ON (m.publisher_id = m2s.publisher_id) WHERE m.publisher_id = '" . (int)$publisher_id . "' AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row;
	}

	public function getPublishers($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "publisher m LEFT JOIN " . DB_PREFIX . "publisher_to_store m2s ON (m.publisher_id = m2s.publisher_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

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
		} else {
			$publisher_data = $this->cache->get('publisher.' . (int)$this->config->get('config_store_id'));

			if (!$publisher_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "publisher m LEFT JOIN " . DB_PREFIX . "publisher_to_store m2s ON (m.publisher_id = m2s.publisher_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY name");

				$publisher_data = $query->rows;

				$this->cache->set('publisher.' . (int)$this->config->get('config_store_id'), $publisher_data);
			}

			return $publisher_data;
		}
	}
}