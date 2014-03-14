</div>
</div>
<?php if ($bottom = $this->getChild('module/kulercp')) { ?>
    <?php echo $bottom; ?>
<?php } ?>
<div id="footer">
<div class="wrapper clearafter">
  <?php if ($informations) { ?>
  <div class="column grid-4">
    <h3><span><?php echo $text_information; ?></span></h3>
    <ul>
      <?php foreach ($informations as $information) { ?>
      <li><a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a></li>
      <?php } ?>
    </ul>
  </div>
  <?php } ?>
  <div class="column grid-4">
    <h3><span><?php echo $text_service; ?></span></h3>
    <ul>
      <li><a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a></li>
      <li><a href="<?php echo $return; ?>"><?php echo $text_return; ?></a></li>
      <li><a href="<?php echo $sitemap; ?>"><?php echo $text_sitemap; ?></a></li>
    </ul>
  </div>
  <div class="column grid-4">
    <h3><span><?php echo $text_extra; ?></span></h3>
    <ul>
      <li><a href="<?php echo $manufacturer; ?>"><?php echo 'Producers'; ?></a></li>
      <li><a href="<?php echo $voucher; ?>"><?php echo $text_voucher; ?></a></li>
      <li><a href="<?php echo $affiliate; ?>"><?php echo $text_affiliate; ?></a></li>
      <li><a href="<?php echo $special; ?>"><?php echo $text_special; ?></a></li>
    </ul>
  </div>
  <div class="column grid-4">
    <h3><span><?php echo $text_account; ?></span></h3>
    <ul>
      <li><a href="<?php echo $account; ?>"><?php echo $text_account; ?></a></li>
      <li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
      <li><a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a></li>
      <li><a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a></li>
    </ul>
  </div>
</div>
</div>
<!--
OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
Please donate via PayPal to donate@opencart.com
//-->
<div id="powered"><div class="wrapper">
	<!-- block: Payment Icons -->
	<?php if($this->config->get('kuler_payment_status') && $this->config->get('kuler_payment_items')) { ?>
	<?php $payments = $this->config->get('kuler_payment_items'); ?>
	<ul id="footer-payments" class="clearafter">
		<?php foreach($payments as $k => $item) { ?>
		<?php if($item['status'] == 0) continue; ?>
		<li class="<?php echo $k ?>"><?php echo $item['link'] ?></li>
		<?php } ?>
	</ul>
	<?php } ?>
    <?php if ($custom_copyright = $this->getChild('module/kulercp/getcustomcopyright')) { ?>
        <?php echo $custom_copyright; ?>
    <?php } else { ?>
	<!-- <?php echo $powered; ?> -->
	<!-- Please do not remove following code or we can not support you with this product ! -->
	<p>Powered by <a href="http://www.opencart.com" title="Opencart" target="_blank">Opencart</a><br />Opencart theme designed by <a href="http://www.kulerthemes.com" title="KulerThemes"><strong>KulerThemes.com</strong></a></p>
    <?php } ?>
</div></div>
<!--
OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
Please donate via PayPal to donate@opencart.com
//-->
<?php if($this->config->get('kuler_analytics_position') == 'bottom') echo $this->config->get('kuler_analytics_code'); ?>
</body></html>