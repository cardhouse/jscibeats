<?php
$paths = isset($this->request->get['path']) ? $this->request->get['path'] : '';
$paths = explode('_', $paths);

$categories = $this->model_catalog_category->getCategories(0);

$top_categories = array();

foreach ($categories as $category) {
    if ($category['top']) {
        // Level 2
        $children_data = array();

        $children = $this->model_catalog_category->getCategories($category['category_id']);

        foreach ($children as $child) {
            $data = array(
                'filter_category_id'  => $child['category_id'],
                'filter_sub_category' => true
            );

            $product_total = $this->model_catalog_product->getTotalProducts($data);

            $children_data[] = array(
                'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $product_total . ')' : ''),
                'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
                'category_id' => $child['category_id'],
                'path'  => $category['category_id'] . '_' . $child['category_id'],
                'active'   => isset($paths[1]) && $paths[1] == $child['category_id'] ? true : false
            );
        }

        // Level 1
        $top_categories[] = array(
            'category_id' => $category['category_id'],
            'name'     => $category['name'],
            'children' => $children_data,
            'column'   => $category['column'] ? $category['column'] : 1,
            'href'     => $this->url->link('product/category', 'path=' . $category['category_id']),
            'active'   => isset($paths[0]) && $paths[0] == $category['category_id'] ? true : false
        );
    }
}

$categories = $top_categories;

foreach ($categories as &$tcategory)
{
    foreach ($tcategory['children'] as &$sub_category)
    {
        $sub_category['children'] = getChildCategoryRecursive($this, $sub_category['category_id'], $sub_category['path'], 2, $paths);
    }
}


function getChildCategoryRecursive($controller, $category_id, $path, $depth, $paths)
{
    $categories = $controller->model_catalog_category->getCategories($category_id);

    $results = array();

    foreach ($categories as $category) {
        $data = array(
            'filter_category_id'  => $category['category_id'],
            'filter_sub_category' => true
        );

        $product_total = $controller->model_catalog_product->getTotalProducts($data);

        $new_path = $path . '_' . $category['category_id'];

        $results[] = array(
            'name'  => $category['name'] . ($controller->config->get('config_product_count') ? ' (' . $product_total . ')' : ''),
            'href'  => $controller->url->link('product/category', 'path=' . $new_path),
            'active' => isset($paths[$depth]) && $paths[$depth] == $category['category_id'] ? true : false,
            'children' => getChildCategoryRecursive($controller, $category['category_id'], $new_path, $depth + 1, $paths),
        );
    }

    return $results;
}

function renderSubMenuRecursive($categories) {
    $html = '<ul class="sublevel">';

    foreach ($categories as $category)
    {
		$parent = !empty($category['children']) ? ' parent' : '';
        $active = !empty($category['active']) ? ' active' : '';
        $html .= sprintf("<li class=\"item$parent $active\"><a href=\"%s\">%s</a>", $category['href'], $category['name']);

        if (!empty($category['children']))
        {
            $html .= renderSubMenuRecursive($category['children']);
        }

        $html .= '</li>';
    }

    $html .= '</ul>';

    return $html;
}
?>
<!DOCTYPE html>
<html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8" />
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<?php if ($description) { ?>
<meta name="description" content="<?php echo $description; ?>" />
<?php } ?>
<?php if ($keywords) { ?>
<meta name="keywords" content="<?php echo $keywords; ?>" />
<?php } ?>
<?php if ($icon) { ?>
<link href="<?php echo $icon; ?>" rel="icon" />
<?php } ?>
<?php foreach ($links as $link) { ?>
<link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>" />
<?php } ?>
<?php if($this->config->get('kuler_compress_styles')) { ?>
<?php echo $this->config->get('kuler_compress_styles') ?>
<?php } else { ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/acceptus-pro/stylesheet/stylesheet.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/theme/acceptus-pro/stylesheet/module-styles.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/theme/acceptus-pro/stylesheet/colors.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/theme/acceptus-pro/stylesheet/responsive.css" />
<?php if ($this->config->get('kulercp_design') && $custom_css_link = $this->getChild('module/kulercp/enableCustomCss')) { ?>
<link rel="stylesheet" href="catalog/view/theme/acceptus-pro/stylesheet/custom.css" />
<?php } ?>
<?php } ?>
<link rel="stylesheet" type="text/css" href="http://jscibeats.com/upload/player/source/css/jquery.fullwidthAudioPlayer.css">
<link rel="stylesheet" type="text/css" href="http://jscibeats.com/upload/player/source/css/jquery.fullwidthAudioPlayer-responsive.css" />

