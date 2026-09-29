<?php
class ModelExtensionCatalogInformationCategory extends Model {
	public function addCategory($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "information_category SET parent_id = '" . (int)$data['parent_id'] . "', information_id = '" . (int)$data['information_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `fa` = '" . $this->db->escape($data['fa']) . "', `menu` = '" . (isset($data['menu']) ? (int)$data['menu'] : 0) . "', `bottom` = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

		$category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "information_category SET image = '" . $this->db->escape($data['image']) . "' WHERE information_category_id = '" . (int)$category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_category_description SET information_category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_category_path` WHERE information_category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "information_category_path` SET `information_category_id` = '" . (int)$category_id . "', `cat_id` = '" . (int)$result['cat_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "information_category_path` SET `information_category_id` = '" . (int)$category_id . "', `cat_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_category_to_store SET information_category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		// Set which layout to use with this category
		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_category_to_layout SET information_category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		//Information Category SEO
		if (isset($data['information_category_seo_url'])) {
			foreach ($data['information_category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'information_category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('category');

		return $category_id;
	}

	public function editCategory($category_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "information_category SET parent_id = '" . (int)$data['parent_id'] . "', information_id = '" . (int)$data['information_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `fa` = '" . $this->db->escape($data['fa']) . "', `menu` = '" . (isset($data['menu']) ? (int)$data['menu'] : 0) . "', `bottom` = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE information_category_id = '" . (int)$category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "information_category SET image = '" . $this->db->escape($data['image']) . "' WHERE information_category_id = '" . (int)$category_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "information_category_description WHERE information_category_id = '" . (int)$category_id . "'");

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_category_description SET information_category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_category_path` WHERE cat_id = '" . (int)$category_id . "' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "information_category_path` WHERE information_category_id = '" . (int)$category_path['information_category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

				$path = array();

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_category_path` WHERE information_category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['cat_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_category_path` WHERE information_category_id = '" . (int)$category_path['information_category_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['cat_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $cat_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "information_category_path` SET information_category_id = '" . (int)$category_path['information_category_id'] . "', `cat_id` = '" . (int)$cat_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "information_category_path` WHERE information_category_id = '" . (int)$category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_category_path` WHERE information_category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "information_category_path` SET information_category_id = '" . (int)$category_id . "', `cat_id` = '" . (int)$result['cat_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "information_category_path` SET information_category_id = '" . (int)$category_id . "', `cat_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "information_category_to_store WHERE information_category_id = '" . (int)$category_id . "'");

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_category_to_store SET information_category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'information_category_id=" . (int)$category_id . "'");

		if (isset($data['information_category_seo_url'])) {
			foreach ($data['information_category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'information_category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		// Set which layout to use with this category
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_category_to_layout WHERE information_category_id = '" . (int)$category_id . "'");

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_category_to_layout SET information_category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('category');
	}

	public function deleteCategory($category_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_category_path WHERE information_category_id = '" . (int)$category_id . "'");

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_category_path WHERE cat_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['information_category_id']);
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "information_category WHERE information_category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_category_description WHERE information_category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_category_path WHERE information_category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_category_to_store WHERE information_category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_category_to_layout WHERE information_category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_to_category WHERE information_category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'category_id=" . (int)$category_id . "'");

		$this->cache->delete('category');
	}

	public function repairCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_category WHERE parent_id = '" . (int)$parent_id . "'");

		foreach ($query->rows as $category) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "information_category_path` WHERE information_category_id = '" . (int)$category['information_category_id'] . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_category_path` WHERE information_category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "information_category_path` SET information_category_id = '" . (int)$category['information_category_id'] . "', `cat_id` = '" . (int)$result['cat_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "information_category_path` SET information_category_id = '" . (int)$category['information_category_id'] . "', `cat_id` = '" . (int)$category['information_category_id'] . "', level = '" . (int)$level . "'");

			$this->repairCategories($category['information_category_id']);
		}
	}

	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "information_category_path cp LEFT JOIN " . DB_PREFIX . "information_category_description cd1 ON (cp.cat_id = cd1.information_category_id AND cp.information_category_id != cp.cat_id) WHERE cp.information_category_id = c.information_category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.information_category_id) AS path, (SELECT id.title FROM " . DB_PREFIX . "information_description id WHERE id.information_id = c.information_id AND id.language_id = '" . (int)$this->config->get('config_language_id') . "') AS information FROM " . DB_PREFIX . "information_category c LEFT JOIN " . DB_PREFIX . "information_category_description cd2 ON (c.information_category_id = cd2.information_category_id) WHERE c.information_category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getCategories($data = array()) {
		$sql = "SELECT cp.information_category_id AS information_category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "information_category_path cp LEFT JOIN " . DB_PREFIX . "information_category c1 ON (cp.information_category_id = c1.information_category_id) LEFT JOIN " . DB_PREFIX . "information_category c2 ON (cp.cat_id = c2.information_category_id) LEFT JOIN " . DB_PREFIX . "information_category_description cd1 ON (cp.cat_id = cd1.information_category_id) LEFT JOIN " . DB_PREFIX . "information_category_description cd2 ON (cp.information_category_id = cd2.information_category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY cp.information_category_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
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

	public function getCategoryDescriptions($category_id) {
		$category_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_category_description WHERE information_category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			);
		}

		return $category_description_data;
	}

	public function getCategoryStores($category_id) {
		$category_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_category_to_store WHERE information_category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_store_data[] = $result['store_id'];
		}

		return $category_store_data;
	}

	public function getCategorySeoUrls($category_id) {
		$category_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'information_category_id=" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $category_seo_url_data;
	}

	public function getCategoryLayouts($category_id) {
		$category_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_category_to_layout WHERE information_category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $category_layout_data;
	}

	public function getTotalCategories() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_category");

		return $query->row['total'];
	}
	
	public function getTotalCategoriesByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_category_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}	
	
	public function getInformationsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id O (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_category i2c ON (i.information_id = i2c.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2c.information_category_id = '" . (int)$category_id . "' ORDER BY id.title");

		return $query->rows;
	}
	
	public function getInformationCategories($information_id) {
		$information_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_category WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_category_data[] = $result['information_category_id'];
		}

		return $information_category_data;
	}

	public function createTables() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "information_category`( information_category_id INT(11) NOT NULL AUTO_INCREMENT, parent_id INT(11), information_id INT(11), image VARCHAR(255), `top` TINYINT(1), `menu` TINYINT(1), `fa` VARCHAR(32), `bottom` TINYINT(1), `column` INT(3), sort_order INT(3), status TINYINT(1), date_added DATE, date_modified DATE, PRIMARY KEY ( information_category_id ))");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "information_category_description`( information_category_id INT(11), language_id INT(11), name VARCHAR(255), description TEXT, meta_title VARCHAR(255), meta_description VARCHAR(255), meta_keyword VARCHAR(255), PRIMARY KEY (information_category_id, language_id))");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "information_category_path`( information_category_id INT(11), cat_id INT(11), `level` INT(11), PRIMARY KEY (information_category_id, cat_id))");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "information_category_to_layout`( information_category_id INT(11), store_id INT(11), layout_id INT(11), PRIMARY KEY ( information_category_id, store_id ))");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "information_category_to_store`( information_category_id INT(11), store_id INT(11), PRIMARY KEY ( information_category_id, store_id ))");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "information_to_category`( information_id INT(11), information_category_id INT(11), PRIMARY KEY ( information_id, information_category_id ))");

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "information` LIKE 'image'");

		if ($query->num_rows < 1) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "information` ADD COLUMN `image` VARCHAR(255) AFTER information_id, ADD COLUMN `top` TINYINT(1) AFTER `image`, ADD COLUMN `menu` TINYINT(1) AFTER `bottom`, ADD COLUMN `fa` VARCHAR(32) AFTER `menu`, ADD COLUMN `extra` TINYINT(1) AFTER `menu`, ADD COLUMN `service` TINYINT(1) AFTER `extra`, ADD COLUMN `account` TINYINT(1) AFTER `service`");
		}
	}
}