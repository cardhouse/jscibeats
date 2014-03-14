<?php
class ControllerModuleKulerFilter extends Controller {
	private $error = array();

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->data['__'] = $this->language->load('module/kuler_filter');
    }

    /**
     * Add route module/kuler_filter_result under each route product/category
     */
    public function install() {
		$this->load->model('design/layout');

        /* @var ModelDesignLayout $layout_model */
        $layout_model = $this->model_design_layout;
		
        $layouts = $layout_model->getLayouts();

        if (is_array($layouts))
        {
            foreach ($layouts as $layout)
            {
                $routes = $layout_model->getLayoutRoutes($layout['layout_id']);

                if (is_array($routes))
                {
                    $need_edit = false;
                    $extra_routes = $routes;

                    foreach ($routes as $route)
                    {
                        if ($route['route'] == 'product/category')
                        {
                            $extra_routes[] = array(
                                'route' => 'module/kuler_filter_result',
                                'store_id' => $route['store_id']
                            );

                            $need_edit = true;
                        }
                    }

                    if ($need_edit)
                    {
                        $layout['layout_route'] = $extra_routes;
                        $layout_model->editLayout($layout['layout_id'], $layout);
                    }
                }
            }
        }
	}

    /**
     * Remove route module/kuler_filter_result
     */
    public function uninstall() {
        $this->load->model('design/layout');

        /* @var ModelDesignLayout $layout_model */
        $layout_model = $this->model_design_layout;

        $layouts = $layout_model->getLayouts();

        if (is_array($layouts))
        {
            foreach ($layouts as $layout)
            {
                $routes = $layout_model->getLayoutRoutes($layout['layout_id']);

                if (is_array($routes))
                {
                    $need_edit = false;

                    foreach ($routes as $route_index => $route)
                    {
                        if ($route['route'] == 'module/kuler_filter_result')
                        {
                            unset($routes[$route_index]);

                            $need_edit = true;
                        }
                    }

                    if ($need_edit)
                    {
                        $layout['layout_route'] = $routes;
                        $layout_model->editLayout($layout['layout_id'], $layout);
                    }
                }
            }
        }
	}
	
	public function index() {   

		$this->getLanguages();
		$this->getPathways();
        $this->getTabActive();
        
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->saveAction();
		}

		$this->getError();
		
		$this->data['action'] = $this->url->link('module/kuler_filter', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['languages'] = $this->getLanguageOptions();

        $default = array(
            'filter_type' => 'select',
            'price_filter' => 1,
            'price_min' => 0,
            'price_max' => 1000
        );
        $this->data['default'] = $default;

		$this->data['modules'] = array();
		
		$this->data['currencies'] = array();
		
		if (isset($this->request->post['kuler_filter_module'])) {
			$this->data['modules'] = $this->request->post['kuler_filter_module'];
		} elseif ($this->config->get('kuler_filter_module')) { 
			$this->data['modules'] = $this->config->get('kuler_filter_module');
		}

        $this->data['modules'] = $this->prepareModules($this->data['modules']);
		
		$this->load->model('localisation/currency');
		
		$results = $this->model_localisation_currency->getCurrencies();	

		foreach ($results as $result) {
			if ($result['status']) {
				$this->data['currencies'][] = array(
					'title'        => $result['title'],
					'code'         => $result['code'],
					'symbol_left'  => $result['symbol_left'],
					'symbol_right' => $result['symbol_right']				
				);
			}
		}

		$this->data['token'] = $this->session->data['token'];
		$this->data['layouts'] = $this->getLayouts();
		
		$this->template = 'module/kuler_filter.phtml';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

    private function prepareModules(array $modules)
    {
        foreach ($modules as &$module)
        {
            $module['title'] = $this->translate($module['title']);
            $module['main_title'] = $module['title'][$this->config->get('config_language_id')];
        }

        return $modules;
    }

    protected function getTabActive() {
        if(isset($this->session->data['kuler_tab'])) {
            $this->data['tab'] = $this->session->data['kuler_tab']; unset($this->session->data['kuler_tab']);
        } else {
            $this->data['tab'] = '';
        }
        if(isset($this->request->post['tab'])) {
            $this->session->data['kuler_tab'] = $this->request->post['tab'];
        }
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
	
	protected function saveAction() {
		$this->load->model('setting/setting');

		$this->model_setting_setting->editSetting('kuler_filter', $this->request->post);		

		$this->session->data['success'] = $this->language->get('text_success');

        if(isset($this->request->post['op']) && $this->request->post['op'] == 'close') {
            $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        } else {
            $this->redirect($this->url->link('module/kuler_filter', 'token=' . $this->session->data['token'], 'SSL'));
        }
	}

//    private function getDefault
	
	protected function getLanguages() {
		$this->data['__'] = $this->language->load('module/kuler_filter');
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_module'] = $this->language->get('text_module');
		$this->data['text_success'] = $this->language->get('text_success');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_content_top'] = $this->language->get('text_content_top');
		$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->data['text_column_left'] = $this->language->get('text_column_left');
		$this->data['text_column_right'] = $this->language->get('text_column_right');
        $this->data['text_select_box'] = $this->language->get('text_select_box');
        $this->data['text_option_list'] = $this->language->get('text_option_list');
        $this->data['text_price_hint'] = $this->language->get('text_price_hint');

		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_title'] = $this->language->get('entry_title');
		$this->data['entry_showtitle'] = $this->language->get('entry_showtitle');
		
		$this->data['entry_category'] = $this->language->get('entry_category');
		$this->data['entry_price'] = $this->language->get('entry_price');
		$this->data['entry_currency'] = $this->language->get('entry_currency');
		$this->data['entry_manufacture'] = $this->language->get('entry_manufacture');
		$this->data['entry_attribute'] = $this->language->get('entry_attribute');
		$this->data['entry_auto'] = $this->language->get('entry_auto');
        $this->data['entry_filter_type'] = $this->language->get('entry_filter_type');
        $this->data['entry_price_filter'] = $this->language->get('entry_price_filter');

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
			'href'      => $this->url->link('module/kuler_filter', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
	}
	
	/**
	 * @todo : Set error message form
	 */
	protected function getError() {
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

        $this->data['error_price_range'] = isset($this->error['error_price_range']) ? $this->error['error_price_range'] : '';
	}
	
	/**
	 * @todo : Validate form beforeSave
	 */
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/kuler_filter')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

        if (isset($this->request->post['kuler_filter_module']))
        {
            foreach ($this->request->post['kuler_filter_module'] as $row => $module)
            {
                if ($module['price_filter'] && ($module['price_min'] === '' || $module['price_max'] === ''))
                {
                    $this->error['error_price_range'][$row] = $this->language->get('error_price_range');
                    $this->data['tab'] = $this->request->post['tab'];
                    break;
                }
            }
        }

        return !$this->error ? true : false;
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
}
?>