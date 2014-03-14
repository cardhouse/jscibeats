<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($unconverted_affiliates || $error_no_default || $error_warning) { ?>
  <div class="warning">
  	<?php	if ($unconverted_affiliates) {?>
  	<div id="unconverted_div">
  	<?php
  		echo $text_unconverted_affiliates;?> - <strong><?php echo $unconverted_affiliates;?></strong><br />
  	<?php 
  		if($error_no_default_unconverted) {
  			echo $error_no_default_unconverted;
  		} else {?>
  		<a id="convert_affiliates"><?php echo $text_convert_affiliates;?></a>
  	<?php
  		}?>
  	</div>
  	<?php
  	}
  	if(!$error_no_default_unconverted || $error_no_default) echo $error_no_default . '<br />';
  	if($error_warning) echo $error_warning;?>  	
  </div>
  <?php } ?>  
  <div class="success" style="display:none";></div>  
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/customer.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="location = '<?php echo $insert; ?>'" class="button"><?php echo $button_insert; ?></a></div>
    </div>
    <div class="content">
	<div style="padding-left:10px;"><a href="javascript:;" onclick="javascript:$('#settings_div').toggle();"><?php echo $text_settings;?></a></div>
	<div id="settings_div" style="display:none">
		<table class="form">
			<tr>
				<td><?php echo $column_settings_tracking;?></td>
				<td>
				<input type="radio" name="mta_ypx_no_aff_in_cust_acc" value="0"<?php if(!$this->config->get('mta_ypx_no_aff_in_cust_acc')) {?> checked="checked"<?php }?> /> <?php echo $text_settings_tracking_permanent;?><br />
				<input type="radio" name="mta_ypx_no_aff_in_cust_acc" value="1"<?php if($this->config->get('mta_ypx_no_aff_in_cust_acc')) {?> checked="checked"<?php }?> /> <?php echo $text_settings_tracking_cookies;?>
				</td>
			</tr>
			<tr>
				<td><?php echo $column_settings_no_shipping;?></td>
				<td>
				<input type="radio" name="mta_ypx_no_shipping" value="0"<?php if(!$this->config->get('mta_ypx_no_shipping')) {?> checked="checked"<?php }?> /> <?php echo $text_settings_no_shipping_default;?><br />				
				<input type="radio" name="mta_ypx_no_shipping" value="any"<?php if($this->config->get('mta_ypx_no_shipping') && $this->config->get('mta_ypx_no_shipping') == 'any') {?> checked="checked"<?php }?> /> <?php echo $text_settings_no_shipping_any;?><br />				
				<input type="radio" name="mta_ypx_no_shipping" value="subtotal"<?php if($this->config->get('mta_ypx_no_shipping') && $this->config->get('mta_ypx_no_shipping') == 'subtotal') {?> checked="checked"<?php }?> /> <?php echo $text_settings_no_shipping_subtotal;?>			
				</td>
			</tr>
			<tr>
				<td><?php echo $column_settings_autoadd_statuses;?></td>
				<td>
				<?php
					foreach($order_statuses as $_i => $_order_status) {?>
					<input type="checkbox" name="mta_ypx_autoadd_statuses[<?php echo $_i;?>]" value="<?php echo $_order_status['order_status_id'];?>"<?php if(in_array($_order_status['order_status_id'], $autoadd_statuses)) {?> checked="checked"<?php }?> /><?php echo $_order_status['name'];?><br />
					<?php }?>
				</td>
			</tr>
			<tr>
				<td><?php echo $column_settings_llaff_priority;?></td>
				<td>
				<input type="radio" name="mta_ypx_llaff_priority" value="0"<?php if(!$this->config->get('mta_ypx_llaff_priority')) {?> checked="checked"<?php }?> /> <?php echo $text_settings_llaff_higher;?><br />
				<input type="radio" name="mta_ypx_llaff_priority" value="1"<?php if($this->config->get('mta_ypx_llaff_priority')) {?> checked="checked"<?php }?> /> <?php echo $text_settings_llaff_lower;?><br />
				</td>
			</tr>				
		</table>
		<div>
			<a class="button" id="button_save_settings"><?php echo $button_save_settings;?></a>
			<img src="view/image/loading.gif" class="loading" style="padding-left: 5px;display:none;" />
		</div>
	</div><br />
