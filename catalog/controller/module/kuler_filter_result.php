<?php 
class ControllerModuleKulerFilterResult extends Controller {  
	public function index() { 
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('module/kuler_filter');
		$this->load->model('tool/image');

        // Filter data
		$filter = array(
			'category'	=> isset($this->request->get['category']) ? $this->request->get['category'] : '',
			'brand'	=> isset($this->request->get['brand'])? (int) $this->request->get['brand'] : '',
			'price_min'	=> isset($this->request->get['price_min']) ? $this->request->get['price_min'] : '',
            'price_max' => isset($this->request->get['price_max']) ? $this->request->get['price_max'] : '',
            'currency_code' => $this->request->get['currency_code'],
		);

        $raw_filter = $filter;

        foreach ($this->request->get as $rq_key => $rq_value)
        {
            // Prepare attributes
            if (strpos($rq_key, 'attr_') !== false)
            {
                $raw_filter[$rq_key] = $rq_value;

                $rq_attr = explode('_', $rq_key);
                $filter['attr'][$rq_attr[1]][] = $rq_value;
            }
            else if (strpos($rq_key, 'option1_') !== false)
            {
                $raw_filter[$rq_key] = $rq_value;

                $rq_option = explode('_', $rq_key);
                $filter['option1s'][$rq_option[1]][] = $rq_value;
            }
            else if (strpos($rq_key, 'option2_') !== false)
            {
                $raw_filter[$rq_key] = $rq_value;

                $rq_option = explode('_', $rq_key);
                $filter['option2s'][$rq_option[1]][] = $rq_value;
            }
        }

        // Convert money
        $this->load->library('currency');
        $currency_code = $this->request->get['currency_code'];
        $default_currency_code = $this->config->get('config_currency');

        if ($filter['price_min'] !== '')
        {
            $filter['price_min'] = $this->currency->convert($filter['price_min'], $currency_code, $default_currency_code);
        }

        if ($filter['price_max'] !== '')
        {
            $filter['price_max'] = $this->currency->convert($filter['price_max'], $currency_code, $default_currency_code);
        }

		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
       		'separator' => false
   		);
		$this->data['breadcrumbs'][] = array(
			'text'      => 'Kuler Filter',
			'href'      => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter)),
			'separator' => $this->language->get('text_separator')
		);

		$this->getLanguages();
        $this->document->setTitle($this->language->get('heading_title_result'));

        // Set default data
		$this->data['thumb'] = '';
		$this->data['description'] = '';
		$this->data['categories'] = array();
		
		// Category data
		if($filter['category']) {
			$parts = explode('_', (string)$filter['category']);
			$category_id = (int) array_pop($parts);
			$category = $this->model_catalog_category->getCategory($category_id);
			
			if($category) {
				$this->document->setTitle($category['name']);
				$this->document->setDescription($category['meta_description']);
				$this->document->setKeywords($category['meta_keyword']);
				$this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');

				$this->data['heading_title'] = $category['name'];

				if ($category['image']) {
					$this->data['thumb'] = $this->model_tool_image->resize($category['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
				} else {
					$this->data['thumb'] = '';
				}
				
				$this->data['description'] = html_entity_decode($category['description'], ENT_QUOTES, 'UTF-8');
				$this->data['compare'] = $this->url->link('product/compare');

				$this->data['categories'] = array();

				$results = $this->model_catalog_category->getCategories($category_id);

				foreach ($results as $result) {
					$data = array(
						'filter_category_id'  => $category_id,
						'filter_sub_category' => true
					);

					$product_total = $this->model_catalog_product->getTotalProducts($data);

					$this->data['categories'][] = array(
						'name'  => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $product_total . ')' : ''),
						'href'  => $this->url->link('product/category', 'path=' . $category_id . '_' . $result['category_id'])
					);
				}				
			}
		}
	
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else { 
			$page = 1;
		}	
							
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_catalog_limit');
		}
		
		// Query products data
		$this->data['products'] = array();

		$data = array(
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);
		
		$data = $filter + $data;
		
		$product_total = $this->model_module_kuler_filter->getProductsTotal($data); 

		$results = $this->model_module_kuler_filter->getProducts($data);

		foreach ($results as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
			} else {
				$image = false;
			}

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
			} else {
				$price = false;
			}

			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
			} else {
				$special = false;
			}	

			if ($this->config->get('config_tax')) {
				$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
			} else {
				$tax = false;
			}				

			if ($this->config->get('config_review_status')) {
				$rating = (int)$result['rating'];
			} else {
				$rating = false;
			}

			$this->data['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'name'        => $result['name'],
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'rating'      => $result['rating'],
				'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'        => $this->url->link('product/product', 'path=' . ($raw_filter['category'] ? $raw_filter['category'] : $result['category_id']) . '&product_id=' . $result['product_id'])
			);
		}

		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$this->data['sorts'] = array();

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_default'),
			'value' => 'p.sort_order-ASC',
			'href'  => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . '&sort=p.sort_order&order=ASC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . '&sort=pd.name&order=ASC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . '&sort=pd.name&order=DESC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_price_asc'),
			'value' => 'p.price-ASC',
			'href'  => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . '&sort=p.price&order=ASC' . $url)
		); 

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_price_desc'),
			'value' => 'p.price-DESC',
			'href'  => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . '&sort=p.price&order=DESC' . $url)
		); 

		if ($this->config->get('config_review_status')) {
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . '&sort=rating&order=DESC' . $url)
			); 

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . '&sort=rating&order=ASC' . $url)
			);
		}

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_model_asc'),
			'value' => 'p.model-ASC',
			'href'  => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . '&sort=p.model&order=ASC' . $url)
		);

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_model_desc'),
			'value' => 'p.model-DESC',
			'href'  => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . '&sort=p.model&order=DESC' . $url)
		);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$this->data['limits'] = array();

		$limits = array_unique(array($this->config->get('config_catalog_limit'), 25, 50, 75, 100));

		sort($limits);

		foreach($limits as $limits){
			$this->data['limits'][] = array(
				'text'  => $limits,
				'value' => $limits,
				'href'  => $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . $url . '&limit=' . $limits)
			);
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}	

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('module/kuler_filter_result', $this->buildQuery($raw_filter) . $url . '&page={page}');

		$this->data['pagination'] = $pagination->render();
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->data['limit'] = $limit;
		$this->data['continue'] = $this->url->link('common/home');

        $this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/category.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/product/category.tpl';
		} else {
			$this->template = 'default/template/product/category.tpl';
		}

		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

		$this->response->setOutput($this->render());										
  	}
	
	private function getLanguages() {
		$this->language->load('product/category');
        $this->language->load('module/kuler_filter');

        $this->data['heading_title'] = $this->language->get('heading_title_result');
		
		$this->data['text_refine'] = $this->language->get('text_refine');
		$this->data['text_empty'] = $this->language->get('text_empty');			
		$this->data['text_quantity'] = $this->language->get('text_quantity');
		$this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
		$this->data['text_model'] = $this->language->get('text_model');
		$this->data['text_price'] = $this->language->get('text_price');
		$this->data['text_tax'] = $this->language->get('text_tax');
		$this->data['text_points'] = $this->language->get('text_points');
		$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
		$this->data['text_display'] = $this->language->get('text_display');
		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_grid'] = $this->language->get('text_grid');
		$this->data['text_sort'] = $this->language->get('text_sort');
		$this->data['text_limit'] = $this->language->get('text_limit');

		$this->data['button_cart'] = $this->language->get('button_cart');
		$this->data['button_wishlist'] = $this->language->get('button_wishlist');
		$this->data['button_compare'] = $this->language->get('button_compare');
		$this->data['button_continue'] = $this->language->get('button_continue');
	}
	
	private function buildQuery($data, $remove = null) {
		if(!is_array($data)) {
			return $data;
		}
		
		$list = array();
		
		foreach($data as $key => $value) {
			if(is_array($value)) {
				foreach($value as $k => $v) {
					$list[] = "{$key}" . "[{$k}]={$v}";
				}
			} else {
				$value = trim($value);
				if($value !== '' && $key != $remove) {
					$list[] = "{$key}={$value}";
				}
			}
		}

		if($list) {
			return implode('&' , $list);
		} else {
			return '';
		}
	}
	
}
?>