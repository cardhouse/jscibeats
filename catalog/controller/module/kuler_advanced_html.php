<?php

/*--------------------------------------------------------------------------/
* @Author		KulerThemes.com http://www.kulerthemes.com
* @Copyright	Copyright (C) 2012 - 2013 KulerThemes.com. All rights reserved.
* @License		KulerThemes.com Proprietary License
/---------------------------------------------------------------------------*/

class ControllerModuleKulerAdvancedHtml extends Controller
{
	protected function index($setting)
    {
        static $module = 0;

        // Prepare setting
        $setting = array_merge(array(
            'widget_type' => 'html',
            'product' => '',
            'image_width' => 80,
            'image_height' => 80,
            'name' => 1,
            'price' => 1,
            'rating' => 1,
            'description' => 1,
            'description_text' => 100,
            'add' => 1,
            'wishlist' => 1,
            'compare' => 1
        ), $setting);

        $setting['title'] = $this->translate($setting['title'], $this->config->get('config_language_id'));

        if (empty($setting['description_text']))
        {
            $setting['description_text'] = 100;
        }

        $this->data['heading_title'] = $this->translate($setting['title'], $this->config->get('config_language_id'));
    	$this->data['show_title'] = isset($setting['showtitle']) ? $setting['showtitle'] : 1;

        if ($setting['widget_type'] == 'html') {
            $message = $this->translate($setting['description'], $this->config->get('config_language_id'));
    	    $message =  html_entity_decode($message, ENT_QUOTES, 'UTF-8');

            $this->data['message'] = $this->helperProcessShortCodes($message);
        } else {
            $products = array();

            if ($setting['product'])
            {
                $setting['description'] = $setting['product_description'];

                $this->load->model('catalog/product');
                $this->load->model('tool/image');

                $selected_products = json_decode(html_entity_decode($setting['product']), true);

                foreach ($selected_products as $selected_product)
                {
                    $products[] = $this->model_catalog_product->getProduct($selected_product['id']);
                }

                $products = $this->prepareProducts($products, $setting);
            }

            $this->data['products'] = $products;
        }

        $this->data['module'] = ++$module;
        $this->data['setting'] = $setting;

        $this->document->addStyle('catalog/view/kulercore/css/kuler_advanced_html.css');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/kuler_advanced_html.phtml')) {
			$this->template = $this->config->get('config_template') . '/template/module/kuler_advanced_html.phtml';
		} else {
			$this->template = 'default/template/module/kuler_advanced_html.phtml';
		}
		
		$this->render();
	}

	private function helperProcessShortCodes($message)
	{
    	if (preg_match_all('#\[(kuler).+?\]#', $message, $matches))
    	{
    		$this->data['modules'] = array();

    		// Get short codes
    		foreach ($matches[0] as $rawCode)
    		{
	    		$shortcodeParts = $this->helperParseShortcode($rawCode);

	    		$extensionCode = $shortcodeParts[0];
	    		$moduleCode = $shortcodeParts[1];

	    		// Parse extra parameters
	    		$params = array(
	    			'show_title' => true
	    		);
	    		if (isset($shortcodeParts[2]))
	    		{
	    			$shortcodeParts[2] = str_replace(' ', '', $shortcodeParts[2]);
	    			list($showTitle, $value) = explode('=', $shortcodeParts[2]);

	    			$params['show_title'] = $value == 'hide' ? false : true;
	    		}

	    		// Load module
	    		$modules = $this->config->get($extensionCode . '_module');

	    		if ($modules)
	    		{
	    			foreach ($modules as $module)
	    			{
                        if (isset($module['shortcode']))
                        {
                            list($dbExtensionCode, $dbModuleCode) = $this->helperParseShortcode($module['shortcode']);

                            if ($extensionCode == $dbExtensionCode && $moduleCode == $dbModuleCode)
                            {
                                $module = array_merge($module, $params);
                                $moduleHtml = $this->getChild('module/' . $extensionCode, $module);

                                if ($moduleHtml)
                                {
                                    // Remove the margin of module
                                    if (preg_match('#<div(.+)?>#', $moduleHtml, $matches) && strpos($matches[0], 'kuler-module') !== false)
                                    {
                                        $matches[1] .= ' style="margin:0;"';
                                        $div = '<div'. $matches[1] .'>';

                                        $moduleHtml = str_replace($matches[0], $div, $moduleHtml);
                                    }

                                    $message = str_replace($rawCode, $moduleHtml, $message);
                                }
                            }
                        }
	    			}
	    		}
    		}
    	}

    	return $message;
	}

    public function loadChromeTemplate(array $setting, array $product)
    {
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/includes/module_chrome_grid.tpl'))
        {
            $button_cart = $this->language->get('button_cart');
            $button_wishlist = $this->language->get('button_wishlist');
            $button_compare = $this->language->get('button_compare');

            include(DIR_TEMPLATE . $this->config->get('config_template') . '/includes/module_chrome_grid.tpl');
        }
    }

    private function prepareProducts(array $products, array $options = array())
    {
        $results = array();

        foreach ($products as $product)
        {
            if ($product['image'])
            {
                $image = $this->model_tool_image->resize($product['image'], $options['image_width'], $options['image_height']);
            }
            else
            {
                $image = false;
            }

            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price'))
            {
                $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
            }
            else
            {
                $price = false;
            }

            if ((float)$product['special'])
            {
                $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
            }
            else
            {
                $special = false;
            }

            if ($this->config->get('config_review_status'))
            {
                $rating = $product['rating'];
            }
            else
            {
                $rating = false;
            }

            $product_categories = $this->model_catalog_product->getCategories($product['product_id']);
            $first_category_id = !empty($product_categories) ? $product_categories[0]['category_id'] : 0;

            $results[] = array(
                'product_id' => $product['product_id'],
                'image'      => $product['image'],
                'thumb'   	 => $image,
                'name'    	 => $product['name'],
                'description' => utf8_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, $options['description_text']) . '..',
                'price'   	 => $price,
                'special' 	 => $special,
                'rating'     => $rating,
                'reviews'    => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                'href'    	 => $this->url->link('product/product', 'path=' . $this->getRecursivePath($first_category_id) .'&product_id=' . $product['product_id'])
            );
        }

        return $results;
    }

    private function getRecursivePath($category_id, $cats = array())
    {
        static $categories;

        if (empty($categories))
        {
            if (!empty($cats))
            {
                $raw_categories = $cats;
            }
            else
            {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

                $raw_categories = $query->rows;
            }

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

	private function helperParseShortcode($shortcode)
	{
		$shortcode = str_replace('&nbsp;', ' ', $shortcode);
		return explode(' ', trim($shortcode, '[]'));
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