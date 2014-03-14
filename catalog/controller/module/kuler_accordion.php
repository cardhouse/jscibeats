<?php

/*--------------------------------------------------------------------------/
* @Author		KulerThemes.com http://www.kulerthemes.com
* @Copyright	Copyright (C) 2012 - 2013 KulerThemes.com. All rights reserved.
* @License		KulerThemes.com Proprietary License
/---------------------------------------------------------------------------*/

class ControllerModuleKulerAccordion extends Controller {
	protected function index($setting) {
		static $module = 0;

        // Prepare setting
        if (empty($setting['description_text']))
        {
            $setting['description_text'] = 100;
        }

        $setting['description_text'] = intval($setting['description_text']);

		$this->data['button_wishlist'] = $this->language->get('button_wishlist');
		$this->data['button_compare'] = $this->language->get('button_compare');
		$this->data['button_cart'] = $this->language->get('button_cart');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		
		$this->document->addStyle('catalog/view/kulercore/css/kuler_accordion.css');
		$this->document->addScript('catalog/view/kulercore/js/kuler_accordion.js');

		$this->data['module_title'] = $this->translate($setting['module_title'], $this->config->get('config_language_id'));
        $this->data['show_title'] = $setting['show_title'];
		$this->data['setting'] = $setting;
		$this->data['image_width'] = $setting['image_width'];
		$this->data['image_height'] = $setting['image_height'];
		$this->data['products'] = array();
		
		// Query products for category module
		if (isset($setting['type']) && isset($setting['category_id']) && $setting['type'] == 'category' && $category_id = $setting['category_id']) {
			$category_id = $setting['category_id'];
			$filter = '';
			$sort = 'p.sort_order';
			$order = 'ASC';
			$limit = isset($setting['limit']) ?  $setting['limit'] : 5;
			$data = array(
				'filter_category_id' => $category_id,
				'filter_filter'      => $filter, 
				'sort'               => $sort,
				'order'              => $order,
				'start'              => 0,
				'limit'              => $limit
			);
			
			$results = $this->model_catalog_product->getProducts($data);
			  
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height']);
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
					'image'       => $result['image'],
					'name'        => $result['name'],
					'description' => $result['description'],
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $result['rating'],
					'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
					'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'])
				);
			}
		} else if(isset($setting['type']) && isset($setting['products']) && $setting['type'] == 'product') {
			// Query products for custom product module
			
			$products = explode(',', $setting['products']);
			$products = array_slice($products, 0, (int)$setting['limit']);
			
			foreach ($products as $product_id) {
				$result = $this->model_catalog_product->getProduct($product_id);

				if ($result) {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height']);
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
						'image'       => $result['image'],
						'name'        => $result['name'],
						'description' => $result['description'],
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
						'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'])
					);
				}
			}
		} else if(isset($setting['type']) && $setting['type'] == 'bestseller') {
			$limit = isset($setting['limit']) ?  $setting['limit'] : 5;
			$results = $this->model_catalog_product->getBestSellerProducts($limit);
		
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height']);
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

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				$this->data['products'][] = array(
					'product_id' => $result['product_id'],
					'thumb'   	 => $image,
					'image'      => $result['image'],
					'name'    	 => $result['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $setting['description_text']) . '..',
					'price'   	 => $price,
					'special' 	 => $special,
					'rating'     => $rating,
					'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
					'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
				);
			}
		} else if(isset($setting['type']) && $setting['type'] == 'latest') {
			$data = array(
				'sort'  => 'p.date_added',
				'order' => 'DESC',
				'start' => 0,
				'limit' => isset($setting['limit']) ?  $setting['limit'] : 5
			);

			$results = $this->model_catalog_product->getProducts($data);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height']);
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

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				$this->data['products'][] = array(
					'product_id' => $result['product_id'],
					'thumb'   	 => $image,
					'image'      => $result['image'],
					'name'    	 => $result['name'],
					'description' => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
					'price'   	 => $price,
					'special' 	 => $special,
					'rating'     => $rating,
					'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
					'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
				);
			}

		} else if(isset($setting['type']) && $setting['type'] == 'special') {
			$data = array(
				'sort'  => 'pd.name',
				'order' => 'ASC',
				'start' => 0,
				'limit' => isset($setting['limit']) ?  $setting['limit'] : 5
			);

			$results = $this->model_catalog_product->getProductSpecials($data);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height']);
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

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				$this->data['products'][] = array(
					'product_id' => $result['product_id'],
					'thumb'   	 => $image,
					'image'      => $result['image'],
					'name'    	 => $result['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $setting['description_text']) . '..',
					'price'   	 => $price,
					'special' 	 => $special,
					'rating'     => $rating,
					'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
					'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}
        } else {
			return false;
		}

        foreach ($this->data['products'] as $product_index => $product)
        {
            $product_categories = $this->model_catalog_product->getCategories($product['product_id']);
            $first_category_id = is_array($product_categories) ? $product_categories[0]['category_id'] : 0;

            $this->data['products'][$product_index]['href'] = $this->url->link('product/product', 'path=' . $this->getRecursivePath($first_category_id) . '&product_id=' . $product['product_id']);

            // Cut words
            $this->data['products'][$product_index]['short_name'] = $this->data['products'][$product_index]['name'];

            if (!empty($setting['title_limit']) && intval($setting['title_limit']) > 0)
            {
                $product_name_words = preg_split('#(\s+|,)#', $this->data['products'][$product_index]['name']);
                $product_name_words = array_slice($product_name_words, 0, intval($setting['title_limit']));
                $this->data['products'][$product_index]['short_name'] = implode(' ', $product_name_words);
            }
        }

		$this->data['module'] = $module++;
        
        // Check for chrome template exists at current theme;
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/includes/module_chrome_list.tpl')) {
			$this->data['template'] = DIR_TEMPLATE . $this->config->get('config_template') . '/includes/module_chrome_list.tpl';
		} else {
            $this->data['template'] = null;
		}
						
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/kuler_accordion.phtml')) {
			$this->template = $this->config->get('config_template') . '/template/module/kuler_accordion.phtml';
		} else {
			$this->template = 'default/template/module/kuler_accordion.phtml';
		}
		
		$this->render();
	}

    protected function getRecursivePath($category_id)
    {
        static $categories;

        if (empty($categories))
        {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");
            $raw_categories = $query->rows;

            $categories = array();
            foreach ($raw_categories as $raw_category)
            {
                $categories[$raw_category['category_id']] = $raw_category['parent_id'];
            }
        }

        if (!isset($categories[$category_id]))
        {
            return '';
        }

        $path = $category_id;
        $parent_id = $categories[$category_id];

        while (true)
        {
            if (!$parent_id)
            {
                break;
            }

            $path = $parent_id . '_' . $path;
            $parent_id = $categories[$parent_id];
        }

        return $path;
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