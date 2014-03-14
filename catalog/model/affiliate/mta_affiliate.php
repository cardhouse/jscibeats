<?php

if(!class_exists('ModelAffiliateMtaScheme')) require_once(DIR_APPLICATION . 'model/affiliate/mta_scheme.php');

class ModelAffiliateMtaAffiliate extends ModelAffiliateMtaScheme {
	
	protected static $table = "mta_affiliate";
	
	public function findParentByEmail($email, $active_only = false) {
		$res = $this->db->query("select affiliate_id from " . DB_PREFIX . "customer where email='" . $this->db->escape($email) . "' and affiliate_id > 0 order by customer_id asc limit 1");
		if($res->num_rows < 1 || ($active_only && !$this->check_aff_active($res->row['affiliate_id']))) return false;	
		return $res->row['affiliate_id'];
	}
	
	public function addAffiliate($id, $parent_id, $scheme_signup_code = '', $scheme_id = false) {
		if(!mta_check_int($id) || $id < 1) return false;
		$aff = array('id' => $id);
		$parent = $parent_id ? $this->getAffiliate($parent_id) : false;
		if($parent) {
			$aff['level_original'] = ((int) $parent['level_original']) + 1;
			$aff['parent_affiliate_id'] = $parent['id'];
			$aff['parents'] = $parent['parents'];
			array_unshift($aff['parents'], $parent['id']);
			$aff['all_parent_ids'] = implode(',', $aff['parents']);
		} else {
			$aff['level_original'] = 1;
			$aff['parent_affiliate_id'] = 0;
			$aff['parents'] = array();
			$aff['all_parent_ids'] = '';
		}
		if($scheme_id && $this->_checkId($scheme_id)) {
			$this->getSchemeById($scheme_signup_code);
		} else if($scheme_signup_code) {
			$this->getSchemeByCode($scheme_signup_code);
		}
		if(isset($this->scheme_id) && $this->scheme_id && $this->scheme && !isset($this->scheme['error'])) {
			$aff['scheme_id'] = $this->scheme_id;
		} else {
			$aff['scheme_id'] = null;
		}
		
		$query = "insert " . DB_PREFIX . self::$table . " set affiliate_id='" . $aff['id'] . "'";
		if(!is_null($aff['scheme_id'])) $query .= ", mta_scheme_id='" . $aff['scheme_id'] . "'";
		$query .= ", parent_affiliate_id='" . $aff['parent_affiliate_id'] . "', all_parent_ids='" . $this->db->escape($aff['all_parent_ids']) . "', level_original='" . $aff['level_original'] . "'";		
		$this->db->query($query);
		if(!$this->db->countAffected()) return false;
		
		//autoapprove?
		$_scheme_id = !is_null($aff['scheme_id']) ? $aff['scheme_id'] : $this->getDefaultSchemeId();
		if($_scheme_id) {
			$res = $this->db->query("select max(signup_level) as mx from " . DB_PREFIX. "mta_autoapprove where mta_scheme_id='{$_scheme_id}'");
			if($res->num_rows > 0) {
				$_lvl  = $aff['level_original'] > $res->row['mx'] ? $res->row['mx'] : $aff['level_original'];
				$res = $this->db->query("select autoapprove from " . DB_PREFIX. "mta_autoapprove where mta_scheme_id='{$_scheme_id}' and signup_level='{$_lvl}'");
				if($res->row['autoapprove']) $this->db->query("update " . DB_PREFIX. "affiliate set approved='1' where affiliate_id='" . $aff['id'] . "'");				
			}
		}
		return $aff;
	}
	
	public function getAffiliate($id) {
		if(!mta_check_int($id) || $id < 1) return false;
		$res = $this->db->query("select affiliate_id as id, mta_scheme_id as scheme_id, parent_affiliate_id, all_parent_ids, level_original from " . DB_PREFIX . self::$table . " where affiliate_id='" . $this->db->escape($id) . "'");
		if($res->num_rows < 1) return false;
		$out = $res->row;
		$out['parents'] = $out['all_parent_ids'] ? array_map('intval', explode(',', $out['all_parent_ids'])) : array();
		return $out;
	}	
	
