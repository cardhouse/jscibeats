<?php

class ControllerMtaMtaPds extends Controller {

	private $error = false;
	
	public function index() {
		$this->load->language('mta/mta_pds');
		if(!$this->validate_get()) $this->error = true;
		if(!$this->error) {
			$this->load->model('mta/mta_product');
			$this->data['schemes'] = $this->_get_schemes();
			if($this->request->request['type'] == 'coupon') {
				$_types = 'coupon';
			} else {
				$_type = substr($this->request->request['type'], 0, 1);
				$_types = array($_type);
				foreach(array('coupon', 'discount', 'special') as $_v) {
					$_types[] = $_type . '_' . $_v;
				}
			}
			$this->data['schemes_default'] = array();
			$_s_def = $this->model_mta_mta_product->getDefaultProductSchemes($_types, $this->request->request['id']);
			if($_s_def) {
				foreach($_s_def as $_v) {
					$this->data['schemes_default'][$_v['type']] = $_v['scheme_id'];
				}
			}
			$this->data['types'] = is_array($_types) ? $_types : array($_types);
			$this->data['mta_type'] = $this->request->request['type'];
			$this->data['entity_id'] = $this->request->request['id'];
			foreach(array('button_set', 'button_save_default', 'option_select', 'option_default', 'text_confirm_set', 'text_confirm_save_default', 'word_saved', 'word_done') as $_v) {
				$this->data[$_v] = $this->language->get($_v);
			}
			$this->data['alert_error'] = $this->language->get('error_warning');
			foreach(array('text_confirm_set', 'text_confirm_save_default', 'alert_error') as $_v) {
				$this->data[$_v] = mta_jsstr($this->data[$_v]);
			}			
			$this->data['url_set'] = mta_jsstr($this->url->link('mta/mta_pds/set', 'token=' . $this->session->data['token'] . '&id=' . $this->data['entity_id'], 'SSL'));
			$this->data['url_save_default'] = mta_jsstr($this->url->link('mta/mta_pds/save_default', 'token=' . $this->session->data['token'] . '&id=' . $this->data['entity_id'], 'SSL'));	
			$this->data['entries_set'] = array();
			$this->data['entries_save_default'] = array();
			foreach($this->data['types'] as $_type) {
				foreach(array('set', 'save_default') as $_action) {
					if($_type == 'coupon' || strlen($_type) < 2) {
						$this->data['entries_' . $_action][$_type] = $this->language->get('entry_' . $_type . '_' . $_action);
					} else {
						$_tAr = explode('_', $_type);
						$this->data['entries_' . $_action][$_type] = sprintf($this->language->get('entry_' . $_tAr[0] . 'sub_' . $_action), $this->language->get('entry_word_' . $_tAr[1]));
					}
				}
			}
		}
		$this->data['error_warning'] = $this->error ? mta_jsstr($this->language->get('error_warning')) : '';
		$this->template = 'mta/mta_pds.tpl';		
		$this->response->setOutput($this->render());		
	}
	
	public function set($func = 'All') {
		if(!$this->validate_set()) $this->error = true;
		if(!$this->error) {
			$this->load->model('mta/mta_product');
			$this->model_mta_mta_product->{'set' . $func . 'ProductScheme'}($this->request->request['type'], $this->request->request['id'], $this->request->request['scheme_id']);		
		}
		$this->response->setOutput($this->error ? '0' : '1');	
	}
	
	public function save_default() {
		$this->set('Default');
	}
	
	private function validate_get() {
		if (!isset($this->request->request['type']) || !isset($this->request->request['id']) || !in_array($this->request->request['type'], array('coupon', 'm', 'c', 'm_coupon', 'm_discount', 'm_special', 'c_coupon', 'c_discount', 'c_special')) || !preg_match("/^\d+$/", $this->request->request['id']) || (intval($this->request->request['id']) < 0)) return false;
		return true;
	}

	private function validate_set() {
		if ((!$this->user->hasPermission('modify', 'mta/mta') && !$this->user->hasPermission('modify', 'mta/mta_pds')) || !$this->validate_get() || !isset($this->request->request['scheme_id']) || !preg_match("/^\d+$/", $this->request->request['scheme_id']) || (intval($this->request->request['scheme_id']) < 0)) return false;
		return true;
	}
	
	private function _get_schemes() {						
		$_s = $this->model_mta_mta_product->getSchemes(array('fields' => array('mta_scheme_id as id', 'scheme_name as n')));
		$out = array();
		foreach($_s as $_s2) {
			$out[strval($_s2['id'])] = $_s2['n'];
		}
		return $out;
	}		

}

