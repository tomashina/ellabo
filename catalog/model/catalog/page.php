<?php

class ModelCatalogPage extends Model
{

    public function getPage($page_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "page i LEFT JOIN " . DB_PREFIX . "page_description id ON (i.page_id = id.page_id) LEFT JOIN " . DB_PREFIX . "page_to_store i2s ON (i.page_id = i2s.page_id) WHERE i.page_id = '" . (int)$page_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

        return $query->row;
    }


    public function getPages()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page i LEFT JOIN " . DB_PREFIX . "page_description id ON (i.page_id = id.page_id) LEFT JOIN " . DB_PREFIX . "page_to_store i2s ON (i.page_id = i2s.page_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' GROUP BY i.page_group ORDER BY i.sort_order, LCASE(id.title) ASC");

        return $query->rows;
    }


    public function getPageLayoutId($page_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_to_layout WHERE page_id = '" . (int)$page_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

        if ($query->num_rows) {
            return (int)$query->row['layout_id'];
        } else {
            return 0;
        }
    }


    public function getPagesDocs($page_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_docs WHERE page_id = '" . (int)$page_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
        
        if ($query->num_rows) {
            $data = array();

            foreach ($query->rows as $result) {
                if (file_exists(DIR_DOWNLOAD . $result['filename'])) {
                    $size = filesize(DIR_DOWNLOAD . $result['filename']);
                    $i = 0;

                    $suffix = array(
                        'B',
                        'KB',
                        'MB',
                        'GB',
                        'TB',
                        'PB',
                        'EB',
                        'ZB',
                        'YB'
                    );

                    while (($size / 1024) > 1) {
                        $size = $size / 1024;
                        $i++;
                    }

                    $data[] = array(
                        'page_id'  => $result['page_id'],
                        'mask'     => $result['mask'],
                        'filename' => $result['filename'],
                        'size'     => round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i],
                        'href'     => $this->url->link('information/page/download', 'filename=' . $result['filename'] . '&page_id=' . $result['page_id'], true)
                    );
                }
            }

            return $data;
        } else {
            return false;
        }
    }


    public function getDoc($page_id, $filename)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_docs WHERE page_id = '" . (int)$page_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' AND filename = '" . $this->db->escape($filename) . "'");

        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }
}
