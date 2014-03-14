<?php

/*--------------------------------------------------------------------------/
* @Author		KulerThemes.com http://www.kulerthemes.com
* @Copyright	Copyright (C) 2012 - 2013 KulerThemes.com. All rights reserved.
* @License		KulerThemes.com Proprietary License
/---------------------------------------------------------------------------*/

class ControllerModuleKulerAdvancedHtml extends Controller {
	private $error = array(); 
	 
	public function index() {
		$this->getLanguages();
		$this->getPathways();
        $this->getStores();
		$this->getResources();
        $this->beforeBuildingMode();
		$this->getTabActive();
        
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->saveAction();
		}

		$this->getErrors();
		
		$this->data['action'] = $this->url->link('module/kuler_advanced_html', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token'] = $this->session->data['token'];

        $this->data['base'] = $this->getCatalogUrl();

		$this->data['modules'] = array();

        $default_module = array(
            'widget_icon_thumb' => '',
            'widget_type' => 'html',
            'product' => '',
            'image_width' => 80,
            'image_height' => 80,
            'name' => 1,
            'price' => 1,
            'rating' => 1,
            'product_description' => 1,
            'description_text' => 100,
            'add' => 1,
            'wishlist' => 1,
            'compare' => 1
        );
		
		if (isset($this->request->post['kuler_advanced_html_module'])) {
			$this->data['modules'] = $this->request->post['kuler_advanced_html_module'];
		}
        else {
            $this->load->model('setting/setting');

            if ($kuler_advanced_html = $this->model_setting_setting->getSetting('kuler_advanced_html', $this->data['selected_store_id']))
            {
                $modules = array();

                if (isset($kuler_advanced_html['kuler_advanced_html_module']))
                {
                    $modules = $kuler_advanced_html['kuler_advanced_html_module'];

                    foreach ($modules as $module_index => $module)
                    {
                        if (isset($module['module_type']) && $module['module_type'] == 'widget')
                        {
                            unset($modules[$module_index]);
                        }
                    }
                }

                if (isset($kuler_advanced_html['kuler_advanced_html_widgets']))
                {
                    $widgets = $kuler_advanced_html['kuler_advanced_html_widgets'];

                    foreach ($widgets as $widget)
                    {
                        $widget = array_merge($default_module, $widget);
                        $widget['widget_icon_thumb'] = $this->data['base'] . $widget['widget_icon'];

                        $modules[] = $widget;
                    }

                    $this->data['modules'] = $this->sort($modules, 'module_index');
                }
            }
        }

        $this->data['default_module'] = $default_module;
		$this->data['layouts'] = $this->getLayouts();
        $this->data['languages'] = $this->getDataLanguages();

        foreach ($this->data['modules'] as $module_index => $module)
        {
            $this->data['modules'][$module_index]['title'] = $this->translate($this->data['modules'][$module_index]['title']);
            $this->data['modules'][$module_index]['main_title'] = $this->data['modules'][$module_index]['title'][$this->config->get('config_language_id')];

            $this->data['modules'][$module_index]['description'] = $this->translate($this->data['modules'][$module_index]['description']);
        }

		$this->template = 'module/kuler_advanced_html.phtml';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/kuler_advanced_html')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

        if (isset($this->request->post['kuler_advanced_html_module']))
        {
            foreach ($this->request->post['kuler_advanced_html_module'] as $key => $value)
            {
                $valid = true;

                if (!$value['image_width'] || !$value['image_height'])
                {
                    $this->error['dimension'][$key] = $this->language->get('error_dimension');
                    $this->data['tab'] = '#tab-module-' . $key;
                    $valid = false;
                }

                if ($valid == false)
                {
                    return false;
                }
            }
        }

        if (!$this->error) {
			return true;
		} else {
			return false;
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

            $this->session->data['kuler_vtab'] = '#tab-module-' . $this->session->data['ksb_module'];
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
        if(isset($this->session->data['kuler_vtab'])) {
            $this->data['vtab'] = $this->session->data['kuler_vtab']; unset($this->session->data['kuler_vtab']);
        } else {
            $this->data['vtab'] = '';
        }
        if(isset($this->session->data['kuler_htab'])) {
            $this->data['htab'] = $this->session->data['kuler_htab']; unset($this->session->data['kuler_htab']);
        } else {
            $this->data['htab'] = '';
        }
        // Store current active tab
        if(isset($this->request->post['vtab'])) {
            $this->session->data['kuler_vtab'] = $this->request->post['vtab'];
        }
        
        if(isset($this->request->post['htab'])) {
            $this->session->data['kuler_htab'] = $this->request->post['htab'];
        }
    }
    
	protected function getResources() {
		$this->document->addStyle('view/kulercore/css/kulercore.css');
        $this->document->addStyle('view/kulercore/colorpicker/css/colorpicker.css');
        $this->document->addScript('view/kulercore/colorpicker/js/colorpicker.js');
	}
	
	protected function getLayouts() {
		$this->load->model('design/layout');
		$result = $this->model_design_layout->getLayouts();
		return $result;
	}

    public function getDataLanguages()
    {
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $config_language = $this->config->get('config_language');

        $results = array();
        $default_language = $languages[$config_language];
        unset($languages[$config_language]);

        $results[$config_language] = $default_language;
        foreach ($languages as $code => $language)
        {
            $results[$code] = $language;
        }

        return $results;
    }
	
	protected function getLanguages() {
		$this->data['__'] = $this->language->load('module/kuler_advanced_html');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_content_top'] = $this->language->get('text_content_top');
		$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->data['text_column_left'] = $this->language->get('text_column_left');
		$this->data['text_column_right'] = $this->language->get('text_column_right');
		
		$this->data['entry_description'] = $this->language->get('entry_description');
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_title'] = $this->language->get('entry_title');
		$this->data['entry_showtitle'] = $this->language->get('entry_showtitle');
		
		$this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_close'] = $this->language->get('button_close');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_module'] = $this->language->get('button_add_module');
		$this->data['button_remove'] = $this->language->get('button_remove');
		
		$this->data['tab_module'] = $this->language->get('tab_module');		
	}
	
