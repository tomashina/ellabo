<?php

/*
Breadcrumbs+ v2.1

The Breadcrumbs+ extension is for CMS Opencart 3.x.
The module allows to manage product breadcrumbs.

<https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=35022>
<https://underr.space/notes/projects/project-008.html>
<https://github.com/underr-ua/ocmod3-breadcrumbs-plus>

This file is subject to the End Use License Agreement (EULA) <https://raw.githubusercontent.com/underr-ua/ocmod3-breadcrumbs-plus/master/EULA.txt>

Copyright (c) 2019 Andrii Burkatskyi aka underr
*/

class ModelExtensionModuleBreadcrumbs extends Model {
	public function getProductCategories($product_id = 0) {
		// Returns array with product categories ids and its parents
		$query = $this->db->query(
			'SELECT c.category_id, c.parent_id FROM ' . DB_PREFIX . 'product_to_category p2c LEFT JOIN ' .
			DB_PREFIX . 'category c ON (p2c.category_id = c.category_id) WHERE product_id = "' .
			(int)$product_id . '"'
		);

		return $query->rows;
	}

	public function getCategoryParent($category_id) {
		// Returns id of category parent
		$query = $this->db->query(
			'SELECT category_id, parent_id FROM ' . DB_PREFIX . 'category WHERE category_id = "' . $category_id . '"'
		);

		return $query->row['parent_id'];
	}

	public function getProductPath($product_id = 0, $mode = '', $max_level = 0) {
		// Returns a string with product categories path (9_11_6 etc.)
		// S - shortest path;
		// L - longest path;
		// T - last category in longest path;

		$path = '';

		if ($product_id && in_array($mode, array('S', 'L', 'T'))) {
			$categories = $this->getProductCategories($product_id);

			$path_ids = array();

			foreach ($categories as $key => $category) {
				$path_ids[$key][] = $category['category_id'];

				while ($category['parent_id']) {
					array_unshift($path_ids[$key], $category['parent_id']);

					$parent_id = $this->getCategoryParent($category['parent_id']);

					if ($parent_id == $category['parent_id']) {
						$this->log->write('[Breadcrumb+]: Recursive Category - id(' . $category['parent_id'] . ')');

						continue 2;
					}

					$category['parent_id'] = $parent_id;
				}
			}

			asort($path_ids);

			if ('T' == $mode) {
				// Last category
				$path_ids = end($path_ids);
			} elseif ('S' == $mode) {
				// Shortest path
				$path_ids = reset($path_ids);
			} elseif ('L' == $mode) {
				// Longest path
				$path_ids = end($path_ids);
			}

			if ($path_ids) {
				if ('S' == $mode || 'L' == $mode) {
					$l = 0;

					foreach ($path_ids as $id) {
						if (0 == $id) {
							break;
						}

						$l++;

						if ($max_level && $l > $max_level) {
							break;
						}

						if (!$path) {
							$path = $id;
						} else {
							$path .= '_' . $id;
						}
					}
				} elseif ('T' == $mode) {
					$path = end($path_ids);
				}
			}
		}

		return $path;
	}
}