<?php	
	if(!sizeof($schemes)) {		
		echo $text_no_results;    	
	} else {?>
        <table class="list">
          <thead>
            <tr>
            	<td class="left"><?php echo $column_name; ?></td>
            	<td class="left"><?php echo $column_commission; ?></td>
            	<td class="left"><?php echo $column_code; ?></td>   
              <td class="right"><?php echo $column_action; ?></td>
            </tr>
          </thead>
          <tbody>            
            <?php foreach ($schemes as $s) { ?>
            <tr>
							<td class="left">
								<?php if($s['is_default']) {?>
								<div style="text-align:right;"><small><?php echo $text_default;?></small></div>
								<?php }?>
								<div><?php echo $s['name'];?></div>
								<div class="help"><?php echo $s['description'];?></div>
								<?php if($s['user_total']) {?>
								<div>
									<a class="show_usage_link"><?php echo $text_show_usage;?> (<?php echo $s['user_total'];?>)</a>
									<a class="hide_usage_link" style="display:none;"><?php echo $text_hide;?></a>
									<div class="usage" style="display:none">
										<?php foreach($user_types as $_k => $_v) {
											if($s['user_count'][$_k]) {?>										
										<input type="checkbox" value="<?php echo $_v;?>" /><?php echo $user_types_lng[$_k];?> :  <strong><?php echo $s['user_count'][$_k];?></strong><br />										
										<?php }
										}
										if($num_schemes > 1) {
											echo $text_move_users;?><br />											
											<select class="move_select_scheme">
												<option value="">Select scheme ...</option>
												<?php
													for($__i = 0; $__i < sizeof($s_opts); $__i++) {?>
													<option value="<?php echo $s_opts[$__i]['id'];?>"><?php echo $s_opts[$__i]['name'];?></option>
												<?php }?>
											</select><br />
											<input type="hidden" class="this_scheme_id" value="<?php echo $s['id'];?>" />
											<input type="hidden" class="move_id_input" value="" />
											<div class="move_name_div" style="display:none;">
												<input type="text" class="move_name_input" size="20" value="" readonly="readonly" />
											</div>
											<input type="button" class="move_button" id="mb_<?php echo $s['id'];?>" value="<?php echo $text_move;?>" />										
										<?php }?>	
									</div>
								</div>
							<?php }?>								
							</td>
							<td class="left">
								<?php echo $s['max_levels'];?>-<?php echo $entry_tier;?>, <?php echo ($s['commission_type'] == 'fixed' ? $text_amount : $text_percent);?><hr />
							<?php echo $s['commissions'];?>
							</td>
							<td class="left">
								<input type="text" size="20" value="<?php echo $s['signup_code'];?>" readonly="readonly" /><br />
								<small>add </small><strong>&amp;mta=<?php echo $s['signup_code'];?></strong><small> to any shop URL&#039;s query part to get new affiliate signed under this scheme</small>
							</td>
							<td class="left">
								[ <a href="javascript:;" class="copy_scheme_link"><?php echo $text_copy_scheme;?></a> ]
								<div style="padding-left:50px;display:none;">
								<?php echo $entry_new_name;?>:<br />
								<input type="hidden" class="this_scheme_id" value="<?php echo $s['id'];?>" />
								<input type="text" class="new_scheme_name" value="" size="30" /><br />
								<input type="button" class="copy_scheme_button" value="<?php echo $text_copy_scheme;?>" />
								</div><br />								
								[ <a href="<?php echo $s['edit_link']['href'];?>"><?php echo $s['edit_link']['text'];?></a> ]
								<?php if(!$s['is_default']) {?>
								<br />
								[ <a class="delete_link" id="dl_<?php echo $s['id'];?>"><?php echo $button_delete;?></a> ]
								<?php }?>
							</td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
<?php }?>
    </div>
  </div>
</div>
<script type="text/javascript" src="view/javascript/mta.util.js"></script>
<script type="text/javascript"><!--
	
$(document).ready(function () { 

	var _all_fixed = <?php echo $all_fixed;?>;

	var _cb = function(r) {
		//standard response callback
		r = parseInt(r);
		if(r) {
			alert('<?php echo mta_esqq($text_success);?>');
			document.location.reload();
		} else {
			alert('<?php echo mta_jsstr($error_invalid_request);?>');
		}		
	}
	
	$('.show_usage_link').click(function() {
		$(this).hide();
		$($(this).parent().find('.usage')).show();
		$($(this).parent().find('.hide_usage_link')).show();
	});
	
	$('.hide_usage_link').click(function() {
		$(this).hide();
		$($(this).parent().find('.usage')).hide();
		$($(this).parent().find('.show_usage_link')).show();
	});
	
	$('#convert_affiliates').click(function() {
		$.get(document.location.href.replace('mta/mta', 'mta/mta/convert_affiliates'), function(r) {
			r = parseInt(r);
			if(r) {
				$('#unconverted_div').hide();
				$('.success').text('<?php echo mta_jsstr($text_convert_success);?>').show();
				$('.warning').hide();
			} else {
				alert('<?php echo mta_jsstr($error_invalid_request);?>');
			}
		});		
	});
	
	$('.delete_link').click(function() {
		if(!confirm('<?php echo mta_jsstr($text_delete_scheme_warning);?>')) return;
		var _id = $(this).attr('id');
		_id = _id.split('_')[1];
		$.post(document.location.href.replace('mta/mta', 'mta/mta/delete'), {'id' : _id, 'really_do_delete' : (parseInt(_id) * 3 + 517)}, _cb);		
	});
	
	$('.copy_scheme_link').click(function() {
		$(this).parent().find('div:first').toggle();
	});
	
	$('.copy_scheme_button').click(function() {
		var _name = $(this).parent().find('.new_scheme_name').val();
		if(!_name) return;
		var _id = $(this).parent().find('.this_scheme_id').val();
		$.post(document.location.href.replace('mta/mta', 'mta/mta/copy'), {'id' : _id, 'name' : _name}, _cb);
	});
	
	$('.move_button').click(function() {
		var _new_id = $(this).parent().find('.move_id_input').val();
		if(!_new_id) return;
		_types = [];
		$($(this).parent().find('input:checked')).each(function() {
			if($(this).val() == 'a' && $.inArray(parseInt(_new_id), _all_fixed) != -1) {
				//alert('<?php echo mta_jsstr($error_fixed_aff_commission);?>');
				//return;
			}
			_types.push($(this).val());
		});
		if(!_types.length) return;
		$.post(document.location.href.replace('mta/mta', 'mta/mta_set_scheme/move'), {
			'from' : $(this).attr('id').split('_')[1],
			'to' : _new_id,
			'types' : _types.join(',')		
		}, _cb);		
	});
	
	$('.move_select_scheme').change(function() {				
		var _id	= $(this).find('option:selected').val();
		if(_id == $($(this).parent().find('.this_scheme_id')).val()) {
			$(this).find('option[value="'+_id+'"]').attr('selected', false);
			$(this).find('option[value="0"]').attr('selected', true);
			return;
		}
		var _name = $(this).find('option:selected').text();
		$(this).parent().find('.move_id_input').val(_id);
		$(this).parent().find('.move_name_input').val(_id ? _name : '');
		$(this).parent().find('.move_name_div').show();
		return true;
	});
	
	var _saving_settings = false;
	$('#button_save_settings').click(function() {
		if(_saving_settings) return;
		_saving_settings = true;
		$('.loading').show();		
		var _data = {};
		$('input[name^="mta_ypx_"]:checked').each(function() {
			_data[$(this).attr('name')] = $(this).attr('value');
		});
		$.post(document.location.href.replace('mta/mta', 'mta/mta/save_settings'), _data, function() {
			_saving_settings = false;
			$('.loading').hide();
		});	
	});
	
});

//--></script>
<?php echo $footer; ?>