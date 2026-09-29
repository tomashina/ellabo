<?php

class ControllerCommonDashboard extends Controller
{
    
    public function index()
    {
        
        $this->load->language('extension/module/blog');
        $this->load->language('common/dashboard');
        
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle('view/stylesheet/blog/blog.css');
        
        $data['text_blog_dashboard']      = $this->language->get('text_blog_dashboard');
        $data['text_add_edit_categories'] = $this->language->get('text_add_edit_categories');
        $data['text_add_edit_articles']   = $this->language->get('text_add_edit_articles');
        $data['text_add_edit_authors']    = $this->language->get('text_add_edit_authors');
        $data['text_add_edit_comments']   = $this->language->get('text_add_edit_comments');
        $data['text_general_settings']    = $this->language->get('text_general_settings');
        
        $data['text_modules']                   = $this->language->get('text_modules');
        $data['text_blog_category']             = $this->language->get('text_blog_category');
        $data['text_blog_search']               = $this->language->get('text_blog_search');
        $data['text_blog_latest_post']          = $this->language->get('text_blog_latest_post');
        $data['text_blog_popular_post']         = $this->language->get('text_blog_popular_post');
        $data['text_blog_product_related_post'] = $this->language->get('text_blog_product_related_post');
        $data['text_blog_popular_tags']         = $this->language->get('text_blog_popular_tags');
        
        $data['link_blog_dashboard']      = $this->url->link('extension/module/blog',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_add_edit_categories'] = $this->url->link('extension/module/blog/category_list',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_add_edit_articles']   = $this->url->link('extension/module/blog/article_list',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_add_edit_authors']    = $this->url->link('extension/module/blog/author_list',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_add_edit_comments']   = $this->url->link('extension/module/blog/comment_list',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_general_settings']    = $this->url->link('extension/module/blog/settings',
            'user_token=' . $this->session->data['user_token'], true);
        
        $data['link_blog_category']             = $this->url->link('extension/module/blog_category',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_blog_search']               = $this->url->link('extension/module/blog_search',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_blog_latest_post']          = $this->url->link('extension/module/blog_latest',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_blog_popular_post']         = $this->url->link('extension/module/blog_popular',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_blog_product_related_post'] = $this->url->link('extension/module/blog_related_post',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_blog_popular_tags']         = $this->url->link('extension/module/blog_tags',
            'user_token=' . $this->session->data['user_token'], true);
        
        $data['link_home_slider']   = $this->url->link('extension/module/layerslider',
            'user_token=' . $this->session->data['user_token'] . '&module_id=35', true);
        $data['link_home_banneri']  = $this->url->link('design/banner',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_home_products'] = $this->url->link('extension/module/filter_product',
            'user_token=' . $this->session->data['user_token'], true);
        $data['link_home_megamenu'] = $this->url->link('extension/module/megamenu',
            'user_token=' . $this->session->data['user_token'], true);

         $data['link_home_megafilter'] = $this->url->link('extension/module/mega_filter',
            'user_token=' . $this->session->data['user_token']. '&module_id=38', true);
        
        $data['link_home_atesti'] = $this->url->link('catalog/page', 'user_token=' . $this->session->data['user_token'],
            true);
        
        $data['link_home_info'] = $this->url->link('catalog/information',
            'user_token=' . $this->session->data['user_token'], true);
        
        
        $data['handy_box'] = $this->load->view('extension/module/blog/partial/header2', $data);
        
        $data['user_token'] = $this->session->data['user_token'];
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Check install directory exists
        if (is_dir(DIR_APPLICATION . 'install')) {
            $data['error_install'] = $this->language->get('error_install');
        } else {
            $data['error_install'] = '';
        }
        
        // Dashboard Extensions
        $dashboards = array();
        
        $this->load->model('setting/extension');
        
        
        // Get a list of installed modules
        $extensions = $this->model_setting_extension->getInstalled('dashboard');
        
        // Add all the modules which have multiple settings for each module
        foreach ($extensions as $code) {
            if ($this->config->get('dashboard_' . $code . '_status') && $this->user->hasPermission('access',
                    'extension/dashboard/' . $code)) {
                $output = $this->load->controller('extension/dashboard/' . $code . '/dashboard');
                
                if ($output) {
                    $dashboards[] = array(
                        'code'       => $code,
                        'width'      => $this->config->get('dashboard_' . $code . '_width'),
                        'sort_order' => $this->config->get('dashboard_' . $code . '_sort_order'),
                        'output'     => $output
                    );
                }
            }
        }
        
        $sort_order = array();
        
        foreach ($dashboards as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        
        array_multisort($sort_order, SORT_ASC, $dashboards);
        
        // Split the array so the columns width is not more than 12 on each row.
        $width        = 0;
        $column       = array();
        $data['rows'] = array();
        
        foreach ($dashboards as $dashboard) {
            $column[] = $dashboard;
            
            $width = ($width + $dashboard['width']);
            
            if ($width >= 12) {
                $data['rows'][] = $column;
                
                $width  = 0;
                $column = array();
            }
        }
        
        /*if (DIR_STORAGE == DIR_SYSTEM . 'storage/') {
            $data['security'] = $this->load->controller('common/security');
        } else {
            $data['security'] = '';
        }*/
        
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');
        
        // Run currency update
        if ($this->config->get('config_currency_auto')) {
            $this->load->model('localisation/currency');
            
            $this->model_localisation_currency->refresh();
        }
        
        $this->response->setOutput($this->load->view('common/dashboard', $data));
    }
}