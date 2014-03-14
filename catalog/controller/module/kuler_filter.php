<?php  
class ControllerModuleKulerFilter extends Controller {
	protected function index($setting) {
		static $module = 0;

        $this->data['__'] = $this->language->load('module/kuler_filter');

        $setting['option'] = !isset($setting['option']) || $setting['option'] ? true : false;
        $setting['option_filter_type'] = !isset($setting['option_filter_type']) ? $setting['filter_type'] : $setting['option_filter_type'];

        if (!$setting['price_filter'])
        {
            unset($setting['price_min'], $setting['price_max']);
        }

		$this->data['module_title'] = $this->translate($setting['title'], $this->config->get('config_language_id'));
		$this->data['show_title'] = $setting['show_title'];
		$this->data['setting'] = $setting;
		
		$this->data['module'] = $module++;
		
		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('module/kuler_filter');

		$filter = array(
			'category' => isset($this->request->get['category']) ? $this->request->get['category'] : '',
            'brand' => isset($this->request->get['brand']) ? $this->request->get['brand'] : ''
		);
		
		if(isset($setting['category']) && $setting['category']) {
			$this->data['category_path'] = $filter['category'];

			$this->data['categories'] = array();

			$categories = $this->model_catalog_category->getCategories(0);

			foreach ($categories as $category) {
				$total = $this->model_catalog_product->getTotalProducts(array('filter_category_id' => $category['category_id']));

				$children_data = array();

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {
					$data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);

					$product_total = $this->model_catalog_product->getTotalProducts($data);

					$total += $product_total;

					$children_data[] = array(
						'category_id' => $child['category_id'],
						'name'        => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $product_total . ')' : ''),
						'href'        => $this->url->link('product/kuler_filter', 'category=' . $child['category_id'])
					);		
				}

				$this->data['categories'][] = array(
					'category_id' => $category['category_id'],
					'name'        => $category['name'] . ($this->config->get('config_product_count') ? ' (' . $total . ')' : ''),
					'children'    => $children_data,
					'href'        => $this->url->link('product/kuler_filter', 'category=' . $category['category_id'])
				);	
			}
		}
		
		if(isset($setting['manufacture']) && $setting['manufacture']) {
			$this->data['brand'] = isset($this->request->get['brand']) ? $this->request->get['brand'] : 0;
			$this->data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
			if($this->data['brand']) {
				$filter['brand'] = $this->data['brand'];
			}
		}

		if($setting['price_filter'])
        {
			$this->data['price_min'] = isset($this->request->get['price_min']) ? $this->request->get['price_min'] : $setting['price_min'];
			if ($this->data['price_min'])
            {
				$filter['price_min'] = $this->data['price_min'];
			}

            $this->data['price_max'] = isset($this->request->get['price_max']) ? $this->request->get['price_max'] : $setting['price_max'];
            if ($this->data['price_max'])
            {
                $filter['price_max'] = $this->data['price_max'];
            }
		}
		
		if(isset($setting['attribute']) && $setting['attribute']) {
			$this->data['attrs'] = array();
			$this->data['attributes'] = $this->model_module_kuler_filter->getCategoryAttributes($filter['category'], $filter['brand']);

			foreach ($this->request->get as $rq_key => $rq_value)
            {
                if (strpos($rq_key, 'attr_') !== false)
                {
                    $rq_attr = explode('_', $rq_key);

                    $this->data['attrs'][$rq_attr[1]][] = $rq_value;
                    $filter[$rq_key] = $rq_value;
                }
            }
		}

        if ($setting['option'])
        {
            $this->data['options'] = $this->model_module_kuler_filter->getOptionsByCategoryId($filter['category'], $filter['brand']);
            $option_ids = array_keys($this->data['options']);

            $select_option1 = array();
            foreach ($this->request->get as $rq_key => $rq_value)
            {
                if (strpos($rq_key, 'option1_') !== false)
                {
                    $rq_attr = explode('_', $rq_key);

                    if (!in_array($rq_attr[1], $option_ids))
                    {
                        continue;
                    }

                    $select_option1[$rq_attr[1]][] = $rq_value;
                    $filter[$rq_key] = $rq_value;
                }
            }
            $this->data['select_option1'] = $select_option1;

            $select_option2 = array();
            foreach ($this->request->get as $rq_key => $rq_value)
            {
                if (strpos($rq_key, 'option2_') !== false)
                {
                    $rq_attr = explode('_', $rq_key);

                    if (!in_array($rq_attr[1], $option_ids))
                    {
                        continue;
                    }

                    $select_option2[$rq_attr[1]][] = $rq_value;
                    $filter[$rq_key] = $rq_value;
                }
            }
            $this->data['select_option2'] = $select_option2;
        }

		$this->data['link'] = $this->url->link('module/kuler_filter_result');
						
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/kuler_filter.phtml')) {
			$this->template = $this->config->get('config_template') . '/template/module/kuler_filter.phtml';
		} else {
			$this->template = 'default/template/module/kuler_filter.phtml';
		}
		
		$this->data['filter'] = $filter;
		
		$this->render();
	}

    private function translate($texts, $language_id)
    {
        if (is_array($texts))
        {
            $first = current($texts);

            if (is_string($first))
            {
                $texts = empty($texts[$language_id]) ? $first : $texts[$language_id];
            }
            else if (is_array($texts))
            {
                if (!isset($texts[$language_id]))
                {
                    $texts[$language_id] = array();
                }

                foreach ($first as $key => $value)
                {
                    if (empty($texts[$language_id][$key]))
                    {
                        $texts[$language_id][$key] = $value;
                    }
                }
            }
        }

        return $texts;
    }
}
?>