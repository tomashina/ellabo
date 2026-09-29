<?php
class ControllerCatalogPage extends Controller {
	private $error = array();

	//quicksave
	public function qsave() {
		$this->language->load('catalog/page');

		$this->load->model('catalog/page');

		$json = array();

		if ($this->validateForm()) {
			$this->model_catalog_page->editPage($this->request->get['information_id'], $this->request->post);
			$json['success'] = ($this->language->get('text_success')).' --- '.(date("Y-m-d - H:i:s"));
		} else {
			$json['error'] = $this->error;
		}

		$this->response->addHeader('Content-Type: application/json; charset=utf-8');
		$this->response->setOutput(json_encode($json));
	}
//quicksave end

	public function index() {
		$this->load->language('catalog/page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/page');

		$this->getList();
	}

	public function add() {
	    
	    $this->load->language('catalog/page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/page');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_page->addPage($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/page', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/page');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_page->editPage($this->request->get['page_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/page', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/page');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $page_id) {
				$this->model_catalog_page->deletePage($page_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/page', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'id.title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/page', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/page/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/page/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['pages'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$page_total = $this->model_catalog_page->getTotalPages();

		$results = $this->model_catalog_page->getPages($filter_data);

		foreach ($results as $result) {
			$data['pages'][] = array(
				'page_id' => $result['page_id'],
				'title'          => $result['title'],
				'sort_order'     => $result['sort_order'],
				'edit'           => $this->url->link('catalog/page/edit', 'user_token=' . $this->session->data['user_token'] . '&page_id=' . $result['page_id'] . $url, true)
			);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_title'] = $this->url->link('catalog/page', 'user_token=' . $this->session->data['user_token'] . '&sort=id.title' . $url, true);
		$data['sort_sort_order'] = $this->url->link('catalog/page', 'user_token=' . $this->session->data['user_token'] . '&sort=i.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $page_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/page', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($page_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($page_total - $this->config->get('config_limit_admin'))) ? $page_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $page_total, ceil($page_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/page_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['page_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/page', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['page_id'])) {
			$data['action'] = $this->url->link('catalog/page/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/page/edit', 'user_token=' . $this->session->data['user_token'] . '&page_id=' . $this->request->get['page_id'] . $url, true);
		}

		//quicksave
	$data['pidqs'] = isset($this->request->get['page_id']) ? $this->request->get['page_id'] : '';
//quicksave end

		$data['cancel'] = $this->url->link('catalog/page', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['page_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$page_info = $this->model_catalog_page->getPage($this->request->get['page_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
        
        if (isset($this->request->post['page_description'])) {
            $data['page_description'] = $this->request->post['page_description'];
        } elseif (isset($this->request->get['page_id'])) {
            $data['page_description'] = $this->model_catalog_page->getPageDescriptions($this->request->get['page_id']);
        } else {
            $data['page_description'] = array();
        }
        
        if (isset($this->request->post['page_docs'])) {
            $data['page_docs'] = $this->request->post['docs'];
        } elseif (isset($this->request->get['page_id'])) {
            $data['page_docs'] = $this->model_catalog_page->getPageDocs($this->request->get['page_id']);
            $data['page_id'] = $this->request->get['page_id'];
        } else {
            $data['page_docs'] = array();
        }
        
        if (isset($this->request->get['page_id'])) {
            $data['page_group'] = $this->model_catalog_page->getPageGroup($this->request->get['page_id']);
        } else {
            $data['page_group'] = null;
        }
        
        $this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
        
        $stores = $this->model_setting_store->getStores();
        
        foreach ($stores as $store) {
            $data['stores'][] = array(
                'store_id' => $store['store_id'],
                'name'     => $store['name']
            );
        }
        
        // fj.agmedia.hr
        $data['groups'] = json_encode($this->model_catalog_page->getPageGroups());

		if (isset($this->request->post['page_store'])) {
			$data['page_store'] = $this->request->post['page_store'];
		} elseif (isset($this->request->get['page_id'])) {
			$data['page_store'] = $this->model_catalog_page->getPageStores($this->request->get['page_id']);
		} else {
			$data['page_store'] = array(0);
		}

		if (isset($this->request->post['bottom'])) {
			$data['bottom'] = $this->request->post['bottom'];
		} elseif (!empty($page_info)) {
			$data['bottom'] = $page_info['bottom'];
		} else {
			$data['bottom'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($page_info)) {
			$data['status'] = $page_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($page_info)) {
			$data['sort_order'] = $page_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		if (isset($this->request->post['page_seo_url'])) {
			$data['page_seo_url'] = $this->request->post['page_seo_url'];
		} elseif (isset($this->request->get['page_id'])) {
			$data['page_seo_url'] = $this->model_catalog_page->getPageSeoUrls($this->request->get['page_id']);
		} else {
			$data['page_seo_url'] = array();
		}
		
		if (isset($this->request->post['page_layout'])) {
			$data['page_layout'] = $this->request->post['page_layout'];
		} elseif (isset($this->request->get['page_id'])) {
			$data['page_layout'] = $this->model_catalog_page->getPageLayouts($this->request->get['page_id']);
		} else {
			$data['page_layout'] = array();
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/page_form', $data));
	}
    
    public function upload() {
        $this->load->language('catalog/download');
        
        $json = array();
        
        // Check user has permission
        if (!$this->user->hasPermission('modify', 'catalog/download')) {
            $json['error'] = $this->language->get('error_permission');
        }
        
        if (!$json) {
            if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
                // Sanitize the filename
                $filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));
                
                // Validate the filename length
                if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 256)) {
                    $json['error'] = $this->language->get('error_filename');
                }
                
                // Allowed file extension types
                $allowed = array();
                
                $extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
                
                $filetypes = explode("\n", $extension_allowed);
                
                foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
                }
                
                if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
                    $json['error'] = $this->language->get('error_filetype');
                }
                
                // Allowed file mime types
                $allowed = array();
                
                $mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));
                
                $filetypes = explode("\n", $mime_allowed);
                
                foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
                }
                
                if (!in_array($this->request->files['file']['type'], $allowed)) {
                    $json['error'] = $this->language->get('error_filetype');
                }
                
                // Check to see if any PHP files are trying to be uploaded
                $content = file_get_contents($this->request->files['file']['tmp_name']);
                
                if (preg_match('/\<\?php/i', $content)) {
                    $json['error'] = $this->language->get('error_filetype');
                }
                
                // Return any upload error
                if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                    $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
                }
            } else {
                $json['error'] = $this->language->get('error_upload');
            }
        }
        
