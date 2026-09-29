<?php
/* 
Template Name: Zeexo
Version: 1.0
Author: Artur Sułkowski
Website: http://artursulkowski.pl
*/

class ControllerExtensionModuleZeexo extends Controller {
	
	private $error = array(); 
	
	public function index() {   
	
		//Load the language file for this module
		$this->language->load('extension/module/zeexo');

		//Set the title from the language file $_['heading_title'] string
		$this->document->setTitle('Zeexo Theme Options');
		
		//Load the settings model. You can also add any other models you want to load here.
		$this->load->model('setting/setting');
		
		// Multilanguage
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		// Subtle patterns
		$data['subtle_patterns'] = array();
		if(is_dir("../image/subtle_patterns/")) {
	        $dir = opendir ("../image/subtle_patterns/");
	        while (false !== ($file = readdir($dir))) { 
	            if ($file<>"." && $file<>"..") {
	                if (strpos($file, '.gif',1) || strpos($file, '.jpg',1) || strpos($file, '.png',3) ) {
	                	$data['subtle_patterns'][] = $file;
	                }
	            }
	        }
	    }
		
		/* Konfiguracja kolorów */
		$data['colors_data'] = array(
			array(
				'name' => 'Body',
				'content' => array(
					array(
						'name' => 'Body text',
						'id'   => 'body_font_text'
					),
					array(
						'name' => 'Body links',
						'id'   => 'body_font_links'
					),
					array(
						'name' => 'Body links hover',
						'id'   => 'body_font_links_hover'
					),
					array(
						'name' => 'Background color',
						'id'   => 'body_background_color'
					)
				)
			),
			array(
				'name' => 'Button',
				'content' => array(
					array(
						'name' => 'Button text color',
						'id'   => 'button_text_color'
					),
					array(
						'name' => 'Button background color',
						'id'   => 'button_background_color'
					),
					array(
						'name' => 'Second button text color',
						'id'   => 'second_button_text_color'
					),
					array(
						'name' => 'Second button background color',
						'id'   => 'second_button_background_color'
					)
				)
			),
			array(
				'name' => 'Product',
				'content' => array(
					array(
						'name' => 'Sale label background color',
						'id'   => 'sale_label_background_color'
					),
					array(
						'name' => 'New label background color',
						'id'   => 'new_label_background_color'
					),
					array(
						'name' => 'Add to cart button background color',
						'id'   => 'add_to_cart_button_background_color'
					),
					array(
						'name' => 'Price color',
						'id'   => 'price_color'
					),
					array(
						'name' => 'Old price color',
						'id'   => 'old_price_color'
					),
					array(
						'name' => 'New price color',
						'id'   => 'new_price_color'
					),
					array(
						'name' => 'Countdown text color',
						'id'   => 'countdown_text_color'
					),
					array(
						'name' => 'Hurry up color',
						'id'   => 'hurry_up_color'
					)
				)
			),
			array(
				'name' => 'Input',
				'content' => array(
					array(
						'name' => 'Text color',
						'id'   => 'input_text_color'
					),
					array(
						'name' => 'Background color',
						'id'   => 'input_background_color'
					),
					array(
						'name' => 'Focus background color',
						'id'   => 'input_focus_background_color'
					)
				)
			),
			array(
				'name' => 'Header',
				'content' => array(
					array(
						'name' => 'Background color',
						'id'   => 'header_background_color'
					)
				)
			),
			array(
				'name' => 'Top bar',
				'content' => array(
					array(
						'name' => 'Background color',
						'id'   => 'top_bar_background_color'
					),
					array(
						'name' => 'Border color',
						'id'   => 'top_bar_border_color'
					),
					array(
						'name' => 'Links, text color',
						'id'   => 'top_bar_links_color'
					),
					array(
						'name' => 'Links hover color',
						'id'   => 'top_bar_links_hover_color'
					),
					array(
						'name' => 'Social links color',
						'id'   => 'top_bar_social_links_color'
					),
					array(
						'name' => 'Social links hover color',
						'id'   => 'top_bar_social_links_hover_color'
					)
				)
			),
			array(
				'name' => 'Top center',
				'content' => array(
					array(
						'name' => 'Background color',
						'id'   => 'top_center_background_color'
					),
					array(
						'name' => 'Text, links color',
						'id'   => 'top_center_text_links_color'
					)
				)
			),
			array(
				'name' => 'Top -> Search',
				'content' => array(
					array(
						'name' => 'Text color',
						'id'   => 'search_text_color'
					),
					array(
						'name' => 'Background color',
						'id'   => 'search_background_color'
					),
					array(
						'name' => 'Focus background color',
						'id'   => 'search_focus_background_color'
					),
					array(
						'name' => 'Button text color',
						'id'   => 'search_button_text_color'
					),
					array(
						'name' => 'Category select text color',
						'id'   => 'search_category_select_text_color'
					),
					array(
						'name' => 'Category select background color',
						'id'   => 'search_category_select_background_color'
					)
				)
			),
			array(
				'name' => 'Top -> Svg icon menu',
				'content' => array(
					array(
						'name' => 'Fill color',
						'id'   => 'svg_icon_menu_fill_color'
					),
					array(
						'name' => 'Stroke color',
						'id'   => 'svg_icon_menu_stroke_color'
					)
				)
			),
			array(
				'name' => 'Top -> Svg icon search',
				'content' => array(
					array(
						'name' => 'Fill color',
						'id'   => 'svg_icon_search_fill_color'
					),
					array(
						'name' => 'Stroke color',
						'id'   => 'svg_icon_search_stroke_color'
					)
				)
			),
			array(
				'name' => 'Top -> Svg icon my account',
				'content' => array(
					array(
						'name' => 'Fill color',
						'id'   => 'svg_icon_my_account_fill_color'
					),
					array(
						'name' => 'Stroke color',
						'id'   => 'svg_icon_my_account_stroke_color'
					)
				)
			),
			array(
				'name' => 'Top -> Svg icon wishlist',
				'content' => array(
					array(
						'name' => 'Fill color',
						'id'   => 'svg_icon_wishlist_fill_color'
					),
					array(
						'name' => 'Stroke color',
						'id'   => 'svg_icon_wishlist_stroke_color'
					),
					array(
						'name' => 'Count text color',
						'id'   => 'wishlist_count_text_color'
					),
					array(
						'name' => 'Count background color',
						'id'   => 'wishlist_count_background_color'
					)
				)
			),
			array(
				'name' => 'Top -> Svg icon cart',
				'content' => array(
					array(
						'name' => 'Fill color',
						'id'   => 'svg_icon_cart_fill_color'
					),
					array(
						'name' => 'Stroke color',
						'id'   => 'svg_icon_cart_stroke_color'
					),
					array(
						'name' => 'Count text color',
						'id'   => 'cart_count_text_color'
					),
					array(
						'name' => 'Count background color',
						'id'   => 'cart_count_background_color'
					)
				)
			),
			array(
				'name' => 'Top -> Menu',
				'content' => array(
					array(
						'name' => 'Background color',
						'id'   => 'top_menu_background_color'
					),
					array(
						'name' => 'Link color',
						'id'   => 'top_menu_link_color'
					),
					array(
						'name' => 'Link hover color',
						'id'   => 'top_menu_link_hover_color'
					)
				)
			),
			array(
				'name' => 'Footer',
				'content' => array(
					array(
						'name' => 'Background color',
						'id'   => 'footer_background_color'
					),
					array(
						'name' => 'Heading color',
						'id'   => 'footer_heading_color'
					),
					array(
						'name' => 'Link, text color',
						'id'   => 'footer_link_color'
					),
					array(
						'name' => 'Link hover color',
						'id'   => 'footer_link_hover_color'
					)
				)
			),
			array(
				'name' => 'Footer -> Newsletter',
				'content' => array(
					array(
						'name' => 'Heading text color',
						'id'   => 'footer_newsletter_heading_text_color'
					),
					array(
						'name' => 'Input text color',
						'id'   => 'footer_newsletter_input_text_color'
					),
					array(
						'name' => 'Input background color',
						'id'   => 'footer_newsletter_input_background_color'
					),
					array(
						'name' => 'Button background color',
						'id'   => 'footer_newsletter_button_background_color'
					),
					array(
						'name' => 'Text, link color',
						'id'   => 'footer_newsletter_text_link_color'
					)
				)
			),
			array(
				'name' => 'Copyright',
				'content' => array(
					array(
						'name' => 'Background color',
						'id'   => 'copyright_background_color'
					),
					array(
						'name' => 'Link, text color',
						'id'   => 'copyright_link_color'
					),
					array(
						'name' => 'Link hover color',
						'id'   => 'copyright_link_hover_color'
					)
				)
			)
		);
		
		foreach ($data['colors_data'] as $colors) {
			foreach($colors['content'] as $color) {
				$data[$color['id']] = false;
			}
		}
		
		// Konfiguracja zmiennych
		$config_data = array(
			'main_layout',
			'top_bar_layout',
			'header_layout',
			'slideshow_layout',
			'content_layout',
			'custom_footer_layout',
			'footer_layout',
			'breadcrumb_layout',
			
			'page_width',
			'max_width',
			
			'responsive_design',
			
			'add_to_compare_text',
			'add_to_wishlist_text',
			'checkout_text',
			'continue_shopping_text',
			'confirmation_text',
			'shopping_cart_text',
			'home_text',
			'welcome_text',
			'proceed_to_checkout_text',
			'view_cart_text',
			'continue_shopping_text',
			'there_are_one_items_in_your_cart_text',
			'total_text',
			'qty_text',
			'added_to_cart_text',
			'filter_text',
			'menu_text',
			'more_details_text',
			'quickview_text',
			'sale_text',
			'new_text',
			'all_categories_text',
			
			'header_type',
			'mobile_header_type',
			'fixed_header',
			'quick_search_autosuggest',
			
			'product_per_pow',
			'product_per_pow2',
			'product_scroll_latest',
			'product_scroll_featured',
			'product_scroll_bestsellers',
			'product_scroll_specials',
			'product_scroll_related',
			'quick_view',
			'hover_product',
			'lazy_loading_images',
			'lazy_loading_images_size',
			'display_text_sale',
			'type_sale',
			'new_products_label_limit',
			'display_text_new',
			'product_image_effect',
			'display_add_to_compare',
			'display_add_to_wishlist',
			'display_add_to_cart',
			'display_rating',
			'display_specials_countdown',
			'default_list_grid',
			'grid_type',
			'list_type',
			'refine_search_style',
			'refine_image_width',
			'refine_image_height',
			'refine_search_number',
			'product_image_zoom',
			'product_image_size',
			'position_image_additional',
			'product_social_share',
			'product_related_status',
			'product_type',
			'product_fixed_bar',
			'hurry',
			'hurry_limit',
			'hurry_text',
			'product_page_radio_style',
			'product_page_radio_image_width',
			'product_page_radio_image_height',
			'product_page_checkbox_style',
			
			'custom_block',
						
			'colors_status',
			
			'background_status',
			'body_background',
			'body_background_background',
			'body_background_subtle_patterns',
			'body_background_position',
			'body_background_repeat',
			'body_background_attachment',
			'header_background',
			'header_background_background',
			'header_background_subtle_patterns',
			'header_background_position',
			'header_background_repeat',
			'header_background_attachment',
						
			'font_status',
			'body_font',
			
			'product_image_zoom',
			
			'product_scroll_latest',
			'product_scroll_featured',
			'product_scroll_bestsellers',
			'product_scroll_specials',
			'product_scroll_related',
			
			'custom_code_css_status',
			'custom_code_css',
			'custom_code_javascript_status',
			'custom_code_js',
			
			'refine_image_width',
			'refine_image_height',
			
			'widget_facebook_status',
			'widget_facebook_id',
			'widget_facebook_position',
			'widget_twitter_status',
			'widget_twitter_id',
			'widget_twitter_user_name',
			'widget_twitter_position',
			'widget_twitter_limit',
			'widget_custom_status',
			'widget_custom_content',
			'widget_custom_position',
			
			'compressor_code_status'
		);
		
		foreach ($config_data as $conf) {
			$data[$conf] = false;
		}

		// Funkcja do usuwania katalogu
		function removeDir($path) { 
			$dir = new DirectoryIterator($path); 
			foreach ($dir as $fileinfo) { 
				if ($fileinfo->isFile() || $fileinfo->isLink()) { 
					unlink($fileinfo->getPathName()); 
				} elseif (!$fileinfo->isDot() && $fileinfo->isDir()) { 
					removeDir($fileinfo->getPathName()); 
				} 
			} 
			rmdir($path); 
		}
		
  		// zeexo MUTLI STORE
  		
			if (isset($this->request->post['store_id'])) {
				$data['store_id'] = $this->request->post['store_id'];
			} else {
				$data['store_id'] = $this->config->get('d_store_id');
			}

			$data['stores'] = array();
			
			$this->load->model('setting/store');
			
			$results = $this->model_setting_store->getStores();
			
			$data['stores'][] = array(
				'name' => 'Default',
				'href' => '',
				'store_id' => 0
			);
				
			foreach ($results as $result) {
				$data['stores'][] = array(
					'name' => $result['name'],
					'href' => $result['url'],
					'store_id' => $result['store_id']
				);
			}		
			
			
			if(isset($_GET['store_id'])) {
				$data['store_id'] = $_GET['store_id'];
			} else {
				if (isset($_GET['submit'])) {
					$data['store_id'] = $data['store_id'];
				} else {
					if (isset($this->request->post['store_id'])) {
						$data['store_id'] = $this->request->post['store_id'];
					} else {
						$data['store_id'] = 0;
					}
				}
			}
			
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				$data['array'] = array(
					'd_store_id' => $this->request->post['store_id']
				);
				$this->model_setting_setting->editSetting('d_id_store', $data['array']);	
			}
			
