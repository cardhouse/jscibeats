<?php

class ModelMtaMtaScheme extends Model {

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
	
	public function getSchemes($data=array()) {
		if(isset($data['fields'])) {
			if(is_array($data['fields'])) $data['fields'] = implode(',', $data['fields']);
		} else {
			$data['fields'] = '*';
		}
		$sql = "select " . $data['fields'] . " from " . DB_PREFIX . "mta_scheme";
		
		if(isset($data['filter_raw'])) {
			$sql .= " where " . $data['filter_raw'];
		} else if(isset($data['filter_commission_type'])) {
			$sql .= " where commission_type='" . $data['filter_commission_type'] . "'";
		}
		
		$sort_data = array(
				'id' => 'mta_scheme_id',
				'levels' => 'max_levels',
				'default' => 'is_default',
				'type' => 'commission_type'				
		);	
		
		$_order = isset($data['sort']) && isset($sort_data, $data['sort']) ? $sort_data[$data['sort']]: '';
		if($_order) {			
			$_order .= (isset($data['order']) && (strtolower($data['order']) == 'desc') ? ' desc' : ' asc') . ', ';
		}
		$sql .= " order by {$_order}scheme_name asc";
		
		if (isset($data['start']) || isset($data['limit'])) {
			if (!isset($data['start']) || $data['start'] < 0) {
				$data['start'] = 0;
			}
			if (!isset($data['limit']) || $data['limit'] < 1) {
				$data['limit'] = 25;
			}		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
		$res = $this->db->query($sql);
		return $res->rows;		
	}
	
	public function getDefaultSchemeId() {
		$res = $this->db->query("select mta_scheme_id from " . DB_PREFIX . "mta_scheme where is_default > 0 order by mta_scheme_id asc limit 1");
		return ($res->num_rows > 0 ? $res->row['mta_scheme_id'] : 0);
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
	
	public function deleteScheme($id) {
		if(!mta_check_int($id) || $id < 1) return false;
		$fix_default = $this->getDefaultSchemeId() == $id ? true : false;
		if($fix_default) {
			$res = $this->db->query("select count(*) as c from " . DB_PREFIX . "mta_scheme where mta_scheme_id!='$id'");
			if($res->row['c'] < 1) return false;
		}
		$this->db->query("delete from " . DB_PREFIX . "mta_scheme where mta_scheme_id='$id'");
		$ret = $this->db->countAffected() > 0 ? true : false;
		if($fix_default && $ret === true) $this->_fixDefaultScheme();
		return $ret;			
	}

	public function &setScheme($s) {		
		if(!isset($s['id'])) {			
				$this->scheme['signup_code'] = $this->_make_signup_code();
		} else {
			$this->scheme['id'] = intval($s['id']);
			if(!preg_match("/^\d+$/", $s['id']) || $this->scheme['id'] < 1) {
				$this->scheme['error'] = 'Invalid ID';
				return $this->scheme;
			}
			$this->scheme_id = $this->scheme['id'];
		}
		
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

	public function saveScheme() {
		
		$query = isset($this->scheme['id']) ? 'update ' : 'insert ';
		$query .= DB_PREFIX . "mta_scheme set scheme_name='" . $this->db->escape($this->scheme['name']) . "', description='" . $this->db->escape($this->scheme['description']) . "', max_levels='" . $this->scheme['max_levels'] . "', is_default='" . intval($this->scheme['is_default']) . "', all_commissions='" . $this->db->escape(serialize($this->scheme['_commissions'])) . "', all_autoadd='" . $this->db->escape(serialize($this->scheme['_autoadd'])) . "', commission_type='" . $this->scheme['commission_type'] . "', before_shipping='" . intval($this->scheme['before_shipping']) . "', eternal='" . intval($this->scheme['eternal']) . "'";		
		if(!isset($this->scheme['id'])) $query .= ", signup_code='" . $this->db->escape($this->scheme['signup_code']) . "'";
		if(isset($this->scheme['id'])) $query .= " where mta_scheme_id='" . $this->scheme['id'] . "'";
		
		$this->db->query('start transaction');		
 		
 		$dsid = $this->getDefaultSchemeId();
		if($dsid && $this->scheme['is_default']) 	$this->db->query("update " . DB_PREFIX . "mta_scheme set is_default='0' where is_default!='0'");			
		
		if(isset($this->scheme['id'])) {
			$this->db->query("delete from " . DB_PREFIX . "mta_autoapprove where mta_scheme_id='" . $this->scheme['id'] . "'");
			if($this->db->countAffected() < 1) return $this->_rollback();
			$this->db->query("delete from " . DB_PREFIX . "mta_scheme_levels where mta_scheme_id='" . $this->scheme['id'] . "'");
			if($this->db->countAffected() < 1) return $this->_rollback();
		}
				
		$this->db->query($query);
		
		if($this->db->countAffected() < 1) return $this->_rollback();
		if(!isset($this->scheme['id'])) {
			$sid = $this->db->getLastId();
			if(!$sid) return $this->_rollback();
			$this->scheme['id'] = intval($sid);
			$this->scheme_id = $this->scheme['id'];
		}		
		
		$_autoapprove = array();
		for($i = 0; $i < sizeof($this->scheme['_autoapprove']);$i++) {
			$_autoapprove[] = "('" . $this->scheme['id'] . "','" . ($i+1) . "','" . intval($this->scheme['_autoapprove'][$i]) . "')";
		}
		$this->db->query("insert into " . DB_PREFIX . "mta_autoapprove (mta_scheme_id , signup_level, autoapprove) values " . implode(',', $_autoapprove));
		if($this->db->countAffected() < 1 || !$this->db->getLastId()) return $this->_rollback();
		
		$_levels = array();
		foreach($this->scheme['_commissions'] as $i => $v) {
			foreach($v as $i2 => $v2) {
				$_levels[] = "('" . $this->scheme['id'] . "','" . ($i + 1) . "','" . ($i2 + 1) . "','" . mta_float4($v2) . "','" . intval($this->scheme['_autoadd'][$i][$i2]) . "')";
			}
		}
		
		$this->db->query("insert into " . DB_PREFIX . "mta_scheme_levels (mta_scheme_id, num_levels, level, commission, autoadd) values " . implode(',', $_levels));
		if($this->db->countAffected() < 1 || !$this->db->getLastId()) return $this->_rollback();	
		$this->_fixDefaultScheme();
		return $this->_commit();
	}
	
	public function checkName($name, $id = false) {
		$query = "select mta_scheme_id from " . DB_PREFIX . "mta_scheme where scheme_name='" . $this->db->escape($name) . "'";
		if($id !== false) $query .= " and mta_scheme_id != '" . $this->db->escape($id) . "'";
		$res = $this->db->query($query);
		return ($res->num_rows > 0 ? false : true);		
	}
	
	public function copyScheme($id, $name) {
		if(!$name || utf8_strlen($name) < 3 || utf8_strlen($name) > 100) return false;
		$dsid = $this->getDefaultSchemeId();
		$signup_code = $this->_make_signup_code();
		$this->db->query("start transaction");
		$this->db->query("insert into " . DB_PREFIX . "mta_scheme (scheme_name, description,	max_levels,	is_default, all_commissions, all_autoadd, commission_type, before_shipping, eternal, signup_code) select '" . $this->db->escape($name) . "', description,	max_levels,	is_default, all_commissions, all_autoadd, commission_type, before_shipping, eternal, '" . $this->db->escape($signup_code) . "' from " . DB_PREFIX . "mta_scheme where mta_scheme_id='" . $this->db->escape($id) . "'");
		$sid = $this->db->getLastId();
		if(!$sid) return $this->_rollback();
		if($dsid == $id) $this->db->query("update " . DB_PREFIX . "mta_scheme set is_default='0' where mta_scheme_id='$sid'");
		$this->db->query("insert into " . DB_PREFIX . "mta_scheme_levels (mta_scheme_id, num_levels, 	level, commission, autoadd) select '$sid', num_levels, 	level, commission, autoadd from " . DB_PREFIX . "mta_scheme_levels where mta_scheme_id='" . $this->db->escape($id) . "'"); 
		if(!$this->db->countAffected()) return $this->_rollback();
		$this->db->query("insert into " . DB_PREFIX . "mta_autoapprove (mta_scheme_id, signup_level, autoapprove) select '$sid', signup_level, autoapprove from " . DB_PREFIX . "mta_autoapprove where mta_scheme_id='" . $this->db->escape($id) . "'");  		
		return ($this->db->countAffected() ? $this->_commit() : $this->_rollback());	
	}
	
	public function getMaxTotalCommission($scheme_id) {
		$res = $this->db->query("select sum(commission) as c from " . DB_PREFIX . "mta_scheme_levels where mta_scheme_id='" . ((int) $scheme_id) . "' group by num_levels order by c desc limit 1");
		if($res->num_rows < 1) return 0;
		return $res->row['c'];		
	}
	
	public function hasAffiliates($scheme_id = false) {
		if(!$scheme_id) {
			if(!$this->scheme_id) return false;
			$scheme_id = $this->scheme_id;
		}
		$res = $this->db->query("select count(*) as c from " . DB_PREFIX . "mta_affiliate where mta_scheme_id='" . (int) $scheme_id . "'");
		return ($res->row['c'] > 0 ? true : false);	
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

	protected function _fixDefaultScheme() {
		$res = $this->db->query("select count(*) as c from " . DB_PREFIX . "mta_scheme where is_default > 0");
		if($res->row['c'] > 1) {
			$default = $this->getDefaultSchemeId();
			$this->db->query("update " . DB_PREFIX . "mta_scheme set is_default='0' where mta_scheme_id != '$default'");
		} else if($res->row['c'] == 0) {
			$res = $this->db->query("select min(mta_scheme_id) as id from " . DB_PREFIX . "mta_scheme");
			if($res->num_rows > 0) $this->db->query("update " . DB_PREFIX . "mta_scheme set is_default='1' where mta_scheme_id='{$res->row['id']}'");			
		}
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

	private function _make_signup_code() {
		while(true) {
			$ret = uniqid();
			$_res = $this->db->query("select mta_scheme_id from " . DB_PREFIX . "mta_scheme where signup_code='" . $this->db->escape($ret) . "'");
			if($_res->num_rows < 1) return $ret;
			usleep(250000);
		}
	}		
	
}
