<?php
	$option = $this->config->get('featured_module');
	if($option && is_array($option)) {
		$option = array_shift($option);
	}
?>
<div class="box">
  <div class="box-heading"><span><?php echo $heading_title; ?></span></div>
  <div class="box-content">
    <div class="box-product product-grid">
      <?php foreach ($products as $product) { ?>
      <div>
        <?php if ($product['thumb']) { ?>
        <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
        <?php } else { ?>
        <div class="image"><span class="no-image"><img src="image/no_image.jpg" alt="<?php echo $product['name']; ?>" /></span>
		</div>
        <?php } ?>
        <?php if ($product['price']) { ?>
        <div class="price-label">
          <?php if (!$product['special']) { ?>
          <div><span class="price-fixed"><?php echo $product['price']; ?></span></div>
          <?php } else { ?>
          <div class="special-price"><span class="price-fixed"><?php echo $product['special']; ?></span><span class="price-old"><?php echo $product['price']; ?></span></div>
          <?php } ?>
        </div>
        <?php } ?>
        <div class="name" style="width: <?php echo $option['image_width']; ?>px"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
        <?php if ($product['rating']) { ?>
        <div class="rating"><img src="catalog/view/theme/acceptus-pro/image/icons/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
        <?php } ?>
        <?php if ($product['price']) { ?>
        <div class="price">
          <?php if (!$product['special']) { ?>
          <div><span class="price-fixed"><?php echo $product['price']; ?></span></div>
          <?php } else { ?>
          <div class="special-price"><span class="price-fixed"><?php echo $product['special']; ?></span><span class="price-old"><?php echo $product['price']; ?></span></div>
          <?php } ?>
        </div>
        <?php } ?>
	  	<div class="details">
		<div class="cart">
		foo
		<a onclick="addToCart('<?php echo $product['product_id']; ?>');"><span><?php echo $button_cart; ?></span></a></div>
        <div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');"><span>Add to Wish List</span></a></div>
        <div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');"><span>Add to Compare</span></a></div>
		</div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>