	public function getAffiliateIdByEmail($email) {		
		$res = $this->db->query("select affiliate_id from " . DB_PREFIX . "affiliate where email='" . $this->db->escape($email) . "'");
		if($res->num_rows < 1) return false;
		return $res->row['affiliate_id'];
	}
	
	public function getCustomerAffiliateId($customer_id, $active_only = false) {
		if(!mta_check_int($customer_id) || $customer_id < 1) return false;
		$res = $this->db->query("select affiliate_id from " . DB_PREFIX . "customer where customer_id='" . (int) $customer_id . "'");
		if($res->num_rows < 1 || !$res->row['affiliate_id']) return false;
		$aff_id = $res->row['affiliate_id'];
		if($active_only && !$this->check_aff_active($aff_id)) return false;		
		return intval($aff_id);		
	}
	
	public function setCustomerAffiliateId($customer_id, $affiliate_id) {
		if(!mta_check_int($customer_id) || $customer_id < 1 || !mta_check_int($affiliate_id) || $affiliate_id < 1) return false;
		$this->db->query("update " . DB_PREFIX . "customer set affiliate_id='$affiliate_id' where customer_id='$customer_id'");
		return intval($affiliate_id);		
	}	
	
	public function createOrder($order_id, $aff_data) {
		//echo '1 $aff_data: ' . print_r($aff_data, 1);//-tmp
		$mta_order_ids = array();
		$this->db->query('start transaction');
		foreach($aff_data['order'] as $_o) {
			if($_o['commission'] < 0.01) continue;
			$this->db->query("delete from " . DB_PREFIX . "mta_order where affiliate_id='{$_o['affiliate_id']}' and order_id='" . (int) $order_id . "'");
			$this->db->query("insert into " . DB_PREFIX . "mta_order (order_id, affiliate_id, commission, autoadd) values ('" . (int) $order_id . "', '{$_o['affiliate_id']}', '" . mta_float4($_o['commission']) . "', '" . (int) $_o['autoadd'] . "')");
			$mta_order_ids[$_o['affiliate_id']] = $this->db->getLastId();
		}
		//ar($aff_id => array('commission' =>, 'autoadd' =>, 'mta_scheme_id'=>,'num_levels'=>,'level'=>)	
		foreach($aff_data['order_product'] as $product_id => $_ar) {
			$_res = $this->db->query("select order_product_id from " . DB_PREFIX . "order_product where order_id='" . (int) $order_id . "' and product_id='{$product_id}'");
			if($_res->num_rows > 0) {
				$_order_product_id = $_res->row['order_product_id'];
				foreach($_ar as $_aff_id => $_op) {
					if($_op['commission'] < 0.01) continue;
					$this->db->query("delete from " . DB_PREFIX . "mta_order_product where affiliate_id='{$_aff_id}' and order_product_id='{$_order_product_id}'");
					$this->db->query("insert into " . DB_PREFIX . "mta_order_product (mta_order_id, product_id, order_product_id, affiliate_id, commission, mta_scheme_id, num_levels, level, autoadd) values ('" . $mta_order_ids[$_aff_id] . "', '{$product_id}', '{$_order_product_id}', '{$_aff_id}', '" . mta_float4($_op['commission']) . "', '{$_op['mta_scheme_id']}', '{$_op['num_levels']}', '{$_op['level']}', '" . (int) $_op['autoadd'] . "')");
				}
			}
		}	
		$this->_commit();
	}
	
