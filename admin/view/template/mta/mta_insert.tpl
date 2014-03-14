<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/customer.png" alt="" /> <?php echo $heading_title_insert; ?></h1>
      <div class="buttons"><a id="form_submit_button" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $insert; ?>" method="post" enctype="multipart/form-data" id="form">
        <div id="tab-general">
          <table class="form">
            <tr>
              <td><span class="required">*</span> <?php echo $entry_name; ?></td>
              <td><input name="name" value="" maxlength="100" />
							</td>
            </tr>
            <tr>
              <td><?php echo $entry_description; ?></td>
              <td>
              <textarea name="description" cols="30" rows="3"></textarea>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_is_default; ?></td>
              <td>
                <input type="radio" name="is_default" value="1" />
                <?php echo $text_yes; ?>
                <input type="radio" name="is_default" value="0" checked="checked" />
                <?php echo $text_no; ?>
               </td>
            </tr>
            <tr>
              <td><?php echo $entry_tiers; ?></td>
              <td>
              	<select name="max_levels">
              		<option value="1" selected="selected">1</option>
              		<?php for($__i = 2; $__i <= 50; $__i++) {?>
              		<option value="<?php echo $__i;?>"><?php echo $__i;?></option>
              	<?php }?>
              	</select>	
              </td>
            </tr>              
            <tr>
              <td><?php echo $entry_commission_type; ?></td>
              <td>
                <input type="radio" name="commission_type" value="percentage" checked="checked" />
                <?php echo $text_percent; ?>
                <input type="radio" name="commission_type" value="fixed" />
                <?php echo $text_amount; ?>
								</td>
            </tr>      
            <tr>
              <td><?php echo $entry_before_shipping; ?></td>
              <td>
                <input type="radio" name="before_shipping" value="1" checked="checked" />
                <?php echo $text_yes; ?>
                <input type="radio" name="before_shipping" value="0" />
                <?php echo $text_no; ?>
								</td>
            </tr> 
            <tr>
              <td><a onclick="javascript:$('#eternal_description').toggle();" title="Click here for explanation"><?php echo $entry_eternal; ?></a><div id="eternal_description" style="display:none"><?php echo $text_eternal_description;?></div></td>
              <td>
              	<select name="eternal">
              		<option value="0" selected="selected"><?php echo $text_no;?></option>
              		<?php for($__i = 1; $__i <= 50; $__i++) {?>
              		<option value="<?php echo $__i;?>"><?php echo $__i;?></option>
              	<?php }?>
              	</select>	
              </td>
            </tr>
            <tr>
            	<td><?php echo $column_commission; ?><br /><?php echo $entry_autoadd;?></td>
            	<td>
            		<div style="padding:5px;">
            			<table class="list" id="commission_table">
            				<thead>
            					<tr>
            						<td>&nbsp;</td>
												<td class="left" id="tier_level_td_1"><?php echo $entry_level;?> 1</td>
											</tr>
										</thead>            		
										<tbody>
											<tr class="tier_tr" id="tier_tr_1">
												<td class="right">1-<?php echo $entry_tier;?>
													<div id="total_div_1">10.00%</div>
												</td>
												<td class="left" id="tier_td_1_1">
													<?php echo $column_commission;?>:<br />
													<input type="text" name="tiers1_level1_commission" value="10.00" /><br />
													<?php echo $column_autoadd;?>:<br />
													<input type="radio" name="tiers1_level1_autoadd" value="1" checked="checked" /><?php echo $text_yes;?>
													<input type="radio" name="tiers1_level1_autoadd" value="0"><?php echo $text_no;?>													
												</td>
											</tr>
										</tbody>
									</table>
								</div>
            	</td>
            </tr>
            <tr>
            	<td><?php echo $entry_autoapprove;?></td>
            	<td>
            		<div style="padding:5px;">
            			<table class="list" id="autoapprove_table">
            				<tbody>
            					<tr id="autoapprove_tr_1">
            						<td class="right" style="width:50px;"><?php echo $entry_level;?> 1</td>
            						<td class="left">
													<input type="radio" name="level1_autoapprove" value="1" checked="checked" /><?php echo $text_yes;?>
													<input type="radio" name="level1_autoapprove" value="0" /><?php echo $text_no;?>													            							
            						</td>
            					</tr>
            				</tbody>
									</table>
								</div>
							</td>
						</tr>
          </table>
        </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" src="view/javascript/mta.util.js"></script>