		// END MULTISTORE
		
		// Pobieranie informacji, która skórka jest włączona	
		$data['setting_skin'] = $this->model_setting_setting->getSetting('zeexo_skin', $data['store_id']);
		
		// Nadanie nazw sklepom 
		if($data['store_id'] == 0) {
			$data['edit_skin_store'] = 'default';
		} else {
			$data['edit_skin_store'] = $data['store_id'];
		}
		
		// Aktywna skórka
		if(isset($data['setting_skin']['zeexo_skin'])) {
			$data['active_skin'] = $data['setting_skin']['zeexo_skin'];
		} else {
			$data['active_skin'] = 'default';
		}
		
		if(!file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin'].'')) {
			$data['active_skin'] = false;
		}
		
		// Tworzenie listy skórek
		if(file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/')) {
			$data['skins'] = array();
			$dir = opendir(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/');
			while(false !== ($file = readdir($dir))) {
				if(is_dir(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$file) && $file != '.' && $file != '..')  {
					$data['skins'][] = $file;
				}
			}
			sort($data['skins']);
		}
		
		// Edycja skórki - sprawdzanie jaki szablon jest edytowany
		if(isset($data['setting_skin']['zeexo_skin'])) {
			$data['active_skin_edit'] = $data['setting_skin']['zeexo_skin'];
		} else {
			$data['active_skin_edit'] = 'default';
		}
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if(isset($_POST['button-edit']) || isset($_POST['button-delete'])){
				$data['active_skin_edit'] = $this->request->post['skin'];
			}
		}
		