	public function prepareCommissions($aff_id, $aff_info) {
		//echo '2 $aff_info ' . print_r($aff_info, 1);//-tmp
		$aff_data = array('order' => array(), 'order_product' => array());
		$scheme_ids = array('products' => array());
		$aff = $this->getAffiliate($aff_id);
		if(!$aff) return false;
		$all_aff_ids = $aff['parents'];
		array_unshift($all_aff_ids, intval($aff['id']));		
		for($_i = 0; $_i < sizeof($all_aff_ids); $_i++){
			if(!$this->check_aff_active($all_aff_ids[$_i])) unset($all_aff_ids[$_i]);
		}
		$all_aff_ids = array_values($all_aff_ids);
		$num_tiers = sizeof($all_aff_ids);
		if(!$num_tiers) return false;
		$all_aff_index = range(0, ($num_tiers - 1));
		if($this->config->get('mta_ypx_llaff_priority')) $all_aff_index = array_reverse($all_aff_index);		
		
		$mod_types = array('coupon', 'special', 'discount');
		foreach($aff_info['products'] as $product_id => $_pAr) {
			$product_id = (int) $product_id;
			//find correct mod type
			$mod_type = '';
			$mod_type_id = 0;
			foreach($mod_types as $_mtype) {
				if(isset($_pAr['mods'][$_mtype])) {
					$mod_type = $_mtype;
					$mod_type_id = $_pAr['mods'][$_mtype];
					break;
				}
			}
			//look for product / affiliate scheme
			$res = $this->db->query("select mpa.mta_scheme_id, mpa.affiliate_id, ms.max_levels, ms.eternal from " . DB_PREFIX . "mta_product_affiliate mpa, " . DB_PREFIX . "mta_scheme ms where mpa.affiliate_id in (" . implode(',', $all_aff_ids) . ") and mpa.product_id='$product_id' and mpa.price_mod_type='$mod_type' and mpa.price_mod_id='" . (int) $mod_type_id . "' and ms.mta_scheme_id=mpa.mta_scheme_id");
			if($res->num_rows > 0) {
				$_ar = array();
				$_scl = array();
				foreach($res->rows as $_r) {
					$_ar[intval($_r['affiliate_id'])] = intval($_r['mta_scheme_id']);
					$_scl[intval($_r['mta_scheme_id'])] = array($_r['max_levels'], $_r['eternal']);
				}
				for($_i = 0; $_i < $num_tiers; $_i++) {
				//foreach($all_aff_ids as $_aid) {
					$_aid = $all_aff_ids[$all_aff_index[$_i]];
					if(isset($_ar[$_aid])) {					
						//product / affiliate scheme found
						$scheme_ids['products'][$product_id] = $_ar[$_aid];
						$_affids = array_merge(array(), $all_aff_ids);
						$this->_fitAffs($_affids, $_scl[$_ar[$_aid]][0], $_scl[$_ar[$_aid]][1]);
						//if(!$this->config->get('mta_ypx_llaff_priority') || $all_aff_index[$_i] < $_scl[$_ar[$_aid]]) break;
						if(in_array($_aid , $_affids)) break;
					}
				}
				if(isset($scheme_ids['products'][$product_id]))	continue;
			}
			//product / affiliate scheme not found
			//look for product scheme
			$res = $this->db->query("select mta_scheme_id from " . DB_PREFIX . "mta_product where product_id='$product_id' and price_mod_type='$mod_type' and price_mod_id='" . (int) $mod_type_id . "'");
			if($res->num_rows > 0) {
				//product scheme found
				$scheme_ids['products'][$product_id] = intval($res->row['mta_scheme_id']);
				continue;
			}
		}
		//check if all products assigned product-specific commissions
		$_pids = array_map('intval', array_keys($aff_info['products']));
		$_sids = array_keys($scheme_ids['products']);
		$not_assigned = array_diff($_pids, $_sids);

		if(sizeof($not_assigned) > 0) {
			$scheme_id_not_assigned = 0;
			$not_assigned = array_values($not_assigned);
			//not all products assigned product-specific commissions
			//look for affiliate-specific commissions
			$res = $this->db->query("select ma.mta_scheme_id, ma.affiliate_id, ms.max_levels, ms.eternal from " . DB_PREFIX . "mta_affiliate ma, " . DB_PREFIX . "mta_scheme ms where ma.affiliate_id in (" . implode(',', $all_aff_ids) . ") and ms.mta_scheme_id=ma.mta_scheme_id");
			if($res->num_rows > 0) {
				$_ar = array();
				$_scl = array();
				foreach($res->rows as $_r) {
					$_ar[intval($_r['affiliate_id'])] = intval($_r['mta_scheme_id']);
					$_scl[intval($_r['mta_scheme_id'])] = array($_r['max_levels'], $_r['eternal']);
				}
				for($_i = 0; $_i < $num_tiers; $_i++) {
				//foreach($all_aff_ids as $_aid) {
					$_aid = $all_aff_ids[$all_aff_index[$_i]];
					if(isset($_ar[$_aid])) {
						//affiliate-specific scheme found
						$scheme_id_not_assigned = $_ar[$_aid];
						$_affids = array_merge(array(), $all_aff_ids);
						$this->_fitAffs($_affids, $_scl[$_ar[$_aid]][0], $_scl[$_ar[$_aid]][1]);
						//if(!$this->config->get('mta_ypx_llaff_priority') || $all_aff_index[$_i] < $_scl[$_ar[$_aid]]) break;
						if(in_array($_aid, $_affids)) break;
					}
				}				
			}
			//if no affilite-specific scheme - use default
			if(!$scheme_id_not_assigned) $scheme_id_not_assigned = $this->getDefaultSchemeId();
			
			foreach($not_assigned as $_napid) {
				$scheme_ids['products'][$_napid] = $scheme_id_not_assigned;
			}			
		}
		//now we have mta_scheme_id for every product in $scheme_ids				
		foreach($aff_info['products'] as $product_id => $_pAr) {	
			$share = $_pAr['share'];
			$scheme_id = $scheme_ids['products'][intval($product_id)];
			$this->getSchemebyId($scheme_id);
			if(!$this->scheme_id || !$this->scheme || isset($this->scheme['error'])) return false;
			$_price = $this->scheme['before_shipping'] ? $aff_info['price_before_shipping'] : $aff_info['price'];	
			$_pricep = $_price * $_pAr['share'];
			if($this->scheme['commission_type'] == 'fixed') {
				$aff_data['order_product'][$product_id] = $this->_calculateCommissions($all_aff_ids, $_price, $_pAr['quantity']);
			} else {				
				$aff_data['order_product'][$product_id] = $this->_calculateCommissions($all_aff_ids, $_pricep);
			}			
		}
		foreach($all_aff_ids as $_aff_id) {
			$_ar = array();
			foreach($aff_data['order_product'] as $product_id => $_comms) {
				if(isset($_comms[$_aff_id])) {
					if(!isset($_ar['commission'])) {
						$_ar['affiliate_id'] = $_aff_id;
						$_ar['commission'] = 0.00;
						$_ar['autoadd'] = false;
					}
					$_ar['commission'] += $_comms[$_aff_id]['commission'];
					if($_ar['autoadd'] === false && $_comms[$_aff_id]['autoadd']) $_ar['autoadd'] = true;
				}
			}
			if(sizeof($_ar) > 0) $aff_data['order'][] = $_ar;
		}					
		//echo '3 $aff_data ' . print_r($aff_data, 1);//-tmp
		return $aff_data;
	}
	