<!-- google fonts -->
<?php $font = $this->config->get('font'); ?>

<?php if (is_array($font)) { ?>
    <?php $heading_font = $font['heading']; $body_font = $font['body']; ?>

    <?php if($heading_font['status'] || $body_font['status']) { // Process Google font when the heading font or the body font is enabled ?>
        <?php
        // Prepare heading & body font
        $font_format = '';
        if ($heading_font['status']) {
            $font_format .= $heading_font['css-name'] . ':' . $heading_font['font-weight'];

            // Wrap double quote around font name if it has space
            if (strpos($heading_font['font-family'], ' ') !== false) {
                $heading_font['font-family'] = '"' . $heading_font['font-family'] . '"';
            }
        }

        if ($body_font['status']) {
            $font_format .= $font_format ? '|' : '';
            $font_format .= $body_font['css-name'] . ':' . $body_font['font-weight'];

            // Wrap double quote around font name if it has space
            if (strpos($body_font['font-family'], ' ') !== false) {
                $body_font['font-family'] = '"' . $body_font['font-family'] . '"';
            }
        }
        ?>

        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=<?php echo $font_format; ?>&subset=all" />

        <style type="text/css">
            <?php if ($heading_font['status']) { ?>
            <?php echo $heading_font['font-selector'] ?> {
                font-family: <?php echo $heading_font['font-family']; ?>;
            }
            <?php } ?>

            <?php if ($body_font['status']) { ?>
            <?php echo $body_font['font-selector'] ?> {
                font-family: <?php echo $body_font['font-family']; ?>;
            }
            <?php } ?>
        </style>
    <?php } ?>
<?php } ?>
<!-- google fonts /-->
<?php if(!$this->config->get('kuler_compress_styles') || $this->config->get('kuler_compress_style_type') == 'theme') { ?>
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" />
<?php } ?>
<?php foreach ($styles as $style) { ?>
<?php if($this->config->get('kuler_compress_style_type') == 'all' && strpos($style['href'], 'catalog/view') === 0) { continue; } ?>
<link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<?php if(!$this->config->get('kuler_compress_scripts') || $this->config->get('kuler_compress_script_type') == 'theme') { ?>

<script type="text/javascript" src="https://connect.soundcloud.com/sdk.js"></script>
<script type="text/javascript" src="catalog/view/javascript/common.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>


<?php } ?>




<?php if($this->config->get('kuler_compress_scripts')) { ?>
<?php echo $this->config->get('kuler_compress_scripts') ?>
<?php } else { ?>
<script type="text/javascript" src="catalog/view/theme/acceptus-pro/js/utils.js"></script>
<?php } ?>

<?php foreach ($scripts as $script) { ?>
<?php if($this->config->get('kuler_compress_script_type') == 'all' && strpos($script, 'catalog/view') === 0) { continue; } ?>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
<?php } ?>
<!--[if lte IE 8]>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/acceptus-pro/stylesheet/ie.css" />
<![endif]-->
<?php if ($stores) { ?>
<script type="text/javascript"><!--
$(document).ready(function() {
<?php foreach ($stores as $store) { ?>
$('body').prepend('<iframe src="<?php echo $store; ?>" style="display: none;"></iframe>');
<?php } ?>
});
//--></script>
<?php } ?>
<?php if($this->config->get('kuler_analytics_position') == 'head') echo $this->config->get('kuler_analytics_code'); ?>
<script>
// Active the home item if there is no active item
$(document).ready(function () {
    if ( ! $('.mainmenu').find('.active').length) {
        $('.mainmenu > li:eq(0)').addClass('active');
    }
});
</script>


<script type="text/javascript" src="http://www.jscibeats.com/upload/player/source/js/jquery-ui.js"></script>
<script type="text/javascript" src="http://www.jscibeats.com/upload/player/source/js/soundmanager2-nodebug-jsmin.js"></script>
<script type="text/javascript" src="http://www.jscibeats.com/upload/player/source/js/jquery.fullwidthAudioPlayer.min.js"></script>



</head>
<body class="primary-define color-<?php echo $this->config->get('kuler_theme_color') ? $this->config->get('kuler_theme_color') : 'red'; ?><?php if (preg_match('#MSIE (.+?);#', $this->request->server['HTTP_USER_AGENT'], $matches) && intval($matches[1]) < 9) echo ' is-ie'; ?>">
<div id="header">
<div id="topbar">
<div class="wrapper clearafter">
  <?php echo $language; ?>
  <?php echo $currency; ?>
  <div class="links">
    <a href="<?php echo $wishlist; ?>" id="wishlist-total" class="icon-wishlist"><?php echo $text_wishlist; ?></a>
    <a href="<?php echo $account; ?>" id="link-account" class="icon-user"><?php echo $text_account; ?></a>
    <a href="<?php echo $shopping_cart; ?>" id="link-cart" class="icon-cart"><?php echo $text_shopping_cart; ?></a>
    <a href="<?php echo $checkout; ?>" id="link-checkout" class="icon-checkout"><?php echo $text_checkout; ?></a>
  </div>
  <div id="welcome">
    <?php if (!$logged) { ?>
    <?php echo $text_welcome; ?>
    <?php } else { ?>
    <?php echo $text_logged; ?>
    <?php } ?>
  </div>
</div>
</div>
<div id="toppanel">
<div class="wrapper clearafter">
  <?php if ($logo) { ?>
  <div id="logo"><a href="<?php echo $home; ?>"><img src="<?php echo $logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" /></a></div>
  <?php } ?>
  <?php echo $cart; ?>
  <?php if (($kuler_finder = $this->config->get('kuler_finder')) && $kuler_finder['status']) { ?>
    <?php echo $this->getChild('module/kuler_finder', $kuler_finder); ?>
  <?php } else { ?>
  <div id="search">
  <div id="search-inner">
        <div class="button-search"></div>
        <input type="text" name="search" placeholder="<?php echo $text_search; ?>" value="<?php echo $search; ?>" />
  </div>
  </div>
  <?php } ?>
</div>
</div>
<?php if ($categories) { ?>
<div id="menu">
<div id="menu-inner">
<div class="wrapper clearafter">
  <?php if ($this->config->get('kuler_menu_status')) { ?>
  <?php echo $this->getChild('module/kuler_menu'); ?>
  <?php } else { ?>
  <ul class="mainmenu clearafter">
  	<li class="item"><a href="index.php" title="Home">Home</a></li>
	<?php $path = isset($this->request->get['path']) ? $this->request->get['path'] : ''; ?>
    <?php foreach ($categories as $category) { ?>
	<?php $category_id = (int) substr($category['href'], strpos($category['href'], 'path=') + 5); ?>
    <li class="item<?php echo count($category['children']) ? ' parent' : '' ?><?php echo !empty($category['active']) ? ' active' : '' ?>"><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a>
      <?php if ($category['children']) { ?>
      <div class="dropdown-container">
		<div class="dropdown clearafter" style="width: <?php echo ($category['column'] * 250); ?>px;">
		<?php for ($i = 0; $i < count($category['children']);) { ?>
        <ul class="sublevel">
          <?php $j = $i + ceil(count($category['children']) / $category['column']); ?>
          <?php for (; $i < $j; $i++) { ?>
          <?php if (isset($category['children'][$i])) { ?>
          <?php $children_id = substr($category['children'][$i]['href'], strpos($category['children'][$i]['href'], 'path=') + 5); ?>
          <li class="item<?php echo !empty($category['children'][$i]['active']) ? ' active' : '' ?><?php if (!empty($category['children'][$i]['children'])) { echo ' parent'; } ?>">
		  	<a href="<?php echo $category['children'][$i]['href']; ?>"><?php echo $category['children'][$i]['name']; ?></a>
            <?php if (!empty($category['children'][$i]['children'])) { echo renderSubMenuRecursive($category['children'][$i]['children']); } ?>
          </li>
          <?php } ?>
          <?php } ?>
        </ul>
        <?php } ?>
		</div>
      </div>
      <?php } ?>
    </li>
    <?php } ?>
	<li id="btn-mobile-toggle"></li>
  </ul>
  <?php } ?>
  <?php } ?>
</div>
</div>
</div>
</div>
<div id="container">
<div id="container-inner" class="wrapper clearafter">
<div id="notification">
<?php if ($error) { ?>
    <div class="warning"><?php echo $error ?><img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>
<?php } ?>
</div>