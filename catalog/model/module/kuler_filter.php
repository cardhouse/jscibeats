<?php

class ModelModuleKulerFilter extends Model {
	/**
	 * @todo Get category attributes
	 */
	public function getCategoryAttributes($category = null, $manufacturer_id = null) {
        $whereClause = 'WHERE pa.language_id = ' . $this->config->get('config_language_id');

        $joinCategory = '';
        if ($category)
        {
            $joinCategory = '
                INNER JOIN `'. DB_PREFIX .'product_to_category` c
                    ON (pa.product_id = c.product_id)
            ';

            $whereClause = ' AND c.category_id = ' . intval($category);
        }

        if ($manufacturer_id)
        {
            $joinCategory .= '
                INNER JOIN `'. DB_PREFIX .'product` p
                    ON (pa.product_id = p.product_id AND p.manufacturer_id = '. intval($manufacturer_id) .')
            ';
        }

        $query = "
            SELECT pa.attribute_id, ad.name AS attribute_name, pa.text, COUNT(pa.product_id) AS total_product
            FROM `". DB_PREFIX ."product_attribute` pa
            INNER JOIN `". DB_PREFIX ."attribute_description` ad
                ON (pa.attribute_id = ad.attribute_id AND pa.language_id = ad.language_id)
            ". $joinCategory ."
            ". $whereClause ."
            GROUP BY pa.attribute_id, pa.text
            ORDER BY ad.name, pa.text
        ";

		$result = $this->db->query($query);

        $attributes = array();
		
		foreach ($result->rows as $row) {
			$attribute_id = $row['attribute_id'];

			if(isset($attributes[$attribute_id]) == false) {
				$attributes[$attribute_id] = array(
					'attribute_id' => $row['attribute_id'],
					'name' => $row['attribute_name'],
					'attributes' => array(),
                    'total_product' => 0
				);
			}

			$attributes[$attribute_id]['attributes'][] = array(
				'attribute_id' => $row['attribute_id'],
				'text' => $row['text'],
                'total_product' => $row['total_product']
			);

            $attributes[$attribute_id]['total_product'] += $row['total_product'];
		}

		return $attributes;
	}

    public function getOptionsByCategoryId($category_id = null, $manufacturer_id = null)
    {
        $language_id = $this->config->get('config_language_id');

        $where_clause = 'WHERE 1 = 1';

        $join = '';
        if ($category_id)
        {
            $join = '
                INNER JOIN '. DB_PREFIX .'product_to_category pc
                    ON po.product_id = pc.product_id
            ';

            $where_clause .= ' AND pc.category_id = ' . intval($category_id);
        }

        if ($manufacturer_id)
        {
            $join .= '
                INNER JOIN '. DB_PREFIX .'product p
                    ON (po.product_id = p.product_id AND p.manufacturer_id = '. intval($manufacturer_id) .')
            ';
        }

        $sql = '
            SELECT po.option_id, po.option_value AS value1, _pov.name AS value2, _pov.option_value_id, od.name, COUNT(po.product_id) AS total_product
            FROM `'. DB_PREFIX .'product_option` po
            LEFT JOIN (
                SELECT pov.product_option_id, ovd.name, pov.option_value_id
                FROM `'. DB_PREFIX .'product_option_value` pov
                INNER JOIN `'. DB_PREFIX .'option_value_description` ovd
                    ON (pov.option_value_id = ovd.option_value_id AND ovd.language_id = '. $language_id .')
            ) _pov
                ON (po.product_option_id = _pov.product_option_id)
            INNER JOIN `'. DB_PREFIX .'option_description` od
                ON (po.option_id = od.option_id AND od.language_id = '. $language_id .')
            INNER JOIN `'. DB_PREFIX .'option` o
                ON (po.option_id = o.option_id)
            '. $join .'
            '. $where_clause .'
            GROUP BY po.option_id, option_value_id, value2, value1
            ORDER BY o.sort_order, od.name, value2, value1
        ';

        $query = $this->db->query($sql);

        $options = array();
        foreach ($query->rows as $row)
        {
            $option_id = $row['option_id'];

            if (!isset($options[$option_id]))
            {
                $options[$option_id] = array(
                    'option_id' => $option_id,
                    'name' => $row['name'],
                    'values' => array(),
                    'total_product' => 0
                );
            }

            $options[$option_id]['values'][] = array(
                'option_value_id' => $row['option_value_id'],
                'value1' => $row['value1'],
                'value2' => $row['value2'],
                'total_product' => $row['total_product']
            );

            if (empty($row['option_value_id']))
            {
                $options[$option_id]['type'] = 'single';
            }
            else
            {
                $options[$option_id]['type'] = 'multiple';
            }

            $options[$option_id]['total_product'] += $row['total_product'];
        }

        return $options;
    }
	
