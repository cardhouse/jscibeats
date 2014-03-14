<?php

if(!class_exists('ModelMtaProduct')) require_once(DIR_APPLICATION . 'model/mta/mta_product.php');

class ModelMtaMtaProductAffiliate extends ModelMtaMtaProduct {

	public function getProductAffiliates($product_id, $mod_type='', $mod_id=0) {
		$out = array();
		$res = $this->db->query("select mta_scheme_id, affiliate_id from " . DB_PREFIX . "mta_product_affiliate where product_id='" . (int) $product_id . "' and price_mod_type='" . $this->db->escape($mod_type) . "' and price_mod_id='" . (int) $mod_id . "'");
		if($res->num_rows > 0) {
			foreach($res->rows as $r) {
				$_id = strval($r['mta_scheme_id']);
				if(!isset($out[$_id])) $out[$_id] = array();
				$out[$_id][] = $r['affiliate_id'];
			}			
		}
		return $out;
	}
	
	
}