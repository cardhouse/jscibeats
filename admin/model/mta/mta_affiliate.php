<?php

if(!class_exists('ModelMtaMtaScheme')) require_once(DIR_APPLICATION . 'model/mta/mta_scheme.php');

class ModelMtaMtaAffiliate extends ModelMtaMtaScheme {
	
	protected static $table = "mta_affiliate";
	
	public function convertPreMtaAffiliates() {		
		$res = $this->db->query("select " . DB_PREFIX . "affiliate.affiliate_id as aid, " . DB_PREFIX . "customer.affiliate_id as pid from " . DB_PREFIX . "affiliate left join " . DB_PREFIX . "customer on " . DB_PREFIX . "customer.email=" . DB_PREFIX . "affiliate.email left join " . DB_PREFIX . self::$table . " on " . DB_PREFIX . self::$table . ".affiliate_id=" . DB_PREFIX . "affiliate.affiliate_id where " . DB_PREFIX . self::$table . ".affiliate_id is null");
		
		$ar = array();		
		foreach($res->rows as $r) {
			$ar[intval($r['aid'])] = $r['pid'];
		}		
		$ar = mta_find_all_parents($ar);		
		//print_r($ar);//-tmp
		$_sqlar = array();
		foreach($ar as $aid => $pids) {
			$_sqlar[] = "('" . $aid . "','" . ($pids ? $pids[0] : 0) . "','" . ($pids ? implode(',', $pids) : "") . "','" . ($pids ? sizeof($pids) + 1 : 1) . "')";
		}
		$this->db->query("insert into " . DB_PREFIX . self::$table . " (affiliate_id, parent_affiliate_id, all_parent_ids, level_original) values " . implode(',', $_sqlar));
		return $this->db->countAffected();
	}	
	
	public function countPreMtaAffiliates() {
		$res = $this->db->query("select count(*) as c from " . DB_PREFIX . "affiliate left join " . DB_PREFIX . self::$table . " on " . DB_PREFIX . self::$table . ".affiliate_id=" . DB_PREFIX . "affiliate.affiliate_id where " . DB_PREFIX . self::$table . ".affiliate_id is null");
		return $res->row['c'];
	}
	
	public function moveFromScheme($from, $to) {
		if(!mta_check_int($from) || !mta_check_int($to)) return false;
		if(($from && !$this->_checkId($from)) || ($to && !$this->_checkId($to))) return false;
		$res = $this->db->query("select commission_type as ct from " . DB_PREFIX . "mta_scheme where mta_scheme_id='$to'");
		//if($res->row['ct'] != 'percentage') return false;
		$query = "update " . DB_PREFIX . self::$table . " set mta_scheme_id=" . ($to ? "'$to'" : "null") . " where mta_scheme_id" . ($from ? "='$from'" : " is null");		
		$this->db->query($query);
		return $this->db->countAffected();		
	}
	
	public function countUsers($scheme_id) {		
		$res = $this->db->query("select count(*) as c from " . DB_PREFIX . self::$table . " where mta_scheme_id='" . $this->db->escape($scheme_id) . "'");
		return $res->row['c'];		
	}
	
	public function getAffiliate($affiliate_id, $get_parent_name=false) {
		if(!mta_check_int($affiliate_id) || $affiliate_id < 1) return false;
		$res = $this->db->query("select affiliate_id as id, mta_scheme_id as scheme_id, parent_affiliate_id, all_parent_ids, level_original from " . DB_PREFIX . self::$table . " where affiliate_id='$affiliate_id'");
		if($res->num_rows < 1) return false;
		$out = $res->row;
		if($get_parent_name && $out['parent_affiliate_id']) {
			$res = $this->db->query("select concat(firstname, ' ', lastname) as n from " . DB_PREFIX . "affiliate where affiliate_id='" . $out['parent_affiliate_id'] . "'");
			$out['parent_affiliate_name'] = $res->row['n'];
		}
		$out['parents'] = $out['all_parent_ids'] ? explode(',', $out['all_parent_ids']) : array();
		return $out;
	}		

	public function getPayoutAccounts($affiliate_ids) {
		if(!is_array($affiliate_ids)) $affiliate_ids = array($affiliate_ids);
		if(sizeof($affiliate_ids) < 1) return array();
		$methods = array('paypal', 'bank', 'cheque');
		$query = "select affiliate_id, payment, cheque, paypal, bank_name, bank_branch_number, bank_swift_code, bank_account_name, bank_account_number from " . DB_PREFIX . "affiliate where affiliate_id in (" . implode(',', $affiliate_ids) . ")";
		$res = $this->db->query($query);
		$out = array();
		foreach($res->rows as $r) {			
			$_id = intval($r['affiliate_id']);
			$_out = array();
			foreach(array('cheque', 'bank_account_name', 'bank_account_number') as $_k) {
				$r[$_k] = trim($r[$_k]);
			}
			if((!in_array($r['payment'], $methods)) || ($r['payment'] == 'paypal' && !mta_is_email($r['paypal'])) || ($r['payment'] == 'cheque' && !$r['cheque']) || ($r['payment'] == 'bank' && !$r['bank_account_name'] && !$r['bank_account_number'])) {
				$out[$_id] = false;
				continue;
			}
			$_out['method'] = $r['payment'];
			switch($_out['method']) {
				case 'paypal':
					$_out['account'] = $r['paypal'];
					break;
				case 'cheque':
					$_out['account'] = $r['cheque'];
					break;
				case 'bank':
					$_out['account'] = array();
					foreach($r as $_k => $_v) {
						if(strpos($_k, 'bank_') === 0) $_out['account'][substr_replace($_k, '', 0, 5)] = $r[$_k];
					}
					break;				
			}
			$out[$_id] = $_out;
		}
		return $out;
	}	
	
}