        if (!$json) {
            $file = $filename . '.' . token(32);
            
            move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file);
            
            $json['filename'] = $file;
            $json['mask'] = $filename;
            
            $json['success'] = $this->language->get('text_upload');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function removeUpload() {
        $this->load->language('catalog/download');
        $this->load->model('catalog/page');
        
        $json = array();
        
        // Check user has permission
        if (!$this->user->hasPermission('modify', 'catalog/download')) {
            $json['error'] = $this->language->get('error_permission');
        }
        
        if (!$json) {
            if ( ! empty($this->request->get['page_id'])) {
                $this->model_catalog_page->removePageDoc($this->request->get['page_id'], $this->request->get['filename']);
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => 200]));
    }

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/page')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['page_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 1) || (utf8_strlen($value['title']) > 64)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}

			if (utf8_strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}

			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}

		if ($this->request->post['page_seo_url']) {
			$this->load->model('design/seo_url');
			
			foreach ($this->request->post['page_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}						
						
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
						
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['page_id']) || ($seo_url['query'] != 'page_id=' . $this->request->get['page_id']))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
							}
						}
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/page')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/store');

		foreach ($this->request->post['selected'] as $page_id) {
			/*if ($this->config->get('config_account_id') == $page_id) {
				$this->error['warning'] = $this->language->get('error_account');
			}

			if ($this->config->get('config_checkout_id') == $page_id) {
				$this->error['warning'] = $this->language->get('error_checkout');
			}

			if ($this->config->get('config_affiliate_id') == $page_id) {
				$this->error['warning'] = $this->language->get('error_affiliate');
			}

			if ($this->config->get('config_return_id') == $page_id) {
				$this->error['warning'] = $this->language->get('error_return');
			}*/

			/*$store_total = $this->model_setting_store->getTotalPagesByPageId($page_id);

			if ($store_total) {
				$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
			}*/
		}

		return !$this->error;
	}
}