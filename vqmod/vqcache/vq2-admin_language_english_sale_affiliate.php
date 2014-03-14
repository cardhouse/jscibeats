<?php
// Heading
$_['heading_title']             = 'Affiliate';

// Text
$_['text_success']              = 'Success: You have modified affiliates!';
$_['text_approved']             = 'You have approved %s accounts!';
$_['text_wait']                 = 'Please Wait!';
$_['text_balance']              = 'Balance:';
$_['text_cheque']               = 'Cheque';
$_['text_paypal']               = 'PayPal';
$_['text_bank']                 = 'Bank Transfer';

// Column
$_['column_name']               = 'Affiliate Name';
$_['column_email']              = 'E-Mail';
$_['column_code']               = 'Tracking Code';
$_['column_balance']            = 'Balance';
$_['column_status']             = 'Status';
$_['column_approved']           = 'Approved';
$_['column_date_added']         = 'Date Added';
$_['column_description']        = 'Description';
$_['column_amount']             = 'Amount';
$_['column_action']             = 'Action';

// Entry
$_['entry_firstname']           = 'First Name:';
$_['entry_lastname']            = 'Last Name:';
$_['entry_email']               = 'E-Mail:';
$_['entry_telephone']           = 'Telephone:';
$_['entry_fax']                 = 'Fax:';
$_['entry_status']              = 'Status:';
$_['entry_password']            = 'Password:';
$_['entry_confirm']             = 'Confirm:';
$_['entry_company']             = 'Company:';
$_['entry_address_1']           = 'Address 1:';
$_['entry_address_2']           = 'Address 2:';
$_['entry_city']                = 'City:';
$_['entry_postcode']            = 'Postcode:';
$_['entry_country']             = 'Country:';
$_['entry_zone']                = 'Region / State:';
$_['entry_code']                = 'Tracking Code:<span class="help">The tracking code that will be used to track referrals.</span>';
$_['entry_commission']          = 'Commission (%):<span class="help">Percentage the affiliate recieves on each order.</span>';
$_['entry_tax']                 = 'Tax ID:';
$_['entry_payment']             = 'Payment Method:';
$_['entry_cheque']              = 'Cheque Payee Name:';
$_['entry_paypal']              = 'PayPal Email Account:';
$_['entry_bank_name']           = 'Bank Name:';
$_['entry_bank_branch_number']  = 'ABA/BSB number (Branch Number):';
$_['entry_bank_swift_code']     = 'SWIFT Code:';
$_['entry_bank_account_name']   = 'Account Name:';
$_['entry_bank_account_number'] = 'Account Number:';
$_['entry_amount']              = 'Amount:';
$_['entry_description']         = 'Description:';

// Error
$_['error_permission']          = 'Warning: You do not have permission to modify affiliates!';
$_['error_exists']              = 'Warning: E-Mail Address is already registered!';
$_['error_firstname']           = 'First Name must be between 1 and 32 characters!';
$_['error_lastname']            = 'Last Name must be between 1 and 32 characters!';
$_['error_email']               = 'E-Mail Address does not appear to be valid!';
$_['error_telephone']           = 'Telephone must be between 3 and 32 characters!';
$_['error_password']            = 'Password must be between 4 and 20 characters!';
$_['error_confirm']             = 'Password and password confirmation do not match!';
$_['error_address_1']           = 'Address 1 must be between 3 and 128 characters!';
$_['error_city']                = 'City must be between 2 and 128 characters!';
$_['error_postcode']            = 'Postcode must be between 2 and 10 characters for this country!';
$_['error_country']             = 'Please select a country!';
$_['error_zone']                = 'Please select a region / state!';
$_['error_code']                = 'Tracking Code required!';

if(!isset($_['entry_website'])) $_['entry_website'] = 'Web Site:';//+mod by yp awf



//+mod by yp start
$_['text_level'] = 'Level';
$_['text_default'] = '<small>(Default)</small>';
$_['option_default'] = 'Default';
$_['text_none'] = 'None';
$_['text_select_affiliate'] = 'Select Affiliate';
$_['text_set_none'] = 'Set to None';

$_['oy_close'] = 'Close';
$_['oy_title'] = 'Affiliates - Click to Select';
$_['oy_loading'] = 'Loading data from server ...';
$_['oy_id'] = 'ID';
$_['oy_name'] = 'Name';
$_['oy_email'] = 'Email';
$_['oy_scheme'] = 'Scheme';
$_['oy_level'] = 'Level';
$_['oy_balance'] = 'Balance';
$_['oy_date_added'] = 'Date Added';

$_['column_scheme'] = 'Scheme';

$_['entry_commission']          = 'Commission (%):<span class="help">Percentage the affiliate recieves on each order. <strong> Ignored</strong> when using Multi Tier system.</span>';

$_['entry_scheme'] = 'Multi Tier Scheme:';
$_['entry_parent'] = 'Parent Affiliate:';

$_['error_database']            = 'Database Error!';
//+mod by yp end


?>