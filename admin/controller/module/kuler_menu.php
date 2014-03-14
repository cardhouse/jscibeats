<?php
class ControllerModuleKulerMenu extends Controller {
	private $error = array();

	public function index() {
        $this->load->model('module/kuler_menu');
        $this->load->model('localisation/language');
        
		$this->getLanguages();
		$this->getPathways();
		$this->getResources();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->saveAction();
		}

        $token = $this->session->data['token'];
        $languages = $this->getDataLanguages();

		$this->getErrors();
		
		$this->data['action'] = $this->url->link('module/kuler_menu', 'token=' . $token, 'SSL');
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $token, 'SSL');

        // Default data
        $default_menu = array(
            'type' => 'category',
            'title' => '',
            'status' => 1,
            'sort_order' => '',

            // Category
            'show_sub_categories' => 1,
            'category_image_width' => 80,
            'category_image_height' => 80,
            'image' => 1,
            'image_position' => 'float-left',

            // Product
            'image_width' => '155',
            'image_height' => '155',
            'name' => 1,
            'price' => 1,
            'rating' => 1,
            'description' => 1,
            'description_text' => 100,
            'add' => 1,
            'wishlist' => 1,
            'compare' => 1,

            // Custom
            'link' => '',
            'new_tab' => 1
        );

        $default_product_row = array(
            'product_id' => '',
            'name' => ''
        );

        $default_category_row = array(
            'category_id' => '',
            'name' => ''
        );

        $default_custom_row = array(
            'link' => '',
            'new_tab' => 1
        );
        foreach ($languages as $language)
        {
            $default_custom_row['titles'][$language['language_id']] = '';
        }

        // Get menu
        $this->data['menus'] = array();
        if (isset($this->request->post['menus']))
        {
            $this->data['menus'] = $this->request->post['menus'];
        }
        else if ($this->config->get('kuler_menu'))
        {
            $this->data['menus'] = $this->config->get('kuler_menu');
        }

        $this->data['menus'] = $this->prepareModules($this->data['menus']);

        // Get menu status
        $this->data['menu_status'] = 0;
        if (isset($this->request->post['menu_status']))
        {
            $this->data['menu_status'] = $this->request->post['menu_status'];
        }
        else
        {
            $this->data['menu_status'] = $this->config->get('kuler_menu_status');
        }

        $this->data['language_id'] = $this->config->get('config_language_id');

        $this->data['default_menu'] = $default_menu;
        $this->data['default_product_row'] = $default_product_row;
        $this->data['default_category_row'] = $default_category_row;
        $this->data['default_custom_row'] = $default_custom_row;

        $this->data['token'] = $token;
        $this->data['languages'] = $languages;

		$this->template = 'module/kuler_menu.phtml';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

    private function prepareModules($modules)
    {
        if (is_array($modules))
        {
            foreach ($modules as &$module)
            {
                $module['title'] = $this->translate($module['title']);
                $module['main_title'] = $module['title'][$this->config->get('config_language_id')];

                if ($module['type'] == 'custom')
                {
                    if (isset($module['links']) && is_array($module['links']))
                    {
                        foreach ($module['links'] as &$link)
                        {
                            $link['titles'] = $this->translate($link['titles']);
                        }
                    }
                }
                else if ($module['type'] == 'html')
                {
                    $module['htmls'] = $this->translate($module['htmls']);
                }
            }
        }

        return $modules;
    }
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/kuler_menu'))
        {
			$this->error['warning'] = $this->language->get('error_permission');
		}

        if (!empty($this->request->post['menus']))
        {
            foreach ($this->request->post['menus'] as $menu_index => $menu)
            {
                if ($menu['type'] == 'product' && (empty($menu['image_width']) || empty($menu['image_height'])))
                {
                    $this->error['error_product_dimension'] = array(
                        $menu_index => $this->language->get('error_product_dimension')
                    );

                    break;
                }
            }
        }

		if (!$this->error)
        {
			return true;
		}
        else
        {
			return false;
		}	
	}
		
	protected function saveAction() {
        $this->load->model('setting/setting');

		if (isset($this->request->post['menus']))
		{
			$setting = array(
				'kuler_menu' => $this->request->post['menus'],
				'kuler_menu_status' => $this->request->post['menu_status']
			);
		}
		else
		{
			$setting = array();
		}

		$this->model_setting_setting->editSetting('kuler_menu',  $setting);

		$this->session->data['success'] = $this->language->get('text_success');
        
        if(isset($this->request->post['op']) && $this->request->post['op'] == 'close') {
            $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        } else {
            $this->redirect($this->url->link('module/kuler_menu', 'token=' . $this->session->data['token'], 'SSL'));
        }
	}

	protected function getResources() {
        $this->document->addStyle('view/kulercore/css/kulercore.css');
        $this->document->addStyle('view/kulercore/css/kuler_menu.css');
        $this->document->addScript('view/kulercore/js/handlebars.js');
        $this->document->addScript('view/javascript/ckeditor/ckeditor.js');
	}
	
	protected function getLayouts() {
		$this->load->model('design/layout');
		$result = $this->model_design_layout->getLayouts();
		return $result;
	}
	
	protected function getLanguages() {
        $__ = $this->language->load('module/kuler_menu');
		$this->data = array_merge($this->data, $__);
        $this->data['__'] = $__;
		$this->document->setTitle($this->data['heading_title']);

        // Load system language
        $texts = array(
            // Buttons
            'button_save',
            'button_cancel',
            'button_close',
        );

        foreach ($texts as $text)
        {
            $this->data[$text] = $this->language->get($text);
        }
	}
	
	protected function getErrors() {
		if (isset($this->error['warning']))
        {
			$this->data['error_warning'] = $this->error['warning'];
		}
        else
        {
			$this->data['error_warning'] = '';
		}

        $this->data['error_product_image'] = isset($this->error['error_product_dimension']) ? $this->error['error_product_dimension'] : array();
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
			'href'      => $this->url->link('module/kuler_menu', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
	}

    protected function getDataLanguages()
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
}
?>