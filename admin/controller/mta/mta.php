<?php

class ControllerMtaMta extends Controller {
	private $error = array();
	private $scheme = array();
	private $error_keys = array('permission','name','duplicate_name','invalid_percent',
'invalid_commission','unknown','database','warning','no_default','no_default_unconverted','all');

	public function index() {
		$this->load->model('mta/mta_affiliate');
		$this->load->model('mta/mta_product_affiliate');

		$_fields = array('heading_title','text_no_results','column_name','column_code','column_action','column_commission','error_invalid_request','entry_tier','text_percent','text_amount','text_show_usage','text_hide','text_delete_scheme_warning','text_success','text_copy_scheme','entry_new_name','text_move_users','text_move','text_select_scheme','button_insert','button_delete','text_default','error_fixed_aff_commission','text_convert_success',
		'text_settings', 'button_save_settings', 'column_settings_tracking', 'column_settings_no_shipping', 'column_settings_autoadd_statuses', 'column_settings_llaff_priority', 'text_settings_llaff_higher', 'text_settings_llaff_lower', 'text_settings_tracking_permanent', 'text_settings_tracking_cookies', 'text_settings_no_shipping_default', 'text_settings_no_shipping_any', 'text_settings_no_shipping_subtotal');

		$this->data['unconverted_affiliates'] = $this->model_mta_mta_affiliate->countPreMtaAffiliates();
		if($this->data['unconverted_affiliates']) $_fields = array_merge($_fields, array('text_unconverted_affiliates','text_convert_affiliates'));
		
		$this->_preprocess($_fields, array('insert'));
		
		$this->data['has_default'] = $this->model_mta_mta_affiliate->getDefaultSchemeId() ? true : false;
		if(!$this->data['has_default'])	$this->error = array_merge($this->error, array('no_default', 'no_default_unconverted'));
		
		$this->data['schemes'] = $this->model_mta_mta_affiliate->getSchemes(array('sort' => 'default', 'order' => 'desc'));
				
		$this->data['user_types'] = array(
			'affiliate' => 'a',
			'product' => 'p',			
			'product_discount' => 'p_d',
			'product_special' => 'p_s',
			'product_coupon' => 'p_c',
			'product_affiliate' => 'pa',
			'product_affiliate_discount' => 'pa_d',
			'product_affiliate_special' => 'pa_s',
			'product_affiliate_coupon' => 'pa_c'	
		);
		$this->data['user_types_lng'] = array();
		$_trans = array();
		foreach($this->data['user_types'] as $_k => $_v) {
			$_kAr = explode('_', $_k);
			$_kAr2 = array();
			foreach($_kAr as $_k2) {
				if(!isset($_trans[$_k2])) $_trans[$_k2] = $this->language->get('word_' . $_k2);
				$_kAr2[] = ucfirst($_trans[$_k2]);
			}
			$this->data['user_types_lng'][$_k] = implode(' / ', $_kAr2);
		}
		
		$this->data['s_opts'] = array();
		$this->data['all_fixed'] = array();
		
		foreach($this->data['schemes'] as $i => $s) {
			$s['id'] = $s['mta_scheme_id'];
			$s['name'] = $s['scheme_name'];
			$s['user_count'] = $this->_count_scheme_users($s['id']);
			$s['user_total'] = array_sum(array_values($s['user_count']));
			foreach($this->data['user_types'] as $_k => $_v) {
				if(!isset($s['user_count'][$_k])) $s['user_count'][$_k] = 0;
			}
			$_c = unserialize($s['all_commissions']);
			foreach($_c as $ii => $v) {
				foreach($v as $i2 => $v2) {
					$_c[$ii][$i2] = mta_float4($v2);
				}
				$_c[$ii] = implode(', ', $_c[$ii]);
			}
			$s['commissions'] = implode('<br />', $_c);
			unset($s['mta_scheme_id']);
			unset($s['scheme_name']);
			unset($s['all_commissions']);
			unset($s['all_autoadd']);
			
			$s['edit_link'] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('mta/mta/update', 'token=' . $this->session->data['token'] . '&id=' . $s['id'], 'SSL')
			);			
			$this->data['s_opts'][] = array('id' => $s['id'], 'name' => $s['name']);
			if($s['commission_type'] == 'fixed') $this->data['all_fixed'][] = (int) $s['id'];
			$this->data['schemes'][$i] = $s;			
		}
		
