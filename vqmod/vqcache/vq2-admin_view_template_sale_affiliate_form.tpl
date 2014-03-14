<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/customer.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <div id="htabs" class="htabs"><a href="#tab-general"><?php echo $tab_general; ?></a> <a href="#tab-payment"><?php echo $tab_payment; ?></a>
        <?php if ($affiliate_id) { ?>
        <a href="#tab-transaction"><?php echo $tab_transaction; ?></a>
        <?php } ?>
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <div id="tab-general">
          <table class="form">

					<?php /* //+mod by yp start */ ?>
          	<tr><td>&nbsp;</td>
          		<td>  <?php echo $text_level;?>  <span style="font-weight:bold;" id="level_span"><?php echo $level;?></span></td>
          	</tr>
					<?php /* //+mod by yp end */ ?>

            <tr>
              <td><span class="required">*</span> <?php echo $entry_firstname; ?></td>
              <td><input type="text" name="firstname" value="<?php echo $firstname; ?>" />
                <?php if ($error_firstname) { ?>
                <span class="error"><?php echo $error_firstname; ?></span>
                <?php } ?></td>
            </tr>
            <tr>
              <td><span class="required">*</span> <?php echo $entry_lastname; ?></td>
              <td><input type="text" name="lastname" value="<?php echo $lastname; ?>" />
                <?php if ($error_lastname) { ?>
                <span class="error"><?php echo $error_lastname; ?></span>
                <?php } ?></td>
            </tr>
            <tr>
              <td><span class="required">*</span> <?php echo $entry_email; ?></td>
              <td><input type="text" name="email" value="<?php echo $email; ?>" />
                <?php if ($error_email) { ?>
                <span class="error"><?php echo $error_email; ?></span>
                <?php  } ?></td>
            </tr>

<?php /* //+mod by yp start */ ?>
            <tr><td><?php echo $entry_parent;?></td>
              <td id="parent_affiliate_td"><span><?php if($parent_affiliate_id) {?>
              	<a href="<?php echo $parent_affiliate_link;?>" target="_blank"><?php echo $parent_affiliate_name;?></a>
              	<?php
              } else {
              	echo $text_none;
              }?>
             	</span><input type="button" rel="#affiliate_dt_overlay_div" id="affiliate_dt_overlay_open" value="<?php echo $text_select_affiliate;?>" /> <input type="button" id="affiliate_set_none" value="<?php echo $text_set_none;?>" />
              <input type="hidden" name="parent_affiliate_id" value="<?php echo $parent_affiliate_id;?>" />
              <input type="hidden" name="parent_affiliate_name" value="<?php echo $parent_affiliate_name;?>" />
              </td>
            </tr>

            <tr>
              <td><?php echo $entry_scheme; ?></td>
              <td>
              <select name="scheme">
              	<option value="0"<?php if(!$scheme_id) {?> selected="selected"<?php }?>><?php echo $option_default;?></option>
              	<?php	
              		foreach($schemes as $_id => $_name) {?>
              	<option value="<?php echo $_id;?>"<?php if($scheme_id == $_id) {?> selected="selected"<?php }?>><?php echo $_name;?></option>
              		<?php }?>              		
              </select>
              </td>
            </tr>
