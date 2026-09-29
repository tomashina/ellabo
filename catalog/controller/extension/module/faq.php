<?php  
/* 
Version: 1.0
Author: Artur Sułkowski
Website: http://artursulkowski.pl
*/

use Agmedia\Models\Page\Page;

class ControllerExtensionModuleFaq extends Controller {
	public function index() {
		$lang_id = $this->config->get('config_language_id');
		$setting = $this->config->get('faq_module');

		$this->load->language('information/page');
        
        $this->load->model('catalog/page');
        
        if (file_exists(DIR_TEMPLATE . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/css/faq.css')) {
            $this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/css/faq.css');
        }
        
        $pages = Page::with('description', 'documents')->orderBy('page_group')->get()->toArray();
        $data['groups'] = [];
        $data['anchors'] = [];
        
        foreach ($pages as $page) {
            $page['files'] = $this->model_catalog_page->getPagesDocs($page['page_id']);
            $data['groups'][$page['page_group']][] = $page;
            $data['anchors'][utf8_substr(utf8_strtoupper($page['page_group']), 0, 1)] = $page['page_group'];
        }
        
        foreach ($pages as $page) {
            $data['anchors'][utf8_substr(utf8_strtoupper($page['page_group']), 0, 1)] = $page['page_group'];
            break;
        }
        
        $this->log->write($data['anchors']);

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => "ATESTI",
			'href' => $this->url->link('extension/module/faq', '', true)
		);
      
        $data['heading_title'] = 'ATESTI';
        $this->document->setTitle('ATESTI');
        
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		        
		$this->response->setOutput($this->load->view('extension/module/faq', $data));
	}
    
    
        
    function sortData(&$data, $col)
    {
        usort($data, function($a, $b) use ($col){
            if ($a[$col] == $b[$col]) {
                return 0;
            }
            return ($a[$col] < $b[$col]) ? -1 : 1;
        });
    }
}
?>