	private function _fitAffs(&$aff_ids, $max_levels, $eternal) {
		$num_affs = sizeof($aff_ids);
		if($num_affs > $max_levels) {
			//more affs than in scheme - determine which affs
			$num_levels = $max_levels;
			if($eternal) {				
				//if eternal commission - incl. affs form higher levels
				$_end = array_slice($aff_ids, -$eternal);
				$_start = $num_levels > $eternal ? array_slice($aff_ids, 0, ($num_levels - $eternal)) : array();
				$aff_ids = array_values(array_merge($_start, $_end));
			} else {
				$aff_ids = array_slice($aff_ids, 0, $num_levels);
			}
		} else {
			$num_levels = $num_affs;
		}
		return $num_levels;
	}
	
	private function _calculateCommissions($aff_ids, $price = null, $quantity = 1) {
		/*$num_affs = sizeof($aff_ids);
		if($num_affs > $this->scheme['max_levels']) {
			//more affs than in scheme - determine which affs
			$num_levels = $this->scheme['max_levels'];
			if($this->scheme['eternal']) {				
				//if eternal commission - incl. affs form higher levels
				$_end = array_slice($aff_ids, -($this->scheme['eternal']));				
				$_start = $num_levels > $this->scheme['eternal'] ? array_slice($aff_ids, 0, ($num_levels - $this->scheme['eternal'])) : array();
				$aff_ids = array_values(array_merge($_start, $_end));
			} else {
				$aff_ids = array_slice($aff_ids, 0, $num_levels);
			}
		} else {
			$num_levels = $num_affs;
		}*/
		$num_levels = $this->_fitAffs($aff_ids, $this->scheme['max_levels'], $this->scheme['eternal']);		
		$comms = $this->scheme['_commissions'][$num_levels - 1];
		$aff_ids = array_reverse($aff_ids);//get affs in correct order (higher to lower)
		$out = array();
		//echo '4 $comms: ' . print_r($comms, 1);//-tmp
		//echo ' price ' . $price;//-tmp
		if($this->scheme['commission_type'] == 'fixed') {
			$_csum = array_sum($comms);			
			$fcoeff = $_csum > $price ? $price / $_csum : 1;			
		}
		for($i = 0; $i < sizeof($comms); $i++) {			
			$_commission = $this->scheme['commission_type'] == 'fixed' ? $comms[$i] * $quantity * $fcoeff : $price * 0.01 * $comms[$i];
			if($_commission > 0) {
				$out[$aff_ids[$i]] =	array(
					'commission' => $_commission,
					'autoadd' => $this->scheme['_autoadd'][$num_levels - 1][$i],
					'mta_scheme_id' => $this->scheme_id,
					'num_levels' => $num_levels,
					'level' => ($i + 1)
				);
			}
		}
		//echo '5 $out: ' . print_r($out, 1);//-tmp
		return $out;
		//ar($aff_id => array('commission' =>, 'autoadd' =>, 'mta_scheme_id'=>,'num_levels'=>,'level'=>)
	}
	
