<?php

class ControllerMtaMtaSetScheme extends Controller {
	
	public function move() {
		//from, to (id or 0), types=(all, p, a, pa, p_c, p_d, p_s)
		
		$this->load->language('mta/mta');
    if(!$this->user->hasPermission('modify', 'mta/mta')) return $this->_jerror('permission');
		foreach(array('from', 'to', 'types') as $_k) {
			if(!isset($this->request->request[$_k])) return $this->_jerror('invalid_request');
			$$_k = $this->request->request[$_k];
		}
		if(!mta_check_int($from) || !mta_check_int($to)) return $this->_jerror('validation');
		$_types = array('p','a','pa','p_c','p_d','p_s','pa_c','pa_d','pa_s');
		
		$types = preg_split("/\s*,\s*/", $types);
		foreach($types as $i => $v) {
			if(!in_array($v, $_types)) unset($types[$i]);
		}
		$types = array_values(array_unique($types));
		$sz = sizeof($types);
		if(!$sz) return $this->_jerror('validation');
		if(in_array('a', $types)) {
			$this->load->model('mta/mta_affiliate');
			$_ret = $this->model_mta_mta_affiliate->moveFromScheme($from, $to);
			if($sz < 2) {
				$this->response->setOutput($_ret !== false ? '1' : '0');
				return;
			}
		}
		$this->load->model('mta/mta_product_affiliate');

		$mod_types = array(
			'c' => 'coupon',
			's' => 'special',
			'd' => 'discount'
		);
		$mods_p = array();
		$mods_pa = array();
		foreach($types as $type) {
			if(strlen($type) > 1 && substr($type, 0, 2) == 'pa') {
				$_mods =& $mods_pa;
			} else {
				$_mods =& $mods_p;
			}
			$_expl = explode('_', $type);
			if(sizeof($_expl) < 2) {
				$_mods[] = "''";
			} else {
				$_mods[] = "'" . $mod_types[$_expl[1]] . "'";
			}
		}
		if(sizeof($mods_p) > 0) {
			$_ret1 = $this->model_mta_mta_product_affiliate->moveFromScheme($from, $to, $mods_p, true);			
		} else {
			$_ret1 = true;
		}
		if(sizeof($mods_pa) > 0) {
			$_ret2 = $this->model_mta_mta_product_affiliate->moveFromScheme($from, $to, $mods_pa);	
		} else {
			$_ret2 = true;
		}
		$this->response->setOutput($_ret1 && $_ret2 ? '1' : '0');
	}
	
	public function set() {
		//+reset
		
		
	}
	
////////////////////////////////////////////
	private function _jerror($msg) {
		$this->response->setOutput($this->language->get('error_' . $msg));
		return false;
	}
		
		
	
}