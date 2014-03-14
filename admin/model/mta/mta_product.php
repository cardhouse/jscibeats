<?php

if(!class_exists('ModelMtaMtaScheme')) require_once(DIR_APPLICATION . 'model/mta/mta_scheme.php');

class ModelMtaMtaProduct extends ModelMtaMtaScheme {
	
	public function moveFromScheme($from, $to, $mods=array("''"), $parent=false) {
		if(!mta_check_int($from) || !mta_check_int($to)) return false;
		if(($from && !$this->_checkId($from)) || ($to && !$this->_checkId($to))) return false;
		$tbl = DB_PREFIX . 'mta_product' . ($parent === true ? '' : '_affiliate');
		$query = "update $tbl set mta_scheme_id=" . ($to ? "'$to'" : "null") . " where mta_scheme_id" . ($from ? "='$from'" : " is null") . " and price_mod_type in (" . implode(',', $mods) . ")";
		$this->db->query($query);
		return $this->db->countAffected();
	}
	
	public function countUsers($scheme_id, $tbl = 'product') {		
		return $this->_countUsage($scheme_id, 'mta_scheme_id', $tbl);
	}
	
	public function countProductUsage($product_id, $tbl = 'product') {
		return $this->_countUsage($product_id, 'product_id', $tbl);
	}	
	
	public function getProductScheme($product_id) {				
		$res = $this->db->query("select mta_scheme_id from " . DB_PREFIX . "mta_product where product_id='" . $this->db->escape($product_id) . "' and price_mod_type=''");
		if($res->num_rows < 1) return false;
		$this->getSchemeById($res->row['mta_scheme_id']);
		if(!$this->scheme_id || !$this->scheme || isset($this->scheme['error'])) return false;
		return $this->scheme;
	}
	
	public function getProductName($product_id) {
		$res = $this->db->query("select `name` from " . DB_PREFIX . "product_description where product_id='$product_id'");
		if($res->rows < 1) return '';
		return $res->row['name'];
	}
	
	public function getAllProductSubSchemes($product_id) {
		$out = array();
		$res = $this->db->query("select price_mod_type as mtype, price_mod_id as mid, mta_scheme_id as scheme_id from " . DB_PREFIX . "mta_product where product_id='" . $this->db->escape($product_id) . "' and price_mod_type!='' order by price_mod_type");
		if($res->num_rows > 0) {
			foreach($res->rows as $r) {				
				$_k = strval($r['mid']);
				if(!isset($out[$r['mtype']])) $out[$r['mtype']] = array();
				$out[$r['mtype']][$_k] = $r['scheme_id'];				
			}
		}
		return $out;
	}
	
	public function unsetSubSchemes($product_id, $ids, $mod_type, $tbl = 'product') {
		if(!is_array($ids)) $ids = array($ids);		
		$this->db->query("delete from " . DB_PREFIX . "mta_{$tbl} where product_id='" . (int)$product_id . "' and price_mod_type='" . $this->db->escape($mod_type) . "' and price_mod_id in (" . implode(',', $ids) . ")");		
	}
	
	public function unsetProductScheme($product_id) {
		$this->db->query("delete from " . DB_PREFIX . "mta_product where product_id='" . (int)$product_id . "' and price_mod_type=''");
	}
	
	public function setProductScheme($product_id, $scheme_id) {
		$this->unsetProductScheme($product_id);
		$this->db->query("insert " . DB_PREFIX . "mta_product set product_id='" . (int)$product_id . "', mta_scheme_id='" . (int)$scheme_id . "'");
	}

	public function setSubScheme($product_id, $id, $scheme_id, $mod_type) {		
		$this->unsetSubSchemes($product_id, $id, $mod_type);
		$this->db->query("insert " . DB_PREFIX . "mta_product set product_id='" . (int)$product_id . "', mta_scheme_id='" . (int)$scheme_id . "', price_mod_type='" . $this->db->escape($mod_type) . "', price_mod_id='" . (int)$id . "'");	
	}
	
	public function setAllProductScheme($entity_type, $entity_id, $scheme_id) {
		if($entity_type != 'coupon') {
			$_type = substr($entity_type, 0, 1);
			if($_type === 'm') {
				$res = $this->db->query("select distinct(product_id) as pid from " . DB_PREFIX . "product where manufacturer_id = '" . (int)$entity_id . "'");
			} else if($_type === 'c') {
				$res = $this->db->query("select distinct(product_id) as pid from " . DB_PREFIX . "product_to_category where category_id = '" . (int)$entity_id . "'");
			}
			if($res->num_rows > 0) {
				$product_ids = array();
				foreach($res->rows as $r) {
					$product_ids[] = $r['pid'];
				}
				$this->_set_for_entity($product_ids, $entity_type, $scheme_id);
			}
		} else {
			$this->db->query("delete from " . DB_PREFIX . "mta_product where price_mod_type='coupon' and price_mod_id='" . (int)$entity_id . "'");
			if($scheme_id > 0) $this->db->query("insert into " . DB_PREFIX . "mta_product (product_id, price_mod_type, price_mod_id, mta_scheme_id) select product_id, 'coupon', '" . (int)$entity_id . "', '" . (int)$scheme_id . "' from " . DB_PREFIX . "product");
		}	
	}
	
