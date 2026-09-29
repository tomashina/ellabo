<?php
class ControllErextensionMennyisegiEgyseg extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/mennyisegi_egyseg');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/mennyisegi_egyseg');

        $this->getList();
    }

    public function add() {
        $this->load->language('extension/mennyisegi_egyseg');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/mennyisegi_egyseg');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_mennyisegi_egyseg->addMennyisegiEgyseg($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['megyseg'])) {
                $url .= '&megyseg=' . $this->request->get['megyseg'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/mennyisegi_egyseg', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit() {
        $this->load->language('extension/mennyisegi_egyseg');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/mennyisegi_egyseg');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_mennyisegi_egyseg->editMennyisegiEgyseg($this->request->get['mennyisegi_egyseg_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['megyseg'])) {
                $url .= '&megyseg=' . $this->request->get['megyseg'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/mennyisegi_egyseg', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete() {
        $this->load->language('extension/mennyisegi_egyseg');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/mennyisegi_egyseg');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $mennyisegi_egyseg_id) {
                $this->model_extension_mennyisegi_egyseg->deleteMennyisegiEgyseg($mennyisegi_egyseg_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['megyseg'])) {
                $url .= '&megyseg=' . $this->request->get['megyseg'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/mennyisegi_egyseg', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'name';
        }

        if (isset($this->request->get['megyseg'])) {
            $megyseg = $this->request->get['megyseg'];
        } else {
            $megyseg = 'ASC';
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

        if (isset($this->request->get['megyseg'])) {
            $url .= '&megyseg=' . $this->request->get['megyseg'];
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
            'href' => $this->url->link('extension/mennyisegi_egyseg', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['add'] = $this->url->link('extension/mennyisegi_egyseg/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/mennyisegi_egyseg/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['mennyisegi_egysegek'] = array();

        $filter_data = array(
            'sort'  => $sort,
            'megyseg' => $megyseg,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $results = $this->model_extension_mennyisegi_egyseg->getMennyisegiEgysegek($filter_data);

        $mennyisegi_egyseg_total = $this->model_extension_mennyisegi_egyseg->getTotalMennyisegiEgysegek();


        foreach ($results as $result) {
            $data['mennyisegi_egysegek'][] = array(
                'mennyisegi_egyseg_id' => $result['mennyisegi_egyseg_id'],
                'name'            => $result['name'] . (($result['mennyisegi_egyseg_id'] == $this->config->get('config_mennyisegi_egyseg_id')) ? $this->language->get('text_default') : null),
                'edit'            => $this->url->link('extension/mennyisegi_egyseg/edit', 'user_token=' . $this->session->data['user_token'] . '&mennyisegi_egyseg_id=' . $result['mennyisegi_egyseg_id'] . $url, true)
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

        if ($megyseg == 'ASC') {
            $url .= '&megyseg=DESC';
        } else {
            $url .= '&megyseg=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_name'] = $this->url->link('extension/mennyisegi_egyseg', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['megyseg'])) {
            $url .= '&megyseg=' . $this->request->get['megyseg'];
        }

        $pagination = new Pagination();
        $pagination->total = $mennyisegi_egyseg_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('extension/mennyisegi_egyseg', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($mennyisegi_egyseg_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($mennyisegi_egyseg_total - $this->config->get('config_limit_admin'))) ? $mennyisegi_egyseg_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $mennyisegi_egyseg_total, ceil($mennyisegi_egyseg_total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['megyseg'] = $megyseg;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mennyisegi_egyseg_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['mennyisegi_egyseg_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = array();
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['megyseg'])) {
            $url .= '&megyseg=' . $this->request->get['megyseg'];
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
            'href' => $this->url->link('extension/mennyisegi_egyseg', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        if (!isset($this->request->get['mennyisegi_egyseg_id'])) {
            $data['action'] = $this->url->link('extension/mennyisegi_egyseg/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('extension/mennyisegi_egyseg/edit', 'user_token=' . $this->session->data['user_token'] . '&mennyisegi_egyseg_id=' . $this->request->get['mennyisegi_egyseg_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('extension/mennyisegi_egyseg', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['mennyisegi_egyseg'])) {
            $data['mennyisegi_egyseg'] = $this->request->post['mennyisegi_egyseg'];
        } elseif (isset($this->request->get['mennyisegi_egyseg_id'])) {
            $data['mennyisegi_egyseg'] = $this->model_extension_mennyisegi_egyseg->getMennyisegiEgysegDescriptions($this->request->get['mennyisegi_egyseg_id']);
        } else {
            $data['mennyisegi_egyseg'] = array();
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mennyisegi_egyseg_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'extension/mennyisegi_egyseg')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->request->post['mennyisegi_egyseg'] as $language_id => $value) {
            if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 32)) {
                $this->error['name'][$language_id] = $this->language->get('error_name');
            }
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/mennyisegi_egyseg')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('setting/store');
        $this->load->model('sale/order');

        foreach ($this->request->post['selected'] as $mennyisegi_egyseg_id) {
            if ($this->config->get('module_mennyisegi_egyseg_id') == $mennyisegi_egyseg_id) {
                $this->error['warning'] = $this->language->get('error_default');
            }




            $megyseg_total = $this->model_extension_mennyisegi_egyseg->getTotalByMennyisegiEgysegId($mennyisegi_egyseg_id);

            if ($megyseg_total) {
                $this->error['warning'] = sprintf($this->language->get('error_order'), $megyseg_total);
            }


        }

        return !$this->error;
    }
}