	public function getOrderCommissions($order_id) {
		$res = $this->db->query("select " . DB_PREFIX . "mta_order.*, CONCAT(" . DB_PREFIX . "affiliate.firstname, ' ', " . DB_PREFIX . "affiliate.lastname) as affiliate_name from " . DB_PREFIX . "mta_order left join " . DB_PREFIX . "affiliate on " . DB_PREFIX . "affiliate.affiliate_id=" . DB_PREFIX . "mta_order.affiliate_id where " . DB_PREFIX . "mta_order.order_id='" . (int) $order_id . "' order by " . DB_PREFIX . "mta_order.mta_order_id asc");
		if($res->num_rows < 1) return array();
		return $res->rows;
	}
	
	public function addMtaTransaction($order_id, $affiliate_id, $description) {
		$res = $this->db->query("select mta_order_id, commission from " . DB_PREFIX . "mta_order where order_id='" . (int) $order_id . "' and affiliate_id='" . (int) $affiliate_id . "' and commission_added='0'");
		if($res->num_rows < 1) return false;
		$commission = $res->row['commission'];
		$res2 = $this->db->query("select " . DB_PREFIX . "mta_order_product.num_levels, " . DB_PREFIX . "mta_order_product.level, " . DB_PREFIX . "order_product.quantity, " . DB_PREFIX . "product_description.name as product_name from " . DB_PREFIX . "mta_order_product left join " . DB_PREFIX . "order_product on " . DB_PREFIX . "order_product.order_product_id=" . DB_PREFIX . "mta_order_product.order_product_id left join " . DB_PREFIX . "product_description on " . DB_PREFIX . "product_description.product_id=" . DB_PREFIX . "mta_order_product.product_id where " . DB_PREFIX . "mta_order_product.mta_order_id='" . (int) $res->row['mta_order_id'] . "'  group by " . DB_PREFIX . "mta_order_product.order_product_id");
		if($res2->num_rows > 0) {
			$_ar = array();
			foreach($res2->rows as $_r) {
				$_str = $_r['product_name'];
				if($_r['quantity'] > 1) $_str .= ' x' . $_r['quantity'];
				$_str .= ' Tier ' . (intval($_r['num_levels']) - intval($_r['level']) + 1);
				$_ar[] = $_str;
			}
			if(sizeof($_ar) > 0) $description .= ': ' . implode(', ', $_ar);
		}
		$this->db->query("update " . DB_PREFIX . "mta_order set commission_added='1' where order_id='" . (int) $order_id . "' and affiliate_id='" . (int) $affiliate_id . "'");
		$this->addTransaction($affiliate_id, $description, $commission, $order_id);
		return true;
	}	

