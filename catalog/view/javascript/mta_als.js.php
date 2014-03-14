<?php

if(!isset($_GET['t']) || preg_match("/[^\w\-\.]+/", $_GET['t'])) die();
header('Content-Type: text/javascript');
?>
$(document).ready(function(){var _v='<div style="padding-right:3px;text-align:right;font-weight:bold"><div style="display:none;padding-bottom:3px;color:#c43">'+document.location.href+(document.location.href.indexOf('?')==-1?'?':'&amp;')+'tracking=<?php echo $_GET['t'];?> &nbsp; </div><a href="javascript:;" onclick="javascript:$(this).parent().find(\'div\').toggle();"><img src="catalog/view/theme/default/image/link.png" /></a></div>';$('#footer').length>0?$('#footer').prepend(_v):$('body').append(_v);});
