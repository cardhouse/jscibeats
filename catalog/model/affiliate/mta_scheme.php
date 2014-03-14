<?php

class ModelAffiliateMtaScheme extends Model {

	public $scheme_id;
	public $scheme = array();
	
	public function getAllSchemeIds() {
		$res = $this->db->query("select mta_scheme_id from " . DB_PREFIX . "mta_scheme");		
		$out = array();
		foreach($res->rows as $r) {
			$out[] = $r['mta_scheme_id'];
		}
		return $out;	
	}
		
	public function getDefaultSchemeId() {
		$res = $this->db->query("select mta_scheme_id from " . DB_PREFIX . "mta_scheme where is_default > 0 order by mta_scheme_id asc limit 1");
		return ($res->num_rows > 0 ? intval($res->row['mta_scheme_id']) : 0);
	}	
	
	public function &getSchemeById($val) {
		return $this->_getScheme($val, 'mta_scheme_id');
	}
	
	public function &getSchemeByName($val) {
		return $this->_getScheme($val, 'scheme_name');
	}

	public function &getSchemeByCode($val) {
		return $this->_getScheme($val, 'signup_code');
	}

	
//////////////////////////////////////////////////////////////////////	
	protected function _checkId($id) {
		if(!mta_check_int($id) || $id < 1) return false;
		$r = $this->db->query("select scheme_name from " . DB_PREFIX . "mta_scheme where mta_scheme_id='$id'");
		return ($r->num_rows > 0 ? true : false);
	}

	protected function &_getScheme($val, $by_field='mta_scheme_id') {
		$this->scheme = array();
		$res = $this->db->query("select *  from " . DB_PREFIX . "mta_scheme where $by_field='" . $this->db->escape($val) . "'");
		if($res->num_rows < 1) return $this->scheme;
		$s = array();
		$s = $res->row;
		$s['id'] = $s['mta_scheme_id'];
		$s['name'] = $s['scheme_name'];
		$s['_commissions'] = unserialize($s['all_commissions']);
		$s['_autoadd'] = unserialize($s['all_autoadd']);
		foreach(array('mta_scheme_id', 'scheme_name', 'all_commissions', 'all_autoadd') as $_k) {
			unset($s[$_k]);
		}
		$res = $this->db->query("select autoapprove as a from " . DB_PREFIX . "mta_autoapprove where mta_scheme_id='{$s['id']}' order by signup_level asc");
		$s['_autoapprove'] = array();
		foreach($res->rows as $_r) {
			$s['_autoapprove'][] = $_r['a'];
		}
		return $this->setScheme($s);		
	}	

	protected function &setScheme($s) {		
		$this->scheme = array();
		if(!isset($s['id']) || !mta_check_int($s['id']) || $s['id'] < 1) {			
			$this->scheme['error'] = 'Invalid ID';
			return $this->scheme;		
		}
		
		$this->scheme_id = $s['id'];
		
		foreach(array('is_default', 'before_shipping') as $_k) {
			$this->scheme[$_k] = (bool) $s[$_k];
		}		

		foreach(array('max_levels', 'eternal') as $_k) {
			$this->scheme[$_k] = (int) $s[$_k];
		}
		
		foreach($s['_autoapprove'] as $i => $v) {
			$s['_autoapprove'][$i] = (bool) $v;
		}
		
		foreach($s['_autoadd'] as $i => $v) {
			foreach($v as $i2 => $v2) {
				$v[$i2] = (bool) $v2;
			}
			$s['_autoadd'][$i] = $v;
		}

		foreach($s['_commissions'] as $i => $v) {
			foreach($v as $i2 => $v2) {
				$v[$i2] = mta_float4($v2);
			}
			$s['_commissions'][$i] = $v;
		}
		
		foreach($s as $k => $v) {
			if(!isset($this->scheme[$k])) $this->scheme[$k] = $v;
		}
 		
 		$dsid = $this->getDefaultSchemeId();
		if(!$dsid && !$this->scheme['is_default']) $this->scheme['is_default'] = true;
 		
		//if($this->scheme['commission_type'] != 'percentage') $this->scheme['is_default'] = false;		
		return $this->scheme;
	}

	protected function _rollback() {
		try {
			$this->db->query('rollback');
		} catch(Exception $e) {
			//
		}
		return false;
	}	
	
	protected function _commit() {
		$this->db->query("commit");
		return true;
	}
	
}