	public function addTransaction($affiliate_id, $description = '', $amount = '', $order_id = 0) {
		$_res = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");	
		
		$affiliate_info = $_res->row;
		
		if ($affiliate_info) { 
			$this->db->query("INSERT INTO " . DB_PREFIX . "affiliate_transaction SET affiliate_id = '" . (int)$affiliate_id . "', order_id = '" . (float)$order_id . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$amount . "', date_added = NOW()");
		
			$this->language->load('affiliate/mta');
			
			$_res = $this->db->query("SELECT SUM(amount) AS total FROM " . DB_PREFIX ."affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "'");
			$_trans_total = $_res->row['total'];

			$message  = sprintf($this->language->get('text_transaction_received'), $this->currency->format($amount, $this->config->get('config_currency'))) . "\n\n";
			$message .= sprintf($this->language->get('text_transaction_total'), $this->currency->format($_trans_total, $this->config->get('config_currency')));
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setTo($affiliate_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(sprintf($this->language->get('text_transaction_subject'), $this->config->get('config_name')));
			$mail->setText($message);
			$mail->send();
		}
	}	
	
	public function check_aff_active($aff_id) {
		$res = $this->db->query("select `status`, approved from " . DB_PREFIX . "affiliate where affiliate_id='$aff_id'");
		return ($res->num_rows < 1 || !isset($res->row['status']) || !isset($res->row['approved']) || !$res->row['status'] || !$res->row['approved'] ? false : true);
	}
	
	public function getSubAffiliates($affiliate_ids) {
		if(!is_array($affiliate_ids)) $affiliate_ids = array($affiliate_ids);
		$res = $this->db->query("select count(*) as c from " . DB_PREFIX . "mta_affiliate where parent_affiliate_id in (" . implode(',', $affiliate_ids) . ")");
		if(!$res->row['c']) return false;
		$out = array(intval($res->row['c']));
		$res = $this->db->query("select affiliate_id from " . DB_PREFIX . "mta_affiliate where parent_affiliate_id in (" . implode(',', $affiliate_ids) . ")");
		if($res->num_rows < 1) return $out;
		$_subs = array();
		foreach($res->rows as $_r) {
			$_subs[] = $_r['affiliate_id'];
		}
		$_sub = $this->getSubAffiliates($_subs);
		if(!$_sub) return $out;
		foreach($_sub as $_i => $num) {
			if(!isset($out[$_i + 1])) $out[$_i + 1] = 0;
			$out[$_i + 1] += $num;
		}
		return $out;
	}
	
}