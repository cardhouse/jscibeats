<?php echo $header; ?>
<?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="box">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <?php if ($products) { ?>
  <div class="product-filter clearafter">
    <div class="display"><span class="icon-list-grey"><?php echo $text_list; ?></span><a class="icon-grid" onclick="display('grid');"><?php echo $text_grid; ?></a></div>
    <div class="product-compare"><a href="<?php echo $compare; ?>" id="compare-total" class="icon-compare-grey"><?php echo $text_compare; ?></a></div>
    <div class="limit"><?php echo $text_limit; ?>
      <select onchange="location = this.value;">
        <?php foreach ($limits as $limits) { ?>
        <?php if ($limits['value'] == $limit) { ?>
        <option value="<?php echo $limits['href']; ?>" selected="selected"><?php echo $limits['text']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $limits['href']; ?>"><?php echo $limits['text']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
    <div class="sort"><?php echo $text_sort; ?>
      <select onchange="location = this.value;">
        <?php foreach ($sorts as $sorts) { ?>
        <?php if ($sorts['value'] == $sort . '-' . $order) { ?>
        <option value="<?php echo $sorts['href']; ?>" selected="selected"><?php echo $sorts['text']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $sorts['href']; ?>"><?php echo $sorts['text']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="box-product product-list list-layout">
    <?php foreach ($products as $product) { ?>
    <?php
      $thumb_width = $this->config->get('config_image_product_width');
      $thumb_height = $this->config->get('config_image_product_height');
	?>
    <div>
      <?php if ($product['thumb']) { ?>
      <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
      <?php } else { ?>
      <div class="image">
        <span class="no-image"><img src="image/no_image.jpg" alt="<?php echo $product['name']; ?>" /></span>
      </div>
      <?php } ?>
      <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
      <div class="description"><?php echo $product['description']; ?></div>
      <?php if ($product['price']) { ?>
      <div class="price">
        <?php if (!$product['special']) { ?>
        <div><span class="price-fixed"><?php echo $product['price']; ?></span></div>
        <?php } else { ?>
		<div class="special-price"><span class="price-fixed"><?php echo $product['special']; ?></span><span class="price-old"><?php echo $product['price']; ?></span></div>
        <?php } ?>
        <?php if ($product['tax']) { ?>
        <span class="price-tax"><?php echo $text_tax; ?> <?php echo $product['tax']; ?></span>
        <?php } ?>
      </div>
      <?php } ?>
      <?php if ($product['rating']) { ?>
      <div class="rating"><img src="catalog/view/theme/acceptus-pro/image/icons/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
      <?php } ?>
      <div class="cart"><a onclick="addToCart('<?php echo $product['product_id']; ?>');"><span><?php echo $button_cart; ?></span></a></div>
      <div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');"><span><?php echo $button_wishlist; ?></span></a></div>
      <div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');"><span><?php echo $button_compare; ?></span></a></div>
    </div>
    <?php } ?>
  </div>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php } else { ?>
  <div class="content"><p><?php echo $text_empty; ?></p></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php } ?>
  </div>
  <?php echo $content_bottom; ?></div>
<script type="text/javascript"><!--
function display(view) {
	if (view == 'list') {

		$('#container .box .grid-layout').each(function () {
			if (!$(this).parents('#column-left, #column-right').length) {
				$(this).attr('class', 'box-product product-list list-layout');
			}
		});

		$('#container .box .list-layout > div').each(function(index, element) {

			html = '<div class="right">';
			
			var image = $(element).find('.image').html();
			
			if (image != null) { 
				html += '<div class="image">' + image + '</div>';
			}

			html += '</div>';


			html += '  <div class="name">' + $(element).find('.name').html() + '</div>';

			var price = $(element).find('.price').html();
			
			if (price != null) {
				html += '<div class="price">' + price  + '</div>';
			}

			var rating = $(element).find('.rating').html();

			if (rating != null) {
				html += '<div class="rating">' + rating + '</div>';
			}

			html += '  <div class="description">' + $(element).find('.description').html() + '</div>';

			html += '<div class="cart">' + $(element).find('.cart').html() + '</div>';

			html += '<div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';

			html += '<div class="compare">' + $(element).find('.compare').html() + '</div>';

			$(element).html(html);

		});
		
		$('.display').html('<span class="icon-list-grey"><?php echo $text_list; ?></span><a class="icon-grid" onclick="display(\'grid\');"><?php echo $text_grid; ?></a>');

		$.totalStorage('display', 'list'); 
	} else {

		$('#container .box .list-layout').each(function () {
			if (!$(this).parents('#column-left, #column-right').length) {
				$(this).attr('class', 'box-product product-grid grid-layout');
			}
		});

		$('#container .box .grid-layout > div').each(function(index, element) {

			html = '';

			var image = $(element).find('.image').html();

			if (image != null) {
				html += '<div class="image">' + image + '</div>';
			}

			var price = $(element).find('.price').html();
			
			if (price != null) {
				html += '<div class="price-label">' + price  + '</div>';
			}

			var rating = $(element).find('.rating').html();
			
			if (rating != null) {
				html += '<div class="rating">' + rating + '</div>';
			}

			html += '<div class="name" style="width: ' + <?php echo $this->config->get('config_image_product_width'); ?> + 'px">' + $(element).find('.name').html() + '</div>';

			html += '<div class="description">' + $(element).find('.description').html() + '</div>';
			
			var price = $(element).find('.price').html();
			
			if (price != null) {
				html += '<div class="price">' + price  + '</div>';
			}
						
			html += '<div class="details">';

			html += '<div class="cart">' + $(element).find('.cart').html() + '</div>';

			html += '<div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';

			html += '<div class="compare">' + $(element).find('.compare').html() + '</div>';

			html += '</div>';
			
			$(element).html(html);
		});	

		// Product hover
		$('#container .box .grid-layout > div').hover(function () {
			var totalHeight = 0;
			$(this).find('.details').children().each(function() {
				totalHeight = totalHeight + $(this).height();
			});
			$(this).find('.details').height(totalHeight);
			$(this).css('marginBottom', -1 * totalHeight);
		}, function () {
			$(this).css('marginBottom', 0);
			$(this).find('.details').height(0);
		});
					
		$('.display').html('<a class="icon-list" onclick="display(\'list\');"><?php echo $text_list; ?></a><span class="icon-grid-grey"><?php echo $text_grid; ?></span>');
		
		$.totalStorage('display', 'grid');
	}
}

view = $.totalStorage('display');

if (view) {
	display(view);
} else {
	display('list');
}
//--></script> 
<?php echo $footer; ?>