	/**
	 * @todo Get category price filter
	 */
	public function getCategoryPrices($category = null) {
		$prices = array();
		
		$query = " SELECT p.price FROM " . DB_PREFIX . "product_to_category pc";
		$query.= " LEFT JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pc.product_id)";
		$query.= " WHERE 1";
		
		if((int)$category) {
			$query .= " AND pc.category_id = " . (int) $category;
		}
		
		$query.= " GROUP BY p.price";
		$query.= " ORDER BY p.price ASC";
		
		$result = $this->db->query($query);
		
		foreach ($result->rows as $row) {
			$row['text'] = number_format($row['price'], 2, '.', ',');
			$prices[] = $row;
		}
		
		if(count($prices) > 5) {
			$end = end($prices);
			
			$prices[] = array(
				'price' => $this->priceRoundUp($end['price'])
			);
			
			$list = array();
			$tmp = array();
			
			foreach($prices as $row) {
				$row['price'] = $this->priceRoundDown($row['price']);
				$row['text'] = number_format($this->priceRoundDown($row['price']), 0, '.', ',');

				if(in_array($row['price'], $tmp) == false) {
					$tmp[] = $row['price'];
					$list[] = $row;
				}
			}
			
			$prices = array();
			
			foreach($list as $k => $v) {
				if(isset($list[$k+1])) {
					$next = $list[$k+1];
					$prices[] = array(
						'price' => $v['price'] . '-' . $next['price'],
						'text' => $v['text'] . '-' . $next['text']
					);
				}
			}
			
			return $prices;
		}
		
		return $prices;
	}
	
	/**
	 * @todo Query product by filter
	 */
	public function getProduct($product_id) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
				
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$customer_group_id . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
		
		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'weight'           => $query->row['weight'],
				'weight_class_id'  => $query->row['weight_class_id'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'length_class_id'  => $query->row['length_class_id'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round($query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			);
		} else {
			return false;
		}
	}
	
	public function getProducts($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
		
		$parent_id = 0;
		$category_id = 0;
		
		if(!empty($data['category'])) {
			$parts = explode('_', $data['category']);

			if(count($parts) == 2) {
				$parent_id = (int) $parts[0];
				$category_id = (int) $parts[1];
			} else {
				$category_id = (int) $data['category'];
			}
		}
		
		$sql  = " SELECT p.product_id, p2c.category_id,";
		$sql .= "   (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating,";
		$sql .= "   (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount";
		$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
		$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
		
		if ($parent_id) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "category_path cp ON (cp.category_id = p2c.category_id)";
		}
		
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		// Category Filter
		if ($category_id) {
			if($parent_id) {
				$sql .= " AND cp.path_id = " . (int) $category_id;
			} else {
				$sql .= " AND p2c.category_id = " . (int) $category_id;
			}
		}
		
		// Manufacturer Filter
		if (!empty($data['brand'])) {
			$sql .= " AND p.manufacturer_id = " . (int)$data['brand'];
		}
		
		// Price Filter
        if ($data['price_min'] !== '')
        {
            $sql .= " AND p.price >= " . intval($data['price_min']);
        }
        if ($data['price_max'] !== '')
        {
            $sql .= " AND p.price <= " . intval($data['price_max']);
        }

