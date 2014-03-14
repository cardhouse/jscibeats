<?php

function mta_array_a2n($ar) {
	ksort($ar);
	return array_values($ar);
}

function mta_jsbin($str) {
	$new_str = '';
	for($i = 0; $i < strlen($str); $i++) {
		$_ord = @ord(substr($str, $i, 1));
		if($_ord) $new_str .= '\\x' . dechex($_ord);
	}
	return $new_str;
}

function mta_jsstr($str) {
	return str_replace(array("\r\n", "\r", "\n"), array('\\n', '\\n', '\\n'), addslashes(html_entity_decode($str, ENT_QUOTES, 'UTF-8')));
}

function mta_float2($v) {
	return sprintf('%.02f', $v);
}

function mta_float4($v) {
	return sprintf('%.04f', $v);
}

function mta_esqq($v) {
	return addcslashes($v, "'");
}

function mta_tpl($str, $data) {
	function ___add_p($_str) {
		return '[[' . $_str . ']]';
	}	
	return str_replace(array_merge(array_map('___add_p', array_keys($data)), array('[[', ']]')), array_merge(array_values($data), array('', '')), $str);	
}

function mta_check_bool($v) {
	return (is_bool($v) || preg_match("/^[01]$/", $v));
}

function mta_check_int($v) {
	return preg_match("/^\d+$/", $v);
}
	
function mta_check_float($v) {
	return preg_match("/^\d+(\.\d+)?$/", $v);
}

function mta_find_all_parents($ar) {

	foreach($ar as $k => $v){
		$ar[intval($k)] = $v ? array(intval($v)) : 0;
	}	
	
	function _atree(&$ar, &$out, $a, $v) {
		$ar[$a][] = $v;
		if(!isset($ar[$v])) {
			if($out[$v]) $ar[$a] = array_merge($ar[$a], $out[$v]);
		} else {
			if($ar[$v]) _atree($ar, $out, $a, $ar[$v]);								
		}
	}	

	$ark = array_keys($ar);
	foreach($ark as $a) {
		if(!isset($ar[$a])) continue;
		$p = $ar[$a];
		if($p) {
			if(!isset($ar[$p[0]])) {
				if(isset($out[$p[0]]) && $out[$p[0]]) {
					$out[$a] = array_merge($ar[$a], $out[$p[0]]); 
				} else {
					$out[$a] = $p;
				}
				unset($ar[$a]);
				continue;					
			} else if($ar[$p[0]]) {
				_atree($ar, $out, $a, $ar[$p[0]][0]);								
			}
			$_sz = sizeof($ar[$a]);
			for($i = 0; $i < $_sz; $i++) {
				$i2 = $i + 1;
				if($i2 < $_sz) {
					$out[$ar[$a][$i]] = array_slice($ar[$a], $i2, $_sz - $i2);
				} else {
					$out[$ar[$a][$i]] = 0;
				}
				unset($ar[$ar[$a][$i]]);
			}
			$out[$a] = $ar[$a];
		} else {
			$out[$a] = 0;
		}
		unset($ar[$a]);
	}
	return $out;
}	

function mta_is_email($email) {
	if (!preg_match("|^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+$|", $email)) return false;
	return true;
}

function mta_aff_link_script($code) {
	return '
	<script language="javascript">
	$(document).ready(function() {
		$("#footer").prepend(\'<div style="padding-right:3px;text-align:right;font-weight:bold;"><div style="display:none;padding-bottom:3px;color:#c43;">\'+document.location.href + (document.location.href.indexOf(\'?\') == -1 ? \'?\' : \'&amp;\') + \'tracking=' . $code . ' &nbsp; </div><a href="javascript:;" onclick="javascript:$(this).parent().find(\\\'div\\\').toggle();"><img src="catalog/view/theme/default/image/link.png" /></a></div>\');		
	});
	</script>
	';
}

