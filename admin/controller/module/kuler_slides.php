<?php

/*--------------------------------------------------------------------------/
* @Author		KulerThemes.com http://www.kulerthemes.com
* @Copyright	Copyright (C) 2012 - 2013 KulerThemes.com. All rights reserved.
* @License		KulerThemes.com Proprietary License
/---------------------------------------------------------------------------*/

class ControllerModuleKulerslides extends Controller {
	private $error = array(); 
	private $vtab = '';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->data['languages'] = $this->getLanguageOptions();

        $this->data['config_admin_language_id'] = 1;

        foreach ($this->data['languages'] as $language)
        {
            if ($language['code'] == $this->config->get('config_admin_language'))
            {
                $this->data['config_admin_language_id'] = $language['language_id'];
                break;
            }
        }
    }

	public function index() {
        $this->data['__'] = $this->language->load('module/kuler_slides');

        $this->beforeBuildingMode();
        $this->getTabActive();
		$this->getLanguages();
		$this->getPathways();
        $this->getStores();
        $this->getResources();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->saveAction();
		}
		
		$this->getErrors();
		
		$this->data['action'] = $this->url->link('module/kuler_slides', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['modules'] = array();
		
		if (isset($this->request->post['kuler_slides_module'])) {
			$this->data['modules'] = $this->request->post['kuler_slides_module'];
		} else {
            $this->load->model('setting/setting');

            if ($kuler_slides = $this->model_setting_setting->getSetting('kuler_slides', $this->data['selected_store_id']))
            {
                if (isset($kuler_slides['kuler_slides_module']))
                {
                    $this->data['modules'] = $kuler_slides['kuler_slides_module'];
                }
            }
        }

        $this->data['modules'] = $this->prepareModules($this->data['modules']);
		
		// Load module tab product by id 
		if(isset($this->data['modules']) && is_array($this->data['modules'])) {
			$this->load->model('catalog/product');
			foreach($this->data['modules'] as $module_row => $module) {
				if(isset($module['tabs']) && is_array($module['tabs'])) {
					foreach($module['tabs'] as $tab_row => $tab) {
						$products = array();
						$list = explode(',', $tab['products']);
						foreach ($list as $product_id) {
							$product_info = $this->model_catalog_product->getProduct($product_id);
							if ($product_info) {
								$item = array(
									'product_id' => $product_info['product_id'],
									'name'       => $product_info['name']
								);

								$products[$product_id] = $item;
							}
						}
						$this->data['modules'][$module_row]['tabs'][$tab_row]['list'] = $products;
					}
				}
			}
		}
		
		$this->data['token'] = $this->session->data['token'];
		$this->data['layouts'] = $this->getLayouts();
		$this->data['categories'] = $this->getCategories();
		
		$this->template = 'module/kuler_slides.phtml';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

    public function autocomplete() {
        $json = array();

        if (isset($this->request->get['filter_name']))
        {
            $this->load->model('module/kuler_slides');

            $filter_name = $this->request->get['filter_name'];

            $data = array(
                'filter_name'  => $filter_name,
                'store_id'     => $this->request->get['store_id'],
            );

            $results = $this->model_module_kuler_slides->getProducts(array(
                    'filter_name' => $filter_name,
                    'store_id' => $this->request->get['store_id']
                ), array(
                    'start' => 0,
                    'limit' => 20
                )
            );

            foreach ($results as $result)
            {
                $json[] = array(
                    'product_id' => $result['product_id'],
                    'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
                );
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    private function prepareModules(array $modules)
    {
        foreach ($modules as &$module)
        {
            $module['module_title'] = $this->translate($module['module_title']);
            $module['main_title'] = $module['module_title'][$this->config->get('config_language_id')];
        }

        return $modules;
    }
	
	protected function getResources() {
		$this->document->addStyle('view/kulercore/css/kulercore.css');
	}
	
	protected function getLayouts() {
		$this->load->model('design/layout');
		$result = $this->model_design_layout->getLayouts();
		$first = array(
			'layout_id' => 0,
			'name' => 'All layout'
		);
		//array_unshift($result, $first);
		return $result;
	}

    private function getStores()
    {
        $this->load->model('setting/store');

        $rows = $this->model_setting_store->getStores();

        $stores = array(
            0 => $this->config->get('config_name') . $this->language->get('text_default')
        );

        foreach ($rows as $row)
        {
            $stores[$row['store_id']] = $row['name'];
        }

        $this->data['selected_store_id'] = 0;
        if (isset($this->request->post['store_id']))
        {
            $this->data['selected_store_id'] = $this->request->post['store_id'];
        }
        else if (isset($this->request->get['store_id']))
        {
            $this->data['selected_store_id'] = $this->request->get['store_id'];
        }

        $this->data['stores'] = $stores;

        return $stores;
    }

    protected function getCategories() {
        $this->load->model('module/kuler_slides');

        $cates = array();
        $results = $this->model_module_kuler_slides->getCategories(array('store_id' => $this->data['selected_store_id']));

        foreach ($results as $result) {
            $cates[] = array(
                'category_id' => $result['category_id'],
                'name'        => $result['name'],
            );
        }

        return $cates;
    }
	
	protected function saveAction() {
		$this->load->model('setting/setting');

        $this->request->post['kuler_slides_module'] = isset($this->request->post['kuler_slides_module']) ? $this->request->post['kuler_slides_module'] : array();

        $data = array(
            'kuler_slides_module' => $this->request->post['kuler_slides_module']
        );

		$this->model_setting_setting->editSetting('kuler_slides', $data, $this->request->post['store_id']);

		$this->session->data['success'] = $this->language->get('text_success');

        $this->postBuildingMode($this->request->post['kuler_slides_module']);
        
        if(isset($this->request->post['op']) && $this->request->post['op'] == 'close') {
            $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        } else {
            $this->redirect($this->url->link('module/kuler_slides', 'token=' . $this->session->data['token'] . '&store_id=' . $this->request->post['store_id'], 'SSL'));
        }
	}

    protected function beforeBuildingMode()
    {
        // Initialize building mode
        if (isset($this->request->get['ksb_module']))
        {
            $this->session->data['ksb_module'] = $this->request->request['ksb_module'];
            $this->session->data['ksb_building_mode'] = 1;
            $this->session->data['ksb_new'] = $this->request->request['ksb_new'];
            $this->session->data['ksb_token'] = $this->request->request['token'];

            if ($this->request->get['ksb_new'])
            {
                $this->data['ksb_trigger_creation'] = true;
            }
        }

        // Check building mode
        if (isset($this->session->data['ksb_building_mode']) && $this->session->data['ksb_token'] == $this->session->data['token'])
        {
            $this->data['ksb_building_mode'] = 1;

            $this->session->data['kuler_tab'] = '#tab-module-' . $this->session->data['ksb_module'];
        }
        else
        {
            unset(
            $this->session->data['ksb_module'],
            $this->session->data['ksb_building_mode'],
            $this->session->data['ksb_new'],
            $this->session->data['ksb_token']
            );
        }

        // Get the updated module
        if (isset($this->session->data['ksb_updated_module']))
        {
            $this->data['ksb_updated_module'] = $this->session->data['ksb_updated_module'];
            unset($this->session->data['ksb_updated_module']);
        }
    }

    protected function postBuildingMode($modules)
    {
        if (!isset($this->session->data['ksb_building_mode']))
        {
            return;
        }

        $module = array();

        if (isset($this->session->data['ksb_new']) && $this->session->data['ksb_new'])
        {
            $module = end($modules);
            $indexes = array_keys($modules);
            $module['index'] = array_pop($indexes);
            $this->session->data['ksb_module'] = $module['index'];
        }
        else
        {
            $module = $modules[$this->session->data['ksb_module']];
        }

        if (isset($module['module_title']))
        {
            $module['title'] = $module['module_title'];
        }

        $this->session->data['ksb_updated_module'] = json_encode(array(
            'status' => '1',
            'module' => $module
        ));
    }
	
    protected function getTabActive() {
        // Remove last active tab
        if(isset($this->session->data['kuler_tab'])) {
            $this->data['vtab'] = $this->session->data['kuler_tab']; unset($this->session->data['kuler_tab']);
        } else {
            $this->data['vtab'] = '';
        }
        // Store current active tab
        if(isset($this->request->post['tab'])) {
            $this->session->data['kuler_tab'] = $this->request->post['tab'];
        }
    }
    
	protected function getLanguages() {
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data['tab_module'] = $this->language->get('tab_module');
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_prefix'] = $this->language->get('text_prefix');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_content_top'] = $this->language->get('text_content_top');
		$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->data['text_column_left'] = $this->language->get('text_column_left');
		$this->data['text_column_right'] = $this->language->get('text_column_right');
		$this->data['text_no_result'] = $this->language->get('text_no_result');
		$this->data['text_category'] = $this->language->get('text_category');
		$this->data['text_product'] = $this->language->get('text_product');
		$this->data['text_bestseller'] = $this->language->get('text_bestseller');
		$this->data['text_latest'] = $this->language->get('text_latest');
		$this->data['text_special'] = $this->language->get('text_special');
        $this->data['text_featured'] = $this->language->get('text_featured');

        $this->data['entry_banner'] = $this->language->get('entry_banner');
		$this->data['entry_dimension'] = $this->language->get('entry_dimension'); 
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_showtitle'] = $this->language->get('entry_showtitle');
        
		$this->data['entry_module'] = $this->language->get('entry_module');
		$this->data['entry_type'] = $this->language->get('entry_type');
		$this->data['entry_tab_title'] = $this->language->get('entry_tab_title');
		$this->data['entry_tab_type'] = $this->language->get('entry_tab_type');
		$this->data['entry_category'] = $this->language->get('entry_category');
		$this->data['entry_limit'] = $this->language->get('entry_limit');
		$this->data['entry_item'] = $this->language->get('entry_item');
		$this->data['entry_dimension'] = $this->language->get('entry_dimension');
		$this->data['entry_product'] = $this->language->get('entry_product');
		
		$this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_close'] = $this->language->get('button_close');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_module'] = $this->language->get('button_add_module');
		$this->data['button_remove'] = $this->language->get('button_remove');		
	}
	
	/**
	 * @todo : Set pathway
	 */
	protected function getPathways() {
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/kuler_slides', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
	}
	
	/**
	 * @todo : Set error message form
	 */
	protected function getErrors() {
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['dimension'])) {
			$this->data['error_dimension'] = $this->error['dimension'];
		} else {
			$this->data['error_dimension'] = array();
		}
		
		if (isset($this->error['limit'])) {
			$this->data['error_limit'] = $this->error['limit'];
		} else {
			$this->data['error_limit'] = array();
		}
		
		if (isset($this->error['item'])) {
			$this->data['error_item'] = $this->error['item'];
		} else {
			$this->data['error_item'] = array();
		}
	}
	
	/**
	 * @todo : Validate form beforeSave
	 */
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/kuler_slides')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (isset($this->request->post['kuler_slides_module'])) {
			foreach ($this->request->post['kuler_slides_module'] as $key => $value) {
				if(isset($value['tabs']) && is_array($value['tabs'])) {
					foreach($value['tabs'] as $k => $item) {
                        $valid = true;
						if (!$item['image_width'] || !$item['image_height']) {
							$this->error['dimension'][$key][$k] = $this->language->get('error_dimension');
                            $this->data['vtab'] = '#tab-module-' . $key;
                            $valid = false;
						}

						if(!is_numeric($item['limit'])) {
							$this->error['limit'][$key][$k] = $this->language->get('error_limit');
                            $this->data['vtab'] = '#tab-module-' . $key;
                            $valid = false;
						}

						if(!is_numeric($item['item'])) {
							$this->error['item'][$key][$k] = $this->language->get('error_item');
                            $this->data['vtab'] = '#tab-module-' . $key;
                            $valid = false;
						}
                        if($valid == false) {
                            return false;
                        }
					}
				}
			}
		}	

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

    private function translate($texts)
    {
        $languages = $this->getLanguageOptions();

        if (is_string($texts))
        {
            $text = $texts;
            $texts = array();

            foreach ($languages as $language)
            {
                $texts[$language['language_id']] = $text;
            }
        }
        else if (is_array($texts))
        {
            $first = current($texts);

            foreach ($languages as $language)
            {
                if (is_string($first))
                {
                    if (empty($texts[$language['language_id']]))
                    {
                        $texts[$language['language_id']] = $first;
                    }
                }
                else if (is_array($first))
                {
                    if (!isset($texts[$language['language_id']]))
                    {
                        $texts[$language['language_id']] = array();
                    }

                    foreach ($first as $key => $val)
                    {
                        if (empty($texts[$language['language_id']][$key]))
                        {
                            $texts[$language['language_id']][$key] = $val;
                        }
                    }
                }
            }
        }

        return $texts;
    }

    private function getLanguageOptions()
    {
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $config_language = $this->config->get('config_language');

        $results = array();
        $default_language = $languages[$config_language];
        unset($languages[$config_language]);

        $results[$config_language] = $default_language;
        $results = array_merge($results, $languages);

        return $results;
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');

        $stores = $this->getStores();

        foreach ($stores as $store_id => $store_name)
        {
            $this->model_setting_setting->deleteSetting('kuler_slides', $store_id);
        }
    }
}
?>