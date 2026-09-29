<?php

class ControllerInformationPage extends Controller
{
    
    public function index()
    {
        $this->load->language('information/page');
        
        $this->load->model('catalog/page');
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );
        
        if (isset($this->request->get['page_id'])) {
            $page_id = (int)$this->request->get['page_id'];
        } else {
            $page_id = 0;
        }
        
        $page_info = $this->model_catalog_page->getPage($page_id);
        
        if ($page_info) {
            $this->document->setTitle($page_info['meta_title']);
            $this->document->setDescription($page_info['meta_description']);
            $this->document->setKeywords($page_info['meta_keyword']);
            
            $data['breadcrumbs'][] = array(
                'text' => $page_info['title'],
                'href' => $this->url->link('information/page', 'page_id=' . $page_id)
            );
            
            $data['heading_title'] = $page_info['title'];
            
            $data['description'] = html_entity_decode($page_info['description'], ENT_QUOTES, 'UTF-8');
            
            $data['docs'] = $this->model_catalog_page->getPagesDocs($page_id);
            
          //  \Agmedia\Log\Log::write($data['docs'], 'docs');
            
            $data['continue'] = $this->url->link('common/home');
            
            $data['column_left']    = $this->load->controller('common/column_left');
            $data['column_right']   = $this->load->controller('common/column_right');
            $data['content_top']    = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer']         = $this->load->controller('common/footer');
            $data['header']         = $this->load->controller('common/header');
            
            $this->response->setOutput($this->load->view('information/page', $data));
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_error'),
                'href' => $this->url->link('information/page', 'page_id=' . $page_id)
            );
            
            $this->document->setTitle($this->language->get('text_error'));
            
            $data['heading_title'] = $this->language->get('text_error');
            
            $data['text_error'] = $this->language->get('text_error');
            
            $data['continue'] = $this->url->link('common/home');
            
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
            
            $data['column_left']    = $this->load->controller('common/column_left');
            $data['column_right']   = $this->load->controller('common/column_right');
            $data['content_top']    = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer']         = $this->load->controller('common/footer');
            $data['header']         = $this->load->controller('common/header');
            
            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }
    
    
    public function agree()
    {
        $this->load->model('catalog/page');
        
        if (isset($this->request->get['page_id'])) {
            $page_id = (int)$this->request->get['page_id'];
        } else {
            $page_id = 0;
        }
        
        $output = '';
        
        $page_info = $this->model_catalog_page->getPage($page_id);
        
        if ($page_info) {
            $output .= html_entity_decode($page_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
        }
        
        $this->response->setOutput($output);
    }
    
    
    public function download()
    {
        /*if ( ! $this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('information/page', '', true);
            
            $this->response->redirect($this->url->link('account/login', '', true));
        }*/
        
        $this->load->model('catalog/page');
        
        if (isset($this->request->get['page_id'])) {
            $row = $this->model_catalog_page->getDoc($this->request->get['page_id'], $this->request->get['filename']);
            
            if ($row) {
                $file = DIR_DOWNLOAD . $row['filename'];
                $mask = basename($row['mask']);
    
                if ( ! headers_sent()) {
                    if (file_exists($file)) {
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($file));
            
                        if (ob_get_level()) {
                            ob_end_clean();
                        }
            
                        readfile($file, 'rb');
            
                        exit();
                    } else {
                        exit('Error: Could not find file ' . $file . '!');
                    }
                } else {
                    exit('Error: Headers already sent out!');
                }
            } else {
                $this->response->redirect($this->url->link('information/page', '', true));
            }
        } else {
            $this->response->redirect($this->url->link('information/page', '', true));
        }
    }
}