	public function getDefaultProductSchemes($entity_types, $entity_id) {
		if(!is_array($entity_types)) {
			$entity_types = array("'" . $this->db->escape($entity_types) . "'");
		} else {
			$_tmp = array();
			foreach($entity_types as $_et) {
				$_tmp[] = "'" . $this->db->escape($_et) . "'";
			}
			$entity_types = $_tmp;
		}
		$res = $this->db->query("select entity_type as `type`, mta_scheme_id as scheme_id from " . DB_PREFIX . "mta_product_default_scheme where entity_id = '" . (int)$entity_id . "' and entity_type in (" . implode(',', $entity_types) . ")");
		if($res->num_rows < 1) return false;
		return $res->rows;
	}
	
	public function setDefaultProductScheme($entity_type, $entity_id, $scheme_id) {
		if($scheme_id > 0) {
			$this->db->query("insert into " . DB_PREFIX . "mta_product_default_scheme (entity_type, entity_id, mta_scheme_id) values ('" . $this->db->escape($entity_type) . "', '" . (int)$entity_id . "', '" . (int)$scheme_id . "') on duplicate key update mta_scheme_id='" . (int)$scheme_id . "'");	
		} else {
			$this->db->query("delete from " . DB_PREFIX . "mta_product_default_scheme where entity_type = '" . $this->db->escape($entity_type) . "' and entity_id = '" . (int)$entity_id . "'");
		}
	}	
	
	public function	setDefaultsForNew($product_id) {
		$product_id = (int)$product_id;
		$this->db->query("insert into " . DB_PREFIX . "mta_product (product_id, price_mod_type, price_mod_id, mta_scheme_id) select '$product_id', 'coupon', mpds.entity_id, mpds.mta_scheme_id from " . DB_PREFIX . "mta_product_default_scheme mpds where mpds.entity_type='coupon' on duplicate key update mta_scheme_id=mpds.mta_scheme_id");
		$res = $this->db->query("select manufacturer_id as m_id from " . DB_PREFIX . "product where product_id = '$product_id'");
		if($res->num_rows > 0 && $res->row['m_id']) {
			$m_id = (int)$res->row['m_id'];
			$res = $this->db->query("select entity_type, mta_scheme_id from " . DB_PREFIX . "mta_product_default_scheme where entity_type like 'm%' and entity_id = '$m_id'");
			foreach($res->rows as $r) {
				$this->_set_for_entity($product_id, $r['entity_type'], $r['mta_scheme_id']);
			}
		}
		$res = $this->db->query("select category_id from " . DB_PREFIX . "product_to_category where product_id = '$product_id'");
		if($res->num_rows > 0) {
			$c = array();
			foreach($res->rows as $r) {
				if($r['category_id']) $c[] = $r['category_id'];
			}
			if(sizeof($c) > 0) {
				$res = $this->db->query("select entity_type, mta_scheme_id from " . DB_PREFIX . "mta_product_default_scheme where entity_type != 'coupon' and entity_type like 'c%' and entity_id in (" . implode(',', $c) . ") order by entity_id desc");
				foreach($res->rows as $r) {
					$this->_set_for_entity($product_id, $r['entity_type'], $r['mta_scheme_id']);
				}			
			}		
		}	
	}
	
///////////////////////////
	protected function _countUsage($id, $id_field, $tbl = 'product') {
		$out = array();
		foreach(array('product','coupon','discount','special') as $k) {
			$out[$k] = 0;
		}				
		$tbl = DB_PREFIX . 'mta_' . $tbl;		
		$res = $this->db->query("select count(*) as c, price_mod_type as modt from $tbl where {$id_field}='" . $this->db->escape($id) . "' group by price_mod_type");
		if($res->num_rows < 1) return $out;
		foreach($res->rows as $r) {
			$r['modt'] = str_replace(DB_PREFIX . 'mta_', '', $tbl) . ($r['modt'] == '' ? '' : '_' . $r['modt']); 
			$out[$r['modt']] = $r['c'];
		}
		return $out;
	}

	private function _set_for_entity($product_ids, $entity_type, $scheme_id) {
		$price_mod_type = strpos($entity_type, '_') === 1 ? substr($entity_type, 2) : '';
		$_type = $price_mod_type;
		if($_type != 'coupon') $_type = 'product_' . $_type;
		if(!is_array($product_ids)) $product_ids = array($product_ids);
		$sz_product_ids = sizeof($product_ids);
		if($sz_product_ids > 20) {
			ignore_user_abort(1);
			set_time_limit($sz_product_ids * 10);
		}
		$this->db->query('start transaction');
		foreach($product_ids as $product_id) {
			if($price_mod_type === '') {
				($scheme_id > 0 ? $this->setProductScheme($product_id, $scheme_id) : $this->unsetProductScheme($product_id));
			} else {
				if($scheme_id > 0) {
					$q = "insert into " . DB_PREFIX . "mta_product (product_id, price_mod_type, price_mod_id, mta_scheme_id) select '$product_id', '$price_mod_type', {$_type}_id, '" . (int)$scheme_id . "' from " . DB_PREFIX . "{$_type} ";
					if($_type != 'coupon') $q .= " where product_id = '$product_id' ";
					$q .=" on duplicate key update mta_scheme_id='" . (int)$scheme_id . "'";					
				} else {
					$q = "delete from " . DB_PREFIX . "mta_product where product_id = '$product_id' and price_mod_type = '$price_mod_type' and price_mod_id in (select {$_type}_id from " . DB_PREFIX . "{$_type} ";
					if($_type != 'coupon') $q .= " where product_id = '$product_id'";
					$q .= ")";					
				}
				$this->db->query($q);
			}		
		}
		$this->_commit();
	}
	
}
