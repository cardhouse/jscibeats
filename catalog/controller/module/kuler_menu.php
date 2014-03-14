<?php  
class ControllerModuleKulerMenu extends Controller
{

	public function index()
    {
        if (!$this->config->get('kuler_menu_status'))
        {
            return false;
        }

        $__ = $this->language->load('module/kuler_menu');
        $this->data['__'] = $__;

        $setting = $this->config->get('kuler_menu');
        $items = array();

        if (is_array($setting))
        {
            $this->load->model('module/kuler_menu');
            $this->load->model('catalog/product');
            $this->load->model('tool/image');

            /* @var ModelModuleKulerMenu $model */
            $model = $this->model_module_kuler_menu;

            /* @var ModelCatalogProduct $product_model */
            $product_model = $this->model_catalog_product;

            // Use category_id for index of array
            $raw_categories = $this->sort($model->getCategories()); // todo: re-sort
            $categories = array();

            if (is_array($raw_categories))
            {
                foreach ($raw_categories as $raw_category)
                {
                    $categories[$raw_category['category_id']] = $raw_category;
                }
            }

            // Sort menu items by sort order
            $setting = $this->sort($setting);

            foreach ($setting as $setting_item)
            {
                if (!$setting_item['status'])
                {
                    continue;
                }

                if ($setting_item['type'] == 'category')
                {
                    $item1 = array(
                        'title' => $this->translate($setting_item['title'], $this->config->get('config_language_id')),
                        'type' => 'category',
                        'image' => $setting_item['image'],
                        'image_position' => $setting_item['image_position'],
                        'show_sub_categories' => !isset($setting_item['show_sub_categories']) || $setting_item['show_sub_categories'] ? true : false,
                        'description' => $setting_item['description'],
                        'category_description_position' => isset($setting_item['category_description_position']) ? $setting_item['category_description_position'] : 'top',
                        'category_image_width' => !empty($setting_item['category_image_width']) ? intval($setting_item['category_image_width']) : 80,
                        'category_image_height' => !empty($setting_item['category_image_height']) ? intval($setting_item['category_image_height']) : 80,
                        'description_text' => !empty($setting_item['description_text']) ? intval($setting_item['description_text']) : 100,
                        'max_subcategory_items' => $setting_item['max_subcategory_items'],
                        'children' => array()
                    );

                    if (isset($setting_item['categories']) && is_array($setting_item['categories']))
                    {
                        // Prepare children
                        foreach ($setting_item['categories'] as $setting_category)
                        {
                            if (!isset($categories[$setting_category['category_id']]))
                            {
                                continue;
                            }

                            $category = $categories[$setting_category['category_id']];

                            $item2 = array(
                                'category_id' => $category['category_id'],
                                'title' => $category['name'],
                                'href' => $this->url->link('product/category', 'path=' . $this->getRecursivePath($category['category_id'], $categories)),
                                'thumb' => $this->getThumb($category['image'], $item1['category_image_width'], $item1['category_image_height']),
                                'description' => utf8_substr(strip_tags(html_entity_decode($category['description'], ENT_QUOTES, 'UTF-8')), 0, $item1['description_text']) . '..',
                                'children' => array()
                            );

                            // Prepare grand children
                            if ($item1['show_sub_categories'])
                            {
                                foreach ($categories as $child_category)
                                {
                                    if ($child_category['parent_id'] == $category['category_id'])
                                    {
                                        if ($item1['max_subcategory_items'] && count($item2['children']) >= $item1['max_subcategory_items'])
                                        {
                                            break;
                                        }

                                        $item2['children'][] = array(
                                            'category_id' => $child_category['category_id'],
                                            'title' => $child_category['name'],
                                            'href' => $this->url->link('product/category', 'path=' . $this->getRecursivePath($child_category['category_id'], $categories)),
                                            'description' => utf8_substr(strip_tags(html_entity_decode($child_category['description'], ENT_QUOTES, 'UTF-8')), 0, $item1['description_text']) . '..',
                                            'children' => array()
                                        );
                                    }
                                }
                            }

                            $item1['children'][] = $item2;
                        }
                    }
                }
                else if ($setting_item['type'] == 'product')
                {
                    $item1 = array(
                    'title' => $this->translate($setting_item['title'], $this->config->get('config_language_id')),
                        'type' => 'product',
                        'image_width' => intval($setting_item['image_width']),
                        'image_height' => intval($setting_item['image_height']),
                        'name' => $setting_item['name'],
                        'price' => $setting_item['price'],
                        'rating' => $setting_item['rating'],
                        'description' => $setting_item['description'],
                        'description_text' => 100,
                        'add' => $setting_item['add'],
                        'wishlist' => $setting_item['wishlist'],
                        'compare' => $setting_item['compare'],
                        'children' => array()
                    );

                    if (isset($setting_item['products']) && is_array($setting_item['products']))
                    {
                        foreach ($setting_item['products'] as $setting_product)
                        {
                            $product = $product_model->getProduct($setting_product['product_id']);

                            if ($product)
                            {
                                $product['description_text'] = $item1['description_text'];
                                $product['width'] = $item1['image_width'];
                                $product['height'] = $item1['image_height'];

                                $item2 = $this->prepareProduct($product);

                                $item1['children'][] = $item2;
                            }
                        }
                    }
                }
                else if ($setting_item['type'] == 'custom')
                {
                    $item1 = array(
                        'title' => $this->translate($setting_item['title'], $this->config->get('config_language_id')),
                        'type' => 'custom',
                        'href' => $setting_item['link'],
                        'new_tab' => $setting_item['new_tab'],
                        'children' => array()
                    );

                    if (isset($setting_item['links']) && is_array($setting_item['links']))
                    {
                        foreach ($setting_item['links'] as $setting_link)
                        {
                            $item2 = array(
                                'title' => $this->translate($setting_link['titles'], $this->config->get('config_language_id')),
                                'new_tab' => $setting_link['new_tab'],
                                'href' => $setting_link['link']
                            );

                            $item1['children'][] = $item2;
                        }
                    }
                }
                else if ($setting_item['type'] == 'html')
                {
                    $item1 = array(
                        'title' => $this->translate($setting_item['title'], $this->config->get('config_language_id')),
                        'type' => 'html',
                        'html' => html_entity_decode($this->translate($setting_item['htmls'], $this->config->get('config_language_id')), ENT_QUOTES, 'UTF-8'),
                        'children' => array(1)
                    );
                }

                $items[] = $item1;
            }
        }

        $this->data['items'] = $items;

        $this->data['route'] = '';
        if (isset($this->request->get['_route_']))
        {
            $this->data['route'] = $this->request->get['_route_'];
        }
        if (isset($this->request->get['route']))
        {
            $this->data['route'] = $this->request->get['route'];
        }

        $this->data['paths'] = isset($this->request->get['path']) ? explode('_', $this->request->get['path']) : array();
        $this->data['product_id'] = isset($this->request->get['product_id']) ? $this->request->get['product_id'] : 0;

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/kuler_menu.phtml'))
        {
			$this->template = $this->config->get('config_template') . '/template/module/kuler_menu.phtml';
		}
        else
        {
			$this->template = 'default/template/module/kuler_menu.phtml';
		}

        $this->render();
	}

