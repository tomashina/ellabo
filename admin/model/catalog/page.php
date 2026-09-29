<?php

class ModelCatalogPage extends Model
{
    
    public function addPage($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "page SET sort_order = '" . (int)$data['sort_order'] . "', page_group = '" . (isset($data['page_group']) ? $this->db->escape($data['page_group']) : null) . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "'");
        
        $page_id = $this->db->getLastId();
        
        foreach ($data['page_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "page_description SET page_id = '" . (int)$page_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }
        
        // AG docs
        if (isset($data['docs'])) {
            foreach ($data['docs'] as $language_id => $docs) {
                foreach ($docs as $file) {
                    $arr = array_keys($file);
                    $this->db->query("INSERT INTO " . DB_PREFIX . "page_docs SET page_id = '" . (int)$page_id . "', language_id = '" . (int)$language_id . "', filename = '" . $this->db->escape($arr[0]) . "', mask = '" . $this->db->escape($file[$arr[0]]) . "'");
                }
            }
        }
        
        if (isset($data['page_store'])) {
            foreach ($data['page_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "page_to_store SET page_id = '" . (int)$page_id . "', store_id = '" . (int)$store_id . "'");
            }
        }
        
        // SEO URL
        if (isset($data['page_seo_url'])) {
            foreach ($data['page_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if ( ! empty($keyword)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'page_id=" . (int)$page_id . "', keyword = '" . $this->db->escape($keyword) . "'");
                    }
                }
            }
        }
        
        if (isset($data['page_layout'])) {
            foreach ($data['page_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "page_to_layout SET page_id = '" . (int)$page_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
            }
        }
        
        $this->cache->delete('page');
        
        return $page_id;
    }
    
    
    public function editPage($page_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "page SET sort_order = '" . (int)$data['sort_order'] . "', page_group = '" . (isset($data['page_group']) ? $this->db->escape($data['page_group']) : null) . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "' WHERE page_id = '" . (int)$page_id . "'");
        
        $this->db->query("DELETE FROM " . DB_PREFIX . "page_description WHERE page_id = '" . (int)$page_id . "'");
        
        foreach ($data['page_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "page_description SET page_id = '" . (int)$page_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }
        
        // AG docs
        if (isset($data['docs'])) {
            foreach ($data['docs'] as $language_id => $docs) {
                foreach ($docs as $file) {
                    $arr = array_keys($file);
                    $this->db->query("INSERT INTO " . DB_PREFIX . "page_docs SET page_id = '" . (int)$page_id . "', language_id = '" . (int)$language_id . "', filename = '" . $this->db->escape($arr[0]) . "', mask = '" . $this->db->escape($file[$arr[0]]) . "'");
                }
            }
        }
        
        $this->db->query("DELETE FROM " . DB_PREFIX . "page_to_store WHERE page_id = '" . (int)$page_id . "'");
        
        if (isset($data['page_store'])) {
            foreach ($data['page_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "page_to_store SET page_id = '" . (int)$page_id . "', store_id = '" . (int)$store_id . "'");
            }
        }
        
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'page_id=" . (int)$page_id . "'");
        
        if (isset($data['page_seo_url'])) {
            foreach ($data['page_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (trim($keyword)) {
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'page_id=" . (int)$page_id . "', keyword = '" . $this->db->escape($keyword) . "'");
                    }
                }
            }
        }
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "page_to_layout` WHERE page_id = '" . (int)$page_id . "'");
        
        if (isset($data['page_layout'])) {
            foreach ($data['page_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "page_to_layout` SET page_id = '" . (int)$page_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
            }
        }
        
        $this->cache->delete('page');
    }
    
    
    public function deletePage($page_id)
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "page` WHERE page_id = '" . (int)$page_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "page_description` WHERE page_id = '" . (int)$page_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "page_docs` WHERE page_id = '" . (int)$page_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "page_to_store` WHERE page_id = '" . (int)$page_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "page_to_layout` WHERE page_id = '" . (int)$page_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'page_id=" . (int)$page_id . "'");
        
        $this->cache->delete('page');
    }
    
    
    public function getPage($page_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "page WHERE page_id = '" . (int)$page_id . "'");
        
        return $query->row;
    }
    
    
    public function getPages($data = array())
    {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "page i LEFT JOIN " . DB_PREFIX . "page_description id ON (i.page_id = id.page_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";
            
            $sort_data = array(
                'id.title',
                'i.sort_order'
            );
            
            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            } else {
                $sql .= " ORDER BY id.title";
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
            $page_data = $this->cache->get('page.' . (int)$this->config->get('config_language_id'));
            
            if ( ! $page_data) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page i LEFT JOIN " . DB_PREFIX . "page_description id ON (i.page_id = id.page_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.title");
                
                $page_data = $query->rows;
                
                $this->cache->set('page.' . (int)$this->config->get('config_language_id'), $page_data);
            }
            
            return $page_data;
        }
    }
    
    
    public function getPageDescriptions($page_id)
    {
        $page_description_data = array();
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_description WHERE page_id = '" . (int)$page_id . "'");
        
        foreach ($query->rows as $result) {
            $page_description_data[$result['language_id']] = array(
                'title'            => $result['title'],
                'description'      => $result['description'],
                'meta_title'       => $result['meta_title'],
                'meta_description' => $result['meta_description'],
                'meta_keyword'     => $result['meta_keyword']
            );
        }
        
        return $page_description_data;
    }
    
    
    public function getPageDocs($page_id)
    {
        $page_docs_data = array();
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_docs WHERE page_id = '" . (int)$page_id . "'");
        
        foreach ($query->rows as $result) {
            $page_docs_data[$result['language_id']][] = array(
                'name'     => $result['mask'],
                'filename' => $result['filename']
            );
        }
        
        return $page_docs_data;
    }
    
    
    public function getPageStores($page_id)
    {
        $page_store_data = array();
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_to_store WHERE page_id = '" . (int)$page_id . "'");
        
        foreach ($query->rows as $result) {
            $page_store_data[] = $result['store_id'];
        }
        
        return $page_store_data;
    }
    
    
    public function getPageSeoUrls($page_id)
    {
        $page_seo_url_data = array();
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'page_id=" . (int)$page_id . "'");
        
        foreach ($query->rows as $result) {
            $page_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
        }
        
        return $page_seo_url_data;
    }
    
    
    public function getPageLayouts($page_id)
    {
        $page_layout_data = array();
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_to_layout WHERE page_id = '" . (int)$page_id . "'");
        
        foreach ($query->rows as $result) {
            $page_layout_data[$result['store_id']] = $result['layout_id'];
        }
        
        return $page_layout_data;
    }
    
    
    public function getTotalPages()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "page");
        
        return $query->row['total'];
    }
    
    
    public function getTotalPagesByLayoutId($layout_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "page_to_layout WHERE layout_id = '" . (int)$layout_id . "'");
        
        return $query->row['total'];
    }
    
    
    public function removePageDoc($page_id, $mask)
    {
        return $this->db->query("DELETE FROM `" . DB_PREFIX . "page_docs` WHERE page_id = '" . (int)$page_id . "' AND mask = '" . $this->db->escape($mask) . "'");
    }
    
    
    public function getPageGroups()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page");
        $response = [];
        
        foreach ($query->rows as $group) {
            $response[$group['page_group']] = $group['page_group'];
        }
        
        return $response;
    }
    
    
    public function getPageGroup($page_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page WHERE page_id = '" . (int)$page_id . "'");
        
        return $query->row['page_group'];
    }
}