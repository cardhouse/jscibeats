      <div>
      	<?php if(isset($product) && $product['location'] != ''){ ?>
      	<iframe width="220" height="220" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/<?php echo $product['location'];?>&amp;auto_play=false&amp;hide_related=true&amp;visual=true"></iframe>
      	<?php } ?>
        <?php if(isset($setting['price']) && $setting['price']) { ?>
        
        <?php } ?>
        <?php if(isset($setting['name']) && $setting['name']) { ?>
        <div class="name" style="width: <?php echo $setting['image_width']; ?>px"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
        <?php } ?>
        <?php if(isset($setting['rating']) && $setting['rating']) { ?>
        <div class="rating"><img src="catalog/view/theme/acceptus-pro/image/icons/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
        <?php } ?>
        <?php if (isset($setting['description']) && $setting['description']) { ?>
        <div class="description"><?php echo $product['description']; ?></div>
        <?php } ?>
        <?php if(isset($setting['price']) && $setting['price']) { ?>
        <div class="price">
          <?php if (!$product['special']) { ?>
          <div><span class="price-fixed"><?php echo $product['price']; ?></span></div>
          <?php } else { ?>
          <div class="special-price"><span class="price-fixed"><?php echo $product['special']; ?></span><span class="price-old"><?php echo $product['price']; ?></span></div>
          <?php } ?>
        </div>
        <?php } ?>
        <?php if((isset($setting['add']) && $setting['add']) ||(isset($setting['wishlist']) && $setting['wishlist']) || (isset($setting['compare']) && $setting['compare'])) { ?>
		<div class="details">
        <?php if(isset($setting['add']) && $setting['add']) { ?>
        <div class="cart"><a onclick="addToCart('<?php echo $product['product_id']; ?>');"><span><?php echo $button_cart; ?></span></a></div>
        <?php } ?>
        <?php if(isset($setting['wishlist']) && $setting['wishlist']) { ?>
        <div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');"><span><?php echo $button_wishlist; ?></span></a></div>
        <?php } ?>
        <?php if(isset($setting['compare']) && $setting['compare']) { ?>
        <div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');"><span><?php echo $button_compare; ?></span></a></div>
        <?php } ?>
        </div>
        <?php } ?>
      </div>