    private function prepareProduct(array $product)
    {
        if ($product['image']) {
            $image = $this->model_tool_image->resize($product['image'], $product['width'], $product['height']);
        } else {
            $image = false;
        }

        if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
            $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
        } else {
            $price = false;
        }

        if ((float)$product['special']) {
            $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
        } else {
            $special = false;
        }

        if ($this->config->get('config_review_status')) {
            $rating = $product['rating'];
        } else {
            $rating = false;
        }

        $product_categories = $this->model_catalog_product->getCategories($product['product_id']);
        $first_category_id = !empty($product_categories) ? $product_categories[0]['category_id'] : 0;

        $result = array(
            'product_id' => $product['product_id'],
            'image'      => $product['image'],
            'thumb'   	 => $image,
            'name'    	 => $product['name'],
            'description' => utf8_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, $product['description_text']) . '..',
            'price'   	 => $price,
            'special' 	 => $special,
            'rating'     => $rating,
            'reviews'    => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
            'href'    	 => $this->url->link('product/product', 'path=' . $this->getRecursivePath($first_category_id) .'&product_id=' . $product['product_id'])
        );

        return $result;
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

    private function getThumb($path, $with, $height)
    {
        $this->load->model('tool/image');

        if ($path && file_exists(DIR_IMAGE . $path))
        {
            return $this->model_tool_image->resize($path, $with, $height);
        }
        else
        {
            return $this->model_tool_image->resize('no_image.jpg', $with, $height);
        }
    }

    private function sort(array $items)
    {
        $sortOrder = array();
        foreach ($items as $key => $value)
        {
            $sortOrder[$key] = $value['sort_order'];
        }
        array_multisort($sortOrder, SORT_ASC, $items);

        return $items;
    }
}
?>