	protected function saveAction() {
		$this->load->model('setting/setting');

        $modules = array();
        $widgets = array();
        if (!empty($this->request->post['kuler_advanced_html_module']))
        {
            $layouts = $this->getLayouts();

            foreach ($this->request->post['kuler_advanced_html_module'] as $module_index => $module)
            {
                $module['module_index'] = $module_index;

                if ($module['module_type'] == 'standard')
                {
                    $modules[] = $module;
                }
                else if ($module['module_type'] == 'widget')
                {
                    $widgets[] = $module;

                    // Create virtual module
                    if ($module['status'])
                    {
                        unset($module['module_index']);
                        $module['position'] = 'content_bottom';
                        foreach ($layouts as $layout)
                        {
                            $module['layout_id'] = $layout['layout_id'];
                            $modules[] = $module;
                        }
                    }
                }
            }
        }

		$this->model_setting_setting->editSetting('kuler_advanced_html', array(
            'kuler_advanced_html_module' => $modules,
            'kuler_advanced_html_widgets' => $widgets
        ), $this->request->post['store_id']);

		$this->session->data['success'] = $this->language->get('text_success');

        $this->postBuildingMode(isset($this->request->post['kuler_advanced_html_module']) ? $this->request->post['kuler_advanced_html_module'] : array());
        
        if(isset($this->request->post['op']) && $this->request->post['op'] == 'close') {
            $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        } else {
            $this->redirect($this->url->link('module/kuler_advanced_html', 'token=' . $this->session->data['token'] . '&store_id=' . $this->request->post['store_id'], 'SSL'));
        }
	}
	
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
	}
	
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
			'href'      => $this->url->link('module/kuler_advanced_html', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
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

    private function getCatalogUrl()
    {
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')))
        {
            $base = $this->config->get('config_ssl') ? $this->config->get('config_ssl') : HTTPS_CATALOG;
        }
        else
        {
            $base = $this->config->get('config_url') ? $this->config->get('config_url') : HTTP_CATALOG;
        }

        return $base;
    }

    private function translate($texts)
    {
        $languages = $this->getDataLanguages();

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

    private function sort(array $items, $field = 'sort_order')
    {
        $sortOrder = array();
        foreach ($items as $key => $value)
        {
            $sortOrder[$key] = $value[$field];
        }
        array_multisort($sortOrder, SORT_ASC, $items);

        return $items;
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');

        $stores = $this->getStores();

        foreach ($stores as $store_id => $store_name)
        {
            $this->model_setting_setting->deleteSetting('kuler_advanced_html', $store_id);
        }
    }
}
?>