<?php
	if($error_warning) {?>
<script type="text/javascript">alert('<?php echo $error_warning;?>');</script>
<?php }
		else {?>
<table class="form">		
	<?php foreach($types as $type) {?>
	<tr>
		<td>
			<?php echo $entries_set[$type];?>
		</td>
		<td>
		<select id="mta-<?php echo $type;?>-set">
			<option value="-1"><?php echo $option_select;?></option>
			<option value="0"><?php echo $option_default;?></option>
			<?php 
				foreach($schemes as $_id => $_name) {?>
			<option value="<?php echo $_id;?>"><?php echo $_name;?></option>
			<?php }?>
		</select>
		<input type="button" class="mta_set_save_button" value="<?php echo $button_set;?>" />		
		<img src="view/image/loading.gif" alt="" style="display:none" class="loading" />
		<span style="color:red;display:none" class="span_done"><?php echo $word_done;?></span>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $entries_save_default[$type];?>
		</td>
		<td>
		<select id="mta-<?php echo $type;?>-save_default">
			<option value="-1"><?php echo $option_select;?></option>
			<option value="0"><?php echo $option_default;?></option>
			<?php 
				foreach($schemes as $_id => $_name) {
					if(isset($schemes_default[$type]) && $schemes_default[$type] == $_id) {
				?>
			<option value="<?php echo $_id;?>" selected="selected"><?php echo $_name;?></option>
				<?php } else {?>
			<option value="<?php echo $_id;?>"><?php echo $_name;?></option>
			<?php }
			}?>		
		</select>
		<input type="button" class="mta_set_save_button" value="<?php echo $button_save_default;?>" />
		<img src="view/image/loading.gif" alt="" style="display:none" class="loading" />
		<span style="color:red;display:none" class="span_done"><?php echo $word_saved;?></span>		
		</td>
	</tr>		
<?php }?>		
</table>		
<script type="text/javascript">
	$('.mta_set_save_button').click(function() {
		var _this = this;
		var _p = $(_this).parent();
		var _s = $(_p).find('select[id^="mta-"]');
		if($(_s).val() < 0) return;
		var _s_id = $(_s).attr('id').split('-');
		var _type = _s_id[1];
		var _action = _s_id[2];
		if(!confirm(_action == 'set' ? '<?php echo $text_confirm_set;?>' : '<?php echo $text_confirm_save_default;?>')) return;
		$(_this).attr('disabled', true);
		$(_p).find('.loading').show();
		$('.span_done').hide();		
		var _url = _action == 'set' ? '<?php echo $url_set;?>' : '<?php echo $url_save_default;?>';
		$.post(_url, {type : _type, scheme_id : $(_s).val()}, function(_r) {
			$(_this).attr('disabled', false);
			$(_p).find('.loading').hide();
			if(parseInt(_r) > 0) {
				$(_p).find('.span_done').show();
				if(_action == 'set' || $(_s).val() == '0') $(_s).val('-1');
			} else {
				alert('<?php echo $alert_error;?>');
			}			
		});
	});	
</script>		
		
		
<?php }?>		