		// Attr Filter
		$language = $this->config->get('config_language_id');
		if (!empty($data['attr'])) {
            $attr_wheres = array();

			foreach($data['attr'] as $attribute_id => $attribute_values) {
                foreach ($attribute_values as $attr_value)
                {
                    $attr_value = $this->db->escape($attr_value);

                    $attr_wheres[] = "(s_pa.attribute_id = $attribute_id AND s_pa.text = '$attr_value')";
                }

                $att_where = implode(' OR ', $attr_wheres);
                $sql .= " AND (
                    SELECT COUNT(*)
                    FROM ". DB_PREFIX ."product_attribute s_pa
                    WHERE p2c.product_id = s_pa.product_id AND language_id = $language
                    AND ( $att_where )
                ) = " . count($attr_wheres);
			}
		}

        if (!empty($data['option1s']))
        {
            $option_wheres = array();

            foreach ($data['option1s'] as $option_id => $option_values)
            {
                $option_id = intval($option_id);

                foreach ($option_values as $option_value)
                {
                    $option_value = $this->db->escape($option_value);

                    $option_wheres[] = '(s_po.option_id = ' . $option_id . " AND s_po.option_value = '$option_value')";
                }
            }

            $option_where = implode(' OR ', $option_wheres);

            $sql .= " AND (
                SELECT COUNT(*)
                FROM ". DB_PREFIX ."product_option s_po
                WHERE p2c.product_id = s_po.product_id
                AND ($option_where)
            ) = " . count($option_wheres);
        }

        if (!empty($data['option2s']))
        {
            $option_wheres = array();

            foreach ($data['option2s'] as $option_id => $option_value_ids)
            {
                $option_id = intval($option_id);

                foreach ($option_value_ids as $option_value_id)
                {
                    $option_value_id = $this->db->escape($option_value_id);

                    $option_wheres[] = '(s_pov.option_id = ' . $option_id . ' AND s_pov.option_value_id = ' . $option_value_id . ')';
                }
            }

            $option_where = implode(' OR ', $option_wheres);

            $sql .= " AND (
                SELECT COUNT(*)
                FROM ". DB_PREFIX ."product_option_value s_pov
                WHERE  p2c.product_id = s_pov.product_id
                AND ($option_where)
            ) = " . count($option_wheres);
        }
		
		$sql .= " GROUP BY p.product_id";
		
		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.price',
			'rating',
			'p.sort_order',
			'p.date_added'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
		}
	
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}				

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();

		$query = $this->db->query($sql);
	
		foreach ($query->rows as $result) {
			$row = $this->getProduct($result['product_id']);
			$row['category_id'] = $result['category_id'];
			$product_data[$result['product_id']] = $row;
		}

		return $product_data;
	}
	
	public function getProductsTotal($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
		
		$parent_id = 0;
		$category_id = 0;
		
		if(!empty($data['category'])) {
			$parts = explode('_', $data['category']);
			if(count($parts) == 2) {
				$parent_id = (int) $parts[0];
				$category_id = (int) $parts[1];
			} else {
				$category_id = (int) $data['category'];
			}
		}

		$sql  = " SELECT COUNT(DISTINCT p.product_id) AS total"; 
		$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
		$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
		
		if ($parent_id) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "category_path cp ON (cp.category_id = p2c.category_id)";
		}
		
		// Load attribute data
		if (!empty($data['attr'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_attribute pa ON (pa.product_id = p2c.product_id)";
		}
		
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		// Category Filter
		if ($category_id) {
			if($parent_id) {
				$sql .= " AND cp.path_id = " . (int) $category_id;
			} else {
				$sql .= " AND p2c.category_id = " . (int) $category_id;
			}
		}
		
		// Manufacturer Filter
		if (!empty($data['brand'])) {
			$sql .= " AND p.manufacturer_id = " . (int)$data['brand'];
		}
		
		// Price Filter
        if ($data['price_min'] !== '')
        {
            $sql .= " AND p.price >= " . intval($data['price_min']);
        }
        if ($data['price_max'] !== '')
        {
            $sql .= " AND p.price <= " . intval($data['price_max']);
        }
		
		// Attr Filter
		$language = $this->config->get('config_language_id');
        if (!empty($data['attr'])) {
            $attr_wheres = array();

            foreach($data['attr'] as $attribute_id => $attribute_values) {
                foreach ($attribute_values as $attr_value)
                {
                    $attr_value = $this->db->escape($attr_value);

                    $attr_wheres[] = "(s_pa.attribute_id = $attribute_id AND s_pa.text = '$attr_value')";
                }

                $att_where = implode(' OR ', $attr_wheres);
                $sql .= " AND (
                    SELECT COUNT(*)
                    FROM ". DB_PREFIX ."product_attribute s_pa
                    WHERE p2c.product_id = s_pa.product_id AND language_id = $language
                    AND ( $att_where )
                ) = " . count($attr_wheres);
            }
        }

        if (!empty($data['option1s']))
        {
            $option_wheres = array();

            foreach ($data['option1s'] as $option_id => $option_values)
            {
                $option_id = intval($option_id);

                foreach ($option_values as $option_value)
                {
                    $option_value = $this->db->escape($option_value);

                    $option_wheres[] = '(s_po.option_id = ' . $option_id . " AND s_po.option_value = '$option_value')";
                }
            }

            $option_where = implode(' OR ', $option_wheres);

            $sql .= " AND (
                SELECT COUNT(*)
                FROM ". DB_PREFIX ."product_option s_po
                WHERE p2c.product_id = s_po.product_id
                AND ($option_where)
            ) = " . count($option_wheres);
        }

        if (!empty($data['option2s']))
        {
            $option_wheres = array();

            foreach ($data['option2s'] as $option_id => $option_value_ids)
            {
                $option_id = intval($option_id);

                foreach ($option_value_ids as $option_value_id)
                {
                    $option_value_id = $this->db->escape($option_value_id);

                    $option_wheres[] = '(s_pov.option_id = ' . $option_id . ' AND s_pov.option_value_id = ' . $option_value_id . ')';
                }
            }

            $option_where = implode(' OR ', $option_wheres);

            $sql .= " AND (
                SELECT COUNT(*)
                FROM ". DB_PREFIX ."product_option_value s_pov
                WHERE  p2c.product_id = s_pov.product_id
                AND ($option_where)
            ) = " . count($option_wheres);
        }

		$query = $this->db->query($sql);
		
		return $query->row['total'];		
	}
	
	private function priceRoundDown($number) {
		$length = strlen(floor($number));
		$exp = pow(10, $length - 1);
		return floor($number / $exp) * $exp;
	}

	private function priceRoundUp($number) {
		$length = strlen(ceil($number));
		$exp = pow(10, $length - 2);
		return ceil($number / $exp) * $exp;
	}
}