<?php /* //+mod by yp end */ ?>            


            <tr>
              <td><span class="required">*</span> <?php echo $entry_telephone; ?></td>
              <td><input type="text" name="telephone" value="<?php echo $telephone; ?>" />
                <?php if ($error_telephone) { ?>
                <span class="error"><?php echo $error_telephone; ?></span>
                <?php  } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_fax; ?></td>
              <td><input type="text" name="fax" value="<?php echo $fax; ?>" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_company; ?></td>
              <td><input type="text" name="company" value="<?php echo $company; ?>" /></td>
            </tr>
            <tr>
              <td><span class="required">*</span> <?php echo $entry_address_1; ?></td>
              <td><input type="text" name="address_1" value="<?php echo $address_1; ?>" />
                <?php if ($error_address_1) { ?>
                <span class="error"><?php echo $error_address_1; ?></span>
                <?php  } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_address_2; ?></td>
              <td><input type="text" name="address_2" value="<?php echo $address_2; ?>" /></td>
            </tr>
            <tr>
              <td><span class="required">*</span> <?php echo $entry_city; ?></td>
              <td><input type="text" name="city" value="<?php echo $city; ?>" />
                <?php if ($error_city) { ?>
                <span class="error"><?php echo $error_city ?></span>
                <?php  } ?></td>
            </tr>
            <tr>
              <td><span id="postcode-required" class="required">*</span> <?php echo $entry_postcode; ?></td>
              <td><input type="text" name="postcode" value="<?php echo $postcode; ?>" />
                <?php if ($error_postcode) { ?>
                <span class="error"><?php echo $error_postcode ?></span>
                <?php  } ?></td>
            </tr>
            <tr>
              <td><span class="required">*</span> <?php echo $entry_country; ?></td>
              <td><select name="country_id">
                  <option value="false"><?php echo $text_select; ?></option>
                  <?php foreach ($countries as $country) { ?>
                  <?php if ($country['country_id'] == $country_id) { ?>
                  <option value="<?php echo $country['country_id']; ?>" selected="selected"> <?php echo $country['name']; ?> </option>
                  <?php } else { ?>
                  <option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select>
                <?php if ($error_country) { ?>
                <span class="error"><?php echo $error_country; ?></span>
                <?php } ?></td>
            </tr>
            <tr>
              <td><span class="required">*</span> <?php echo $entry_zone; ?></td>
              <td><select name="zone_id">
                </select>
                <?php if ($error_zone) { ?>
                <span class="error"><?php echo $error_zone; ?></span>
                <?php } ?></td>
            </tr>
            <tr>
              <td><span class="required">*</span> <?php echo $entry_code; ?></td>
              <td><input type="code" name="code" value="<?php echo $code; ?>"  />
                <?php if ($error_code) { ?>
                <span class="error"><?php echo $error_code; ?></span>
                <?php } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_password; ?></td>
              <td><input type="password" name="password" value="<?php echo $password; ?>"  />
                <?php if ($error_password) { ?>
                <span class="error"><?php echo $error_password; ?></span>
                <?php  } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_confirm; ?></td>
              <td><input type="password" name="confirm" value="<?php echo $confirm; ?>" />
                <?php if ($error_confirm) { ?>
                <span class="error"><?php echo $error_confirm; ?></span>
                <?php  } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_status; ?></td>
              <td><select name="status">
                  <?php if ($status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select></td>
            </tr>
          </table>
        </div>
        <div id="tab-payment">
          <table class="form">
            <tbody>
              <tr>
                <td><?php echo $entry_commission; ?></td>
                <td><input type="text" name="commission" value="<?php echo $commission; ?>" /></td>
              </tr>
              <tr>
                <td><?php echo $entry_tax; ?></td>
                <td><input type="text" name="tax" value="<?php echo $tax; ?>" /></td>
              </tr>
              <tr>
                <td><?php echo $entry_payment; ?></td>
                <td><?php if ($payment == 'cheque') { ?>
                  <input type="radio" name="payment" value="cheque" id="cheque" checked="checked" />
                  <?php } else { ?>
                  <input type="radio" name="payment" value="cheque" id="cheque" />
                  <?php } ?>
                  <label for="cheque"><?php echo $text_cheque; ?></label>
                  <?php if ($payment == 'paypal') { ?>
                  <input type="radio" name="payment" value="paypal" id="paypal" checked="checked" />
                  <?php } else { ?>
                  <input type="radio" name="payment" value="paypal" id="paypal" />
                  <?php } ?>
                  <label for="paypal"><?php echo $text_paypal; ?></label>
                  <?php if ($payment == 'bank') { ?>
                  <input type="radio" name="payment" value="bank" id="bank" checked="checked" />
                  <?php } else { ?>
                  <input type="radio" name="payment" value="bank" id="bank" />
                  <?php } ?>
                  <label for="bank"><?php echo $text_bank; ?></label></td>
              </tr>
            </tbody>
            <tbody id="payment-cheque" class="payment">
              <tr>
                <td><?php echo $entry_cheque; ?></td>
                <td><input type="text" name="cheque" value="<?php echo $cheque; ?>" /></td>
              </tr>
            </tbody>
            <tbody id="payment-paypal" class="payment">
              <tr>
                <td><?php echo $entry_paypal; ?></td>
                <td><input type="text" name="paypal" value="<?php echo $paypal; ?>" /></td>
              </tr>
            </tbody>
            <tbody id="payment-bank" class="payment">
              <tr>
                <td><?php echo $entry_bank_name; ?></td>
                <td><input type="text" name="bank_name" value="<?php echo $bank_name; ?>" /></td>
              </tr>
              <tr>
                <td><?php echo $entry_bank_branch_number; ?></td>
                <td><input type="text" name="bank_branch_number" value="<?php echo $bank_branch_number; ?>" /></td>
              </tr>
              <tr>
                <td><?php echo $entry_bank_swift_code; ?></td>
                <td><input type="text" name="bank_swift_code" value="<?php echo $bank_swift_code; ?>" /></td>
              </tr>
              <tr>
                <td><span class="required">*</span> <?php echo $entry_bank_account_name; ?></td>
                <td><input type="text" name="bank_account_name" value="<?php echo $bank_account_name; ?>" /></td>
              </tr>
              <tr>
                <td><span class="required">*</span> <?php echo $entry_bank_account_number; ?></td>
                <td><input type="text" name="bank_account_number" value="<?php echo $bank_account_number; ?>" /></td>
              </tr>
            </tbody>
          </table>
        </div>
        <?php if ($affiliate_id) { ?>
        <div id="tab-transaction">
          <table class="form">
            <tr>
              <td><?php echo $entry_description; ?></td>
              <td><input type="text" name="description" value="" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_amount; ?></td>
              <td><input type="text" name="amount" value="" /></td>
            </tr>
            <tr>
              <td colspan="2" style="text-align: right;"><a id="button-reward" class="button" onclick="addTransaction();"><span><?php echo $button_add_transaction; ?></span></a></td>
            </tr>
          </table>
          <div id="transaction"></div>
        </div>
        <?php } ?>
      </form>
    </div>
  </div>
</div>

<?php /* //+mod by yp start */ ?>
<div style="background-color:#efefef;border:2px solid #ababab;z-index:99999999;display:none;" id="affiliate_dt_overlay_div"><div style="text-align:right;padding-right:5px;"><a class="overlay_close"><?php echo $oy_close;?></a><div style="text-align:center;font-weight:bold;"><?php echo $oy_title;?><hr /></div><div style="height:400px;width:850px;overflow-y:auto;margin:3px;padding:5px;">
<table cellpadding="0" cellspacing="0" border="0" id="affiliate_dt">
	<thead>
		<tr>
			<th width="5%"><?php echo $oy_id;?></th>
			<th width="20%"><?php echo $oy_name;?></th>
			<th width="20%"><?php echo $oy_email;?></th>
			<th width="20%"><?php echo $oy_scheme;?></th>
			<th width="5%"><?php echo $oy_level;?></th>
			<th width="10%"><?php echo $oy_balance;?></th>			
			<th width="20%"><?php echo $oy_date_added;?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="7" class="dataTables_empty"><?php echo $oy_loading;?></td>

		</tr>
	</tbody>
	<tfoot>
		<tr>
			<th><?php echo $oy_id;?></th>
			<th><?php echo $oy_name;?></th>
			<th><?php echo $oy_email;?></th>
			<th><?php echo $oy_scheme;?></th>
			<th><?php echo $oy_level;?></th>
			<th><?php echo $oy_balance;?></th>			
			<th><?php echo $oy_date_added;?></th>
		</tr>
	</tfoot>
</table>
</div>
<div style="text-align:right;padding-right:5px;"><hr /><a class="overlay_close"><?php echo $oy_close;?></a></div>
</div>
<script type="text/javascript" src="view/javascript/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="view/javascript/jquery.tools.min.js"></script>

<script type="text/javascript"><!--
	
$(document).ready(function () { 
    $('#affiliate_dt').dataTable( {
        'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': document.location.href.replace(/sale\/affiliate(?:\/\w*)/, 'sale/mta_affiliate_dt'),
        'sServerMethod' : 'POST',
        'sPaginationType' : 'full_numbers',
        'iDisplayLength' : 10,
        'aoColumns': [
        		{ 'mDataProp': 'id', 'bSearchable' : false, 'bSortable' : false },
            { 'mDataProp': 'name' },
            { 'mDataProp': 'email' },
            { 'mDataProp': 'scheme', 'bSearchable' : false, 'bSortable' : false },            
            { 'mDataProp': 'level', 'bSearchable' : false},
            { 'mDataProp': 'balance', 'bSearchable' : false, 'bSortable' : false},
            { 'mDataProp': 'date_added', 'bSearchable' : false  }
        ],

        'fnDrawCallback': function(){            
            $('.affiliate_dt_row').css({'cursor' : 'pointer'});
            $('.affiliate_dt_row').click( function () {
            	$('#affiliate_dt_overlay_open').overlay().close();            	
              var _id = $(this).attr('id').split('-')[1];
              <?php if($affiliate_id) {?>
              if(_id == <?php echo $affiliate_id;?>) return;
            	<?php }?>
              var _name = $($(this).find('td')[1]).text();
              $('#parent_affiliate_td span').empty();
			  var _href = document.location.href.replace(/affiliate_id=\d+/, 'affiliate_id='+_id);
			  if(_href.indexOf('affiliate_id') == -1) _href += '&affiliate_id='+_id;
              var _a = $('<a/>').attr('href', _href).attr('target', '_blank').text(_name).appendTo($('#parent_affiliate_td span')); 
              $('input[name="parent_affiliate_id"]').val(_id);  
              $('input[name="parent_affiliate_name"]').val(_name);  
              $('#level_span').text(parseInt($($(this).find('td')[4]).text()) + 1);             
            });
        }        
    } );
    
$('#affiliate_set_none').click(function() {
	$('#parent_affiliate_td span').html('<?php echo mta_jsstr($text_none);?>');
	$('input[name="parent_affiliate_id"]').val('0');
	$('input[name="parent_affiliate_name"]').val('');
	$('#level_span').text('1');
});             	
    
$('#affiliate_dt_overlay_open').overlay({
        	mask: {
        		color: '#efefef',
            loadSpeed: 200,
            zIndex: '999999959',
        		opacity: 0.9
        	},
        	closeOnClick: false
      });
      $('.overlay_close').click(function() {
      	$('#affiliate_dt_overlay_open').overlay().close();
      });      
});
//--></script>
<?php /* //+mod by yp end */ ?>


<script type="text/javascript"><!--
$('select[name=\'country_id\']').bind('change', function() {
	$.ajax({
		url: 'index.php?route=sale/affiliate/country&token=<?php echo $token; ?>&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('select[name=\'payment_country_id\']').after('<span class="wait">&nbsp;<img src="view/image/loading.gif" alt="" /></span>');
		},
		complete: function() {
			$('.wait').remove();
		},			
		success: function(json) {
			if (json['postcode_required'] == '1') {
				$('#postcode-required').show();
			} else {
				$('#postcode-required').hide();
			}
			
			html = '<option value=""><?php echo $text_select; ?></option>';
			
			if (json != '' && json['zone'] != '') {
				for (i = 0; i < json['zone'].length; i++) {
        			html += '<option value="' + json['zone'][i]['zone_id'] + '"';
	    			
					if (json['zone'][i]['zone_id'] == '<?php echo $zone_id; ?>') {
	      				html += ' selected="selected"';
	    			}
	
	    			html += '>' + json['zone'][i]['name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
			}
			
			$('select[name=\'zone_id\']').html(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$('select[name=\'country_id\']').trigger('change');
//--></script> 
<script type="text/javascript"><!--
$('input[name=\'payment\']').bind('change', function() {
	$('.payment').hide();
	
	$('#payment-' + this.value).show();
});

$('input[name=\'payment\']:checked').trigger('change');
//--></script> 
<script type="text/javascript"><!--
$('#transaction .pagination a').live('click', function() {
	$('#transaction').load(this.href);
	
	return false;
});			

$('#transaction').load('index.php?route=sale/affiliate/transaction&token=<?php echo $token; ?>&affiliate_id=<?php echo $affiliate_id; ?>');

function addTransaction() {
	$.ajax({
		url: 'index.php?route=sale/affiliate/transaction&token=<?php echo $token; ?>&affiliate_id=<?php echo $affiliate_id; ?>',
		type: 'post',
		dataType: 'html',
		data: 'description=' + encodeURIComponent($('#tab-transaction input[name=\'description\']').val()) + '&amount=' + encodeURIComponent($('#tab-transaction input[name=\'amount\']').val()),
		beforeSend: function() {
			$('.success, .warning').remove();
			$('#button-transaction').attr('disabled', true);
			$('#transaction').before('<div class="attention"><img src="view/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
		},
		complete: function() {
			$('#button-transaction').attr('disabled', false);
			$('.attention').remove();
		},
		success: function(html) {
			$('#transaction').html(html);
			
			$('#tab-transaction input[name=\'amount\']').val('');
			$('#tab-transaction input[name=\'description\']').val('');
		}
	});
}
//--></script> 
<script type="text/javascript"><!--
$('.htabs a').tabs();
//--></script> 

			
<?php /* //+mod by yp start awf */ ?>
<script type="text/javascript"><!--
	$(document).ready(function() {
		if($('[name="website"]').length < 1) {
			$('[name="code"]').parent().parent().before('<tr><td><?php echo $entry_website; ?></td><td><input type="text" name="website" value="<?php echo $website; ?>"  /></td></tr>');	
		}	
	});
//--></script> 
<?php /* //+mod by yp end awf */ ?>


<?php echo $footer; ?>