		$this->data['all_fixed'] = json_encode($this->data['all_fixed']);
		$this->data['num_schemes'] = sizeof($this->data['schemes']);		

		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->data['autoadd_statuses'] = $this->config->get('mta_ypx_autoadd_statuses');
		if(!is_array($this->data['autoadd_statuses'])) $this->data['autoadd_statuses'] = array($this->config->get('config_complete_status_id'));
		
		$this->_process_errors();		
		$this->template = 'mta/mta.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render());		
	}


	public function insert() {
		$this->load->model('mta/mta_scheme');
		if($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['name'])) {
			$this->_save();
			return;
		}
		
		$this->_preprocess(array('heading_title_insert','entry_name','entry_is_default','entry_before_shipping','entry_tiers','entry_tier','column_commission','column_autoadd','column_autoapprove','text_default_value','entry_add_tier','entry_remove_tier','button_save','button_cancel','text_yes','text_no','entry_autoadd','entry_autoapprove','text_percent','text_amount','entry_eternal','text_eternal_description','entry_commission_type','entry_level','error_duplicate_name','error_invalid_percent','entry_description','error_invalid_commission','error_name'), array('insert','save','cancel'));				

		$this->_process_errors();
		$this->template = 'mta/mta_insert.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render());		
	}

	public function update() {
		if(!isset($this->request->request['id']) || !mta_check_int($this->request->request['id']) || $this->request->request['id'] < 1) {
			$this->redirect($this->url->link('mta/mta/insert', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			return;
		}		
		$this->load->model('mta/mta_scheme');		
		
		if($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['name'])) {
			$this->_save();
			return;
		}
		
		$id = intval($this->request->request['id']);
		$this->model_mta_mta_scheme->getSchemeById($id);
		$this->scheme =& $this->model_mta_mta_scheme->scheme;
		if(!$this->scheme || isset($this->scheme['error'])) {
			$this->redirect($this->url->link('mta/mta/insert', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			return;
		}		
		
		$this->_preprocess(array('heading_title_update','entry_name','entry_is_default','entry_before_shipping','entry_tiers','entry_tier','column_commission','column_autoadd','column_autoapprove','text_default_value','entry_add_tier','entry_remove_tier','button_save','button_cancel','text_yes','text_no','entry_autoadd','entry_autoapprove','text_percent','text_amount','entry_eternal','text_eternal_description','entry_commission_type','entry_level','error_duplicate_name','error_invalid_percent','entry_description','error_invalid_commission','error_name','text_no_fixed_for_affs'), array('update','save','cancel'));		
		
		foreach($this->scheme as $k => $v) {
			$this->data['s_' . $k] = $v;
		}	

		$this->data['update'] = $this->url->link('mta/mta/update', 'token=' . $this->session->data['token'] . '&id=' . $id, 'SSL');
		
		$this->data['s_autoadd_json'] = json_encode($this->data['s__autoadd']);	
		$this->data['s_autoapprove_json'] = json_encode($this->data['s__autoapprove']);	
		$this->data['s_commissions_json'] = json_encode($this->data['s__commissions']);
		$this->data['has_affiliates'] = $this->model_mta_mta_scheme->hasAffiliates();

		$this->_process_errors();
		$this->template = 'mta/mta_update.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render());
	}

	public function delete() {		
		if(!isset($this->request->post['id']) || !mta_check_int($this->request->post['id']) || $this->request->post['id'] < 1 || !isset($this->request->post['really_do_delete']) || $this->request->post['really_do_delete'] != ($this->request->post['id'] * 3 + 517) || !$this->user->hasPermission('modify', 'mta/mta')) {
			$this->response->setOutput('0');
			return;
		}
		$this->load->model('mta/mta_scheme');
		$this->response->setOutput($this->model_mta_mta_scheme->deleteScheme($this->request->post['id']) ? '1' : '0');
	}
	
	public function check_scheme_name() {
		if(!isset($this->request->request['name']) || $this->request->request['name'] == 'INVALID') {
			$this->response->setOutput('0');
			return;
		}
		$this->load->model('mta/mta_scheme');
		$id = isset($this->request->request['id']) ? $this->request->request['id'] : false;		
		$this->response->setOutput(intval($this->model_mta_mta_scheme->checkName($this->request->request['name'], $id)));
	}

	public function copy() {
		if(!isset($this->request->request['name']) || !isset($this->request->request['id']) || !$this->user->hasPermission('modify', 'mta/mta')) {
			$this->response->setOutput('0');
			return;
		}
		$this->load->model('mta/mta_scheme');
		$this->response->setOutput(intval($this->model_mta_mta_scheme->copyScheme($this->request->request['id'], $this->request->request['name'])));
	}
	
	public function convert_affiliates() {
		$this->load->model('mta/mta_affiliate');
		if(!$this->model_mta_mta_affiliate->countPreMtaAffiliates()) {
			$this->response->setOutput('1');
			return;
		}
		$r = $this->model_mta_mta_affiliate->convertPreMtaAffiliates();
		$this->response->setOutput($r ? strval($r) : '0');
	}
	
	
	public function save_settings() {
		if(!$this->user->hasPermission('modify', 'mta/mta')) {
			$this->response->setOutput('0');
			return;
		}	
		if(!isset($this->request->post['mta_ypx_autoadd_statuses'])) {
			$this->request->post['mta_ypx_autoadd_statuses'] = array();
		} else {
			$this->request->post['mta_ypx_autoadd_statuses'] = array_values($this->request->post['mta_ypx_autoadd_statuses']);
		}		
		$_settings = array();
		foreach($this->request->post as $k => $v) {
			if(strpos($k, 'mta_ypx_') === 0) $_settings[$k] = $v;
		}
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('mta_ypx', $_settings);
		$this->response->setOutput('1');	
	}
	
/////////////////////////////////////////////////////////////////
	private function _count_scheme_users($scheme_id) {
		if(!mta_check_int($scheme_id) || $scheme_id < 1) return false;
		$c = array(
			'affiliate' => $this->model_mta_mta_affiliate->countUsers($scheme_id)
		);		
		$c = array_merge($c, $this->model_mta_mta_product_affiliate->countUsers($scheme_id, 'product_affiliate'));
		$c = array_merge($c, $this->model_mta_mta_product_affiliate->countUsers($scheme_id));		
		
		return $c;		
	}
	
	private function _save() {
		$this->load->language('mta/mta');
    if(!$this->user->hasPermission('modify', 'mta/mta')) {
     		$this->response->setOutput($this->language->get('error_permission'));
     		return false;
    }		
		if(!$this->_post_to_scheme($this->request->post)) {
			if(isset($this->scheme['error'])) {
				$_er = $this->language->get('error_' . $this->scheme['error']);
				if($_er == $this->scheme['error']) $_er = $this->language->get('error_database') . ' : ' . $this->scheme['error']; 
			} else {
				$_er = $this->language->get('error_validation');
			}
			$this->response->setOutput($_er);
			return false;
		}
		$_ret = $this->model_mta_mta_scheme->saveScheme();			
		$this->response->setOutput($_ret === true ? '1' : $this->language->get('error_database'));
		return ($_ret === true);		
	}

	private function _preprocess($fields, $buttons) {
		$this->load->language('mta/mta');
		$this->document->setTitle($this->language->get('heading_title'));
		
		foreach($fields as $_v) {
			$this->data[$_v] = $this->language->get($_v);			
		}
		
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
		$url = '';
		
  	$this->data['breadcrumbs'] = array(
   		array(
    	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => false
   		),

   		array(
      'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('mta/mta', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      'separator' => ' :: '
   		)
   	);
		
		foreach($buttons as $_b) {
			$_bv = $_b == 'cancel' ? '' : '/' . $_b;	
			$this->data[$_b] = $this->url->link('mta/mta' . $_bv, 'token=' . $this->session->data['token'] . $url, 'SSL');
		}
	}
	
	private function _post_to_scheme(&$p) {
		foreach(array('name', 'max_levels') as $_v) {
			if(!isset($p[$_v])) return false;
		}
		if(utf8_strlen($p['name']) < 1 || utf8_strlen($p['name']) > 100 || !mta_check_int($p['max_levels'])) return false;
		
		if(!isset($p['description'])) $p['description'] = '';
		if(!isset($p['is_default']) || !mta_check_bool($p['is_default'])) $p['is_default'] = 0;
		if(!isset($p['commission_type']) || !in_array($p['commission_type'], array('fixed', 'percentage'))) $p['commission_type'] = 'percentage';
		if(!isset($p['before_shipping']) || !mta_check_bool($p['before_shipping'])) $p['before_shipping'] = 1;
		if(!isset($p['eternal']) || !mta_check_int($p['eternal'])) $p['eternal'] = 0;

		$scheme = array();
		$_levels = array('autoadd' => array(), 'commission' => array());
		$autoapprove = array();		
		foreach($p as $k => $v) {
			if(preg_match("/^((?:tiers)|(?:level))(\d+)_(.+)$/", $k, $_lAr)) {
				if($_lAr[1] == 'tiers') {
					if(!preg_match("/^\d+(\.\d+)?$/s", $v)) return false;
					$tier = $_lAr[2];
					$_lAr = explode('_', $_lAr[3]);
					$_type = $_lAr[1];
					preg_match("/\d+$/", $_lAr[0], $_lAr);
					$level = $_lAr[0];
					if(!isset($_levels[$_type][$tier])) $_levels[$_type][$tier] = array();
					$_levels[$_type][$tier][$level] = $v;					
				} else {
					$autoapprove[$_lAr[2]] = $v;					
				}
			} else {
				$scheme[$k] = $v;
			}
		}
		//if($scheme['commission_type'] == 'fixed') $scheme['is_default'] = 0;		
		
		if($scheme['eternal'] > $scheme['max_levels']) $scheme['eternal'] = 0;		
		$scheme['_autoapprove'] = mta_array_a2n($autoapprove);
		if(sizeof($scheme['_autoapprove']) != $scheme['max_levels']) return false;
		foreach($scheme['_autoapprove'] as $_i => $_v) {
			if(!mta_check_bool($_v)) $scheme['_autoapprove'][$_i] = 1;
		}
		
		foreach($_levels as $k => $v) {			
			$v = mta_array_a2n($v);
			if(sizeof($v) != $scheme['max_levels']) return false;
			foreach($v as $i => $v2) {				
				$v[$i] = mta_array_a2n($v2);
				if(sizeof($v[$i]) != ($i + 1)) return false;
				foreach($v[$i] as $_i => $_v) {
					if($k == 'autoadd') {
						if(!mta_check_bool($_v)) $v[$i][$_i] = 1;
					} else if($k == 'commission') {
						if(!mta_check_float($_v)) return false;
					} else {
						return false;
					}
				}
			}
			$_levels[$k] = $v;
		}
		
		$scheme['_commissions'] = $_levels['commission'];
		$scheme['_autoadd'] = $_levels['autoadd'];		
		$this->scheme =& $this->model_mta_mta_scheme->setScheme($scheme);
		return (isset($this->scheme['error']) ? false : true);		
	}
	
	private function _process_errors() {		
		$_num_errors = sizeof($this->error);
		$_errors = array();
		if($_num_errors) {
			foreach($this->error as $i => $_er) {
				if(is_array($_er)) {
					$_msg = mta_tpl($this->language->get('error_' . $_er[0]));
					$_er = $_er[0];
				} else {
					$_msg = $this->language->get('error_' . $_er);
				}
				if(!isset($this->data['error_' . $_er])) $this->data['error_' . $_er] = $_msg;
				$this->error[$_er] = $this->data['error_' . $_er];
				$_errors[] = $this->data['error_' . $_er];
				unset($this->error[$_er]);
			}		
			if($_num_errors > 1) {
				$this->data['error_all'] = $this->language->get('error_all') . "\n" . implode("\n", $_errors);
			} else {
				$this->data['error_all'] = $_errors[0];
			}
		}		
		foreach($this->error_keys as $_er) {
			if(!isset($this->data['error_' . $_er])) $this->data['error_' . $_er] = '';
		}
	}

}