		if(isset($this->request->post['save_skin']) && !isset($_POST['button-edit']) && !isset($_POST['button-delete'])) {
			$data['active_skin_edit'] = $this->request->post['save_skin'];
		}
		
		if(isset($_GET['skin_edit'])) {
			$data['active_skin_edit'] = $_GET['skin_edit'];
		}
			
		// Zmiana skórki
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if(isset($_POST['button-active'])){
				$save_zeexo_skin = array(
					'zeexo_skin' => $this->request->post['skin']
				);
				$this->model_setting_setting->editSetting('zeexo_skin', $save_zeexo_skin, $this->request->post['store_id']);	
				$this->session->data['success'] = $this->language->get('text_success');
				$this->response->redirect($this->url->link('extension/module/zeexo&submit=true', 'user_token=' . $this->session->data['user_token'], true));
            }
		}
		
		// Dodawanie skórki
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if(isset($_POST['add-skin'])){
				if(is_writable(DIR_CATALOG . 'view/theme/zeexo/skins/') && (is_writable(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/') || !file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'))) {
					// Sprawdzanie czy istnieje folder store_ 
					if(file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/') && filetype(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/') == 'dir') {
					} else {
						mkdir(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/', 0777);
					}
					
					// Dodawanie pliku z ustawieniami
					if($this->request->post['add-skin-name'] != '') {	
						if(!file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$this->request->post['add-skin-name'].'/')) {
							mkdir(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$this->request->post['add-skin-name'].'/', 0777);
							file_put_contents(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$this->request->post['add-skin-name'].'/settings.json', json_encode($config_data));
							mkdir(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$this->request->post['add-skin-name'].'/js/', 0777);
							file_put_contents(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$this->request->post['add-skin-name'].'/js/custom_code.js', ' ');
							mkdir(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$this->request->post['add-skin-name'].'/css/', 0777);
							file_put_contents(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$this->request->post['add-skin-name'].'/css/custom_code.css', ' ');
							$this->session->data['success'] = $this->language->get('text_success');
							$this->response->redirect($this->url->link('extension/module/zeexo&submit=true', 'user_token=' . $this->session->data['user_token'], true));
						}
					}  
				}

				$this->session->data['error_warning'] = 'You need to set CHMOD 777 for all folder and subfolder in catalog/view/theme/zeexo/skins!';
				$this->response->redirect($this->url->link('extension/module/zeexo&submit=true', 'user_token=' . $this->session->data['user_token'], true));
		    }
		}
		
		// Zapisywanie skórki
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if(isset($_POST['button-save'])){
				if(is_writable(DIR_CATALOG . 'view/theme/zeexo/skins') && is_writable(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store']) && is_writable(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'])) {
					// Sprawdzanie czy istnieje skórka
					if(file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/') && filetype(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/') == 'dir' && file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'')) {
						// Zapisywanie ustawien
						file_put_contents(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'/settings.json', json_encode($this->request->post));  
						
						// Custom js
						file_put_contents(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'/js/custom_code.js', html_entity_decode($this->request->post['custom_code_js']));  
						
						// Custom css
						file_put_contents(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'/css/custom_code.css', html_entity_decode($this->request->post['custom_code_css']));  
						
						// Informacja o zapisaniu ustawien
						$this->session->data['success'] = $this->language->get('text_success');
						$this->response->redirect($this->url->link('extension/module/zeexo&submit=true&skin_edit=' . $data['active_skin_edit'] . '', 'user_token=' . $this->session->data['user_token'], true));
					}
				}
				
				// Jezeli nie istnieje katalog skórki to pojawia się komunikat o błedzie
				$this->session->data['error_warning'] = 'You need to set CHMOD 777 for all folder and subfolder in catalog/view/theme/zeexo/skins!';
				$this->response->redirect($this->url->link('extension/module/zeexo&submit=true', 'user_token=' . $this->session->data['user_token'], true));
			}
		}
		
		// Usuwanie skórki
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if(isset($_POST['button-delete'])){
				if(is_writable(DIR_CATALOG . 'view/theme/zeexo/skins')) {
					// Sprawdzanie czy istnieje skórka
					if(file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/') && filetype(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/') == 'dir' && file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'')) {
						// Sprawdzanie czy skórka jest ustawiona jako aktywna
						if($data['active_skin_edit'] != $data['active_skin']) {
							removeDir(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'');
							
							// Informacja o usunięciu skórki
							$this->session->data['success'] = $this->language->get('text_success');
							$this->response->redirect($this->url->link('extension/module/zeexo&submit=true', 'user_token=' . $this->session->data['user_token'], true));
						}
					}
				} else {
					$this->session->data['error_warning'] = 'You need to set CHMOD 777 for all folder and subfolder in catalog/view/theme/zeexo/skins!';
					$this->response->redirect($this->url->link('extension/module/zeexo&submit=true', 'user_token=' . $this->session->data['user_token'], true));
				}
				
				// Jezeli nie usunięto szablonu to pojawia sie błąd
				$this->session->data['error_warning'] = $this->language->get('text_warning2');
				$this->response->redirect($this->url->link('extension/module/zeexo&submit=true', 'user_token=' . $this->session->data['user_token'], true));
			}
		}
		
		// Pobieranie ustawień szablonu
		if(file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'/settings.json')) {
			$template = json_decode(file_get_contents(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'/settings.json'), true);
			if(isset($template)) {
				foreach ($template as $option => $value) { 
					if($option != 'store_id') {
						$data[$option] = $value;
					}
				}
			}
		}
				
		// Pobieranie ustawień szablon --> custom code js
		if(file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'/js/custom_code.js')) {
			$data['custom_code_js'] = file_get_contents(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'/js/custom_code.js');
		}
		
		// Pobieranie ustawień szablon --> custom code css
		if(file_exists(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'/css/custom_code.css')) {
			$data['custom_code_css'] = file_get_contents(DIR_CATALOG . 'view/theme/zeexo/skins/store_'.$data['edit_skin_store'].'/'.$data['active_skin_edit'].'/css/custom_code.css');
		}
		
		$data['text_image_manager'] = 'Image manager';
		$data['user_token'] = $this->session->data['user_token'];		
		
		$text_strings = array('heading_title');
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}
		
		
		// Instalacja przykladowych danych
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {	
		     // Custom Module
		     if(isset($_POST['install_advanced_grid'])){
		          $output["select_demo"] = $this->request->post['select_demo'];
		          $this->model_setting_setting->editSetting( "select_demo", $output );	
		          
		          include '../data_sample/zeexo/' . $this->request->post['select_demo'] . '/advanced_grid.php'; 
		          
		     	$this->session->data['success'] = $this->language->get('text_success');
		     	$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
		     }

		     // Header notice
		     if(isset($_POST['install_header_notice'])){
		          $output["select_demo"] = $this->request->post['select_demo'];
		          $this->model_setting_setting->editSetting( "select_demo", $output );	
		          
		          include '../data_sample/zeexo/' . $this->request->post['select_demo'] . '/header_notice.php'; 
		          
		     	$this->session->data['success'] = $this->language->get('text_success');
		     	$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
		     }
		     
		     // Filter product
		     if(isset($_POST['install_filter_product'])){
		          $output["select_demo"] = $this->request->post['select_demo'];
		          $this->model_setting_setting->editSetting( "select_demo", $output );	
		          
		          include '../data_sample/zeexo/' . $this->request->post['select_demo'] . '/filter_product.php'; 
		          
		     	$this->session->data['success'] = $this->language->get('text_success');
		     	$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
		     }
		     
		     // Product questions
		     if(isset($_POST['install_product_questions'])){
		          $output["select_demo"] = $this->request->post['select_demo'];
		          $this->model_setting_setting->editSetting( "select_demo", $output );	
		          
		          include '../data_sample/zeexo/' . $this->request->post['select_demo'] . '/product_questions.php'; 
		          
		     	$this->session->data['success'] = $this->language->get('text_success');
		     	$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
		     }
		     
		     // Faq
		     if(isset($_POST['install_faq'])){
		          $output["select_demo"] = $this->request->post['select_demo'];
		          $this->model_setting_setting->editSetting( "select_demo", $output );	
		          
		          include '../data_sample/zeexo/' . $this->request->post['select_demo'] . '/faq.php'; 
		          
		     	$this->session->data['success'] = $this->language->get('text_success');
		     	$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
		     }
		     
		     // Popup
		     if(isset($_POST['install_popup'])){
		          $output["select_demo"] = $this->request->post['select_demo'];
		          $this->model_setting_setting->editSetting( "select_demo", $output );	
		          
		          include '../data_sample/zeexo/' . $this->request->post['select_demo'] . '/popup.php'; 
		          
		     	$this->session->data['success'] = $this->language->get('text_success');
		     	$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
		     }
		     
		     // Product blocks
		     if(isset($_POST['install_product_blocks'])){
		          $output["select_demo"] = $this->request->post['select_demo'];
		          $this->model_setting_setting->editSetting( "select_demo", $output );	
		          
		          include '../data_sample/zeexo/' . $this->request->post['select_demo'] . '/product_blocks.php'; 
		          
		     	$this->session->data['success'] = $this->language->get('text_success');
		     	$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
		     }
		     
			// Custom Module
			if(isset($_POST['install_custom_module'])){
			     $output["select_demo"] = $this->request->post['select_demo'];
			     $this->model_setting_setting->editSetting( "select_demo", $output );	
			     
			     include '../data_sample/zeexo/' . $this->request->post['select_demo'] . '/custom_module.php'; 
			     
				$this->session->data['success'] = $this->language->get('text_success');
				$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
			}

			// Carousel item
			if(isset($_POST['install_carousel_item'])){
			     $output["select_demo"] = $this->request->post['select_demo'];
			     $this->model_setting_setting->editSetting( "select_demo", $output );	
			     
			     include '../data_sample/zeexo/' . $this->request->post['select_demo'] . '/carousel_item.php'; 
			     
				$this->session->data['success'] = $this->language->get('text_success');
				$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
			}
			
			// BLOG
			if(isset($_POST['install_blog'])){
				
				$this->load->model('blog/setup');
				$this->model_blog_setup->installSampleData();
			    
				$this->session->data['success'] = $this->language->get('text_success');
				$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
			}
			
			// Megamenu
			if(isset($_POST['install_megamenu'])){
			     $output["select_demo"] = $this->request->post['select_demo'];
			     $this->model_setting_setting->editSetting( "select_demo", $output );	
			     
				include '../data_sample/zeexo/' . $this->request->post['select_demo'] . '/megamenu_query.php'; 
				
				$this->session->data['success'] = $this->language->get('text_success');
				$this->response->redirect($this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true));
			}
		}
		
		// Pobieranie informacji, ktora wersja dema jest instalowana		
		$data['select_demos'] = $this->model_setting_setting->getSetting('select_demo');
		if(isset($data['select_demos']['select_demo'])) $data['select_demo'] = $data['select_demos']['select_demo'];
		
		//This creates an error message. The error['warning'] variable is set by the call to function validate() in this controller (below)
		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
 		} elseif(isset($this->error['warning'])) {
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

		$data['action'] = $this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);
		
		// Multilanguage
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => 'Modules',
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
		);
				
		$data['breadcrumbs'][] = array(
			'text' => 'zeexo Theme Options',
			'href' => $this->url->link('extension/module/zeexo', 'user_token=' . $this->session->data['user_token'], true)
		);
				
		// No image
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
				
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
				
		$this->response->setOutput($this->load->view('extension/module/zeexo', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/zeexo')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}
?>