<script type="text/javascript"><!--
	
$(document).ready(function () { 
	
	var _num_levels = 1;
	var _waiting = false;
	
	var failform = function(msg) {
		alert(msg);
		_waiting = false;
		return false;
	}
	
	var get_num_levels = function() {
		_num_levels = parseInt($('select[name="max_levels"]').find('option:selected').val());
		return _num_levels;
	}
	
	_num_levels = get_num_levels();
	
	var tpl = {
		empty_td : $('<td style="background-color:#dedede;">&nbsp;</td>'),
		tier_level_td : $('#tier_level_td_1').clone(),
		tier_tr : $('#tier_tr_1').clone(),
		tier_td : $('#tier_td_1_1').clone(),		
		autoapprove_tr : $('#autoapprove_tr_1').clone()		
	}	
	$(tpl.tier_tr).find('td:last').remove();
	
	var sprintf04f = MTA_UTIL.sprintf04f;
	
	var calc_total = function(tier) {
		var _total = 0;
		$('input[name^="tiers'+tier+'_"][type="text"]').each(function() {
			var _v = parseFloat($(this).val());			
			if(_v) _total += _v;			
		});		
		return sprintf04f(_total);
	}	
	
	var set_total = function(tier) {
		var _txt = $('div[id="total_div_'+tier+'"]').text();
		$('div[id="total_div_'+tier+'"]').text(_txt.replace(/\d+\.\d+/, calc_total(tier)));
	}
	
	var change_tl = function() {
			$(this).val(sprintf04f($(this).val()));
			var _tier = $(this).attr('name').match(/^tiers(\d+)/)[1];			
			set_total(_tier);
	}		
	
		$('#form_submit_button').click(function() {
			if(_waiting === true) return;
			_waiting = true;
			
			$('input[name="name"]').val($('input[name="name"]').val().replace(/^\s+/, '').replace(/\s+$/, ''));
			var _nl  = $('input[name="name"]').val().length;
			if(_nl < 3 || _nl > 100) return failform('<?php echo mta_jsstr($error_name);?>');			
			
			var _cms = $('input[name^="tiers"][type="text"]');
			for(var i = 0;i < _cms.length; i++) {
				if(!(/^\d+(\.\d{1,4})?$/.test($(_cms[i]).val()))) return failform('<?php echo mta_jsstr($error_invalid_commission);?>');				
			}			
			
      if($('input[name="commission_type"]:checked').val() == 'percentage') {      	
      	_num_levels = get_num_levels();
      	for(var _i = 1; _i <= _num_levels; _i++) {
      		if(calc_total(_i) > 100 || calc_total(_i) < 0) return failform('<?php echo mta_jsstr($entry_tier);?> '+_i+': <?php echo mta_jsstr($error_invalid_percent);?>');    	  	
      	}
      }
      
      $.get(document.location.href.replace('insert', 'check_scheme_name')+'&name='+encodeURIComponent($('input[name="name"]').val()), function(r) {
      	if(!(parseInt(r))) return failform('<?php echo mta_jsstr($error_duplicate_name);?>');

      	jQuery.post(
      		document.location.href+'&',      		
      		$('#form').serialize(),
      		function(d) {
      			if(d == '1') {
      				document.location.href = document.location.href.replace('/insert', '');
      				return;
      			}
      			return failform(d);
      		},
      		'text'
      	);      	
      });      
		});		

		$('input[name^="tiers"][type="text"]').change(change_tl);
		
		$('select[name="max_levels"]').change(function() {
			var _old_num_levels = _num_levels;
			_num_levels = get_num_levels();
			if(_num_levels > _old_num_levels) {				
								
				for(var i = (_old_num_levels + 1); i <= _num_levels; i++) {
					var _tltd = tpl.tier_level_td.clone();
					_tltd.attr('id', 'tier_level_td_'+i);
					_tltd.text(_tltd.text().replace(/\d+$/, i));
					$('#commission_table thead tr').append(_tltd);
					
					var _ttr = tpl.tier_tr.clone();
					_ttr.attr('id', 'tier_tr_'+i);
					$($(_ttr).find('td')).html($($(_ttr).find('td')).html().replace(/\d+/, i));					
					if($('input[name="commission_type"]:checked').val() != 'percentage') $($(_ttr).find('#total_div_1')).text('$'+$($(_ttr).find('#total_div_1')).text().replace('%', ''));
					$($(_ttr).find('#total_div_1')).attr('id', 'total_div_'+i);
					
					
					$('#commission_table tbody').append(_ttr);
					for(var ii = 1; ii <= i; ii++) {						
						var _ttd = tpl.tier_td.clone();
						_ttd.attr('id', 'tier_td_'+i+'_'+ii);
						$($(_ttd).find('input[name^="tiers"]')).each(function() {
							$(this).attr('name', $(this).attr('name').replace(/^tiers\d+_level\d+/, 'tiers'+i+'_level'+ii));
						});
						$('#tier_tr_'+i).append(_ttd);
					}
					
					for(var i2 = 1; i2 < i; i2++) {						
						$('#tier_tr_'+i2).append(tpl.empty_td.clone());						
					}
					
					var _atr = tpl.autoapprove_tr.clone();
					var _atr_td = $(_atr.find('td:first'));
					_atr_td.text(_atr_td.text().replace(/ \d+$/, ' '+i));
					$(_atr.find('input[name^="level"]')).attr('name', 'level'+i+'_autoapprove');
					_atr.attr('id', 'autoapprove_tr_'+i);
					$('#autoapprove_table').append(_atr);
					set_total(i);
				}	
				$('input[name^="tiers"][type="text"]').change(change_tl);
							
			} else if(_num_levels < _old_num_levels) {
				for(var i = _old_num_levels; i > _num_levels; i--) {
					$('#autoapprove_tr_'+i).remove();
					$('#tier_tr_'+i).remove();					
					$('#tier_level_td_'+i).remove();
					$($('tr[id^="tier_tr"]').find('td:last')).remove();
				}
				var _s = $('select[name="eternal"]').find('option:selected');
				if(_s.val() > _num_levels) {
					_s.attr('selected', false);
					$('select[name="eternal"]').find('option[value="0"]').attr('selected', true);
				}
			}			
		});
		
		$('select[name="eternal"]').change(function() {
			if($(this).find('option:selected').val() > _num_levels) {
				$(this).find('option:selected').attr('selected', false);
				$(this).find('option[value="0"]').attr('selected', true);
			}
		});
		
		$('input[name="commission_type"]').change(function(){			
			if($('input[name="commission_type"]:checked').val() == 'percentage') {
				$('input[name="is_default"]').parent().parent().show();
				$('input[name="before_shipping"]').parent().parent().show();				
				$('div[id^="total_div_"]').each(function() {
					$(this).text($(this).text().replace('$', '')+'%');
				});
			} else {
				$('input[name="is_default"]').parent().parent().show();
				$('input[name="before_shipping"]').parent().parent().show();								
				$('div[id^="total_div_"]').each(function() {
					$(this).text('$'+$(this).text().replace('%', ''));
				});
			}			
		});
		
});	
//--></script>	
<?php echo $footer; ?>