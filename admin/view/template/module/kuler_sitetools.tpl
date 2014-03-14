<?php

/*----------------------------------------------------------------------------/
* @Author		KulerThemes.com http://www.kulerthemes.com
* @Copyright	Copyright (C) 2012 - 2013 KulerThemes.com. All rights reserved.
* @License		KulerThemes.com Proprietary License
/-----------------------------------------------------------------------------*/

?>

<?php echo $header; ?>
<link rel="stylesheet" href="view/kulercore/css/kulercore.css" media="all"/>
<div id="content" class="kuler-module">
<div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
  <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
</div>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<div class="box">
    <div class="heading clearafter">
      <h1><img src="./view/kulercore/images/logos/kuler_sitetools.png" alt="<?php echo $heading_title; ?>" /></h1>
      <div class="buttons">
          <a onclick="$('#form').submit();" class="button save-settings"><?php echo $button_save; ?></a>
          <a onclick="$('#op').val('close'); $('#form').submit();" class="button cancel-settings"><?php echo $button_close; ?></a>
          <a href="<?php echo $cancel; ?>" class="button cancel-settings"><?php echo $button_cancel; ?></a>
      </div>
    </div>
  <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <input type="hidden" name="op" id="op" />
        <ul class="vtabs">
            <li><a href="#tab-module-theme-colors"><?php echo $text_theme_colors ?></a></li>
            <li><a href="#tab-module-sample-data"><?php echo $text_sample_data; ?></a></li>
        </ul>
            <div id="tab-module-theme-colors" class="vtabs-content">
                <?php if ($colorChanged) { ?>
                <p class="warning"><?php echo __('Colors have changed! You must save to use this color set.'); ?></p>
                <?php } ?>
                <table class="form">
                    <tr>
                        <td><?php echo $entry_status; ?></td>
                        <td>
                            <div class="kuler-switch-btn">
                                <input type="hidden" name="kuler_sitetools_colors_status" value="0"/>
                                <input type="checkbox" name="kuler_sitetools_colors_status" value="1"<?php if (isset($kuler_sitetools_colors_status) && $kuler_sitetools_colors_status) echo ' checked="checked"'; ?> />
                                <span class="kuler-switch-btn-holder"></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo __('Buy URL'); ?>:</td>
                        <td>
                            <input type="text" name="kuler_sitetools_buy_url" value="<?php if (isset($kuler_sitetools_buy_url)) echo $kuler_sitetools_buy_url; ?>" size="40" />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo __('Show Buy Button'); ?>:</td>
                        <td>
                            <div class="kuler-switch-btn">
                                <input type="hidden" name="kuler_sitetools_show_buy_button" value="0"/>
                                <input type="checkbox" name="kuler_sitetools_show_buy_button" value="1"<?php if (!isset($kuler_sitetools_show_buy_button) || $kuler_sitetools_show_buy_button) echo ' checked="checked"'; ?> />
                                <span class="kuler-switch-btn-holder"></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo __('Show Logo'); ?>:</td>
                        <td>
                            <div class="kuler-switch-btn">
                                <input type="hidden" name="kuler_sitetools_show_logo" value="0"/>
                                <input type="checkbox" name="kuler_sitetools_show_logo" value="1"<?php if (!isset($kuler_sitetools_show_logo) || $kuler_sitetools_show_logo) echo ' checked="checked"'; ?>/>
                                <span class="kuler-switch-btn-holder"></span>
                            </div>

                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table id="color" class="list">
                                <thead>
                                <tr>
                                    <td class="left"><?php echo $entry_body_class ?></td>
                                    <td class="left"><?php echo $entry_description; ?></td>
                                    <td class="right"><?php echo $entry_sort_order ?></td>
                                    <td></td>
                                </tr>
                                </thead>
                                <?php $color_row = 0; ?>
                                <?php foreach ($kuler_sitetools_colors as $color) { ?>
                                <tbody id="color-row<?php echo $color_row; ?>">
                                <tr>
                                    <td class="left"><input type="text" name="kuler_sitetools_colors[<?php echo $color_row  ?>][body_class]" value="<?php echo $color['body_class'] ?>" /></td>
                                    <td class="left"><input type="text" style="width: 100%;" name="kuler_sitetools_colors[<?php echo $color_row  ?>][description]" value="<?php echo $color['description'] ?>" /></td>
                                    <td class="right"><input type="text" size="3" name="kuler_sitetools_colors[<?php echo $color_row  ?>][sort_order]" value="<?php echo $color['sort_order'] ?>" /></td>
                                    <td class="left"><a href="javascript:void();" onclick="$('#color-row<?php echo $color_row; ?>').remove();" class="button"><span><?php echo $button_remove; ?></span></a></td>
                                </tr>
                                </tbody>
                                <?php $color_row++; ?>
                                <?php } ?>
                                <tfoot>
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="left"><a href="javascript:void();" onclick="addColor();" class="button"><?php echo $button_add_color; ?></a></td>
                                </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                    </tr>
                </table>
            </div>
            <div id="tab-module-sample-data" class="vtabs-content">
                <table class="form">
                    <tr>
                        <td><?php echo $entry_status; ?></td>
                        <td>
                            <div class="kuler-switch-btn">
                                <input type='hidden' name='sample_data[status]' value='0' />
                                <input type="checkbox" name="sample_data[status]"<?php if ($sample_data['status']) echo 'checked="checked"'; ?>>
                                <span class="kuler-switch-btn-holder"></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_folders; ?></td>
                        <td>
                            <textarea name="sample_data[folders]" cols="60" rows="5"><?php echo $sample_data['folders']; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <a class="button" href="<?php echo $build_sample_data_url; ?>" id="BuildSampleData"><?php echo $button_build_sample_data; ?></a>
                        </td>
                    </tr>
                </table>
            </div>
    </form>
  </div>
</div>
<script type="text/javascript"><!--
var color_row = <?php echo $color_row; ?>;

function addColor() {
    html  = '<tbody id="color-row' + color_row + '">';
    html += '  <tr>';
    html += '    <td class="left"><input type="text" name="kuler_sitetools_colors[' + color_row + '][body_class]" /></td>';
    html += '    <td class="left"><input type="text" style="width: 100%;" name="kuler_sitetools_colors[' + color_row + '][description]" /></td>';
    html += '    <td class="right"><input type="text" size="3" name="kuler_sitetools_colors[' + color_row + '][sort_order]" /></td>';
    html += '    <td class="left"><a href="javascript:void();" onclick="$(\'#color-row' + color_row + '\').remove();" class="button"><span><?php echo $button_remove; ?></span></a></td>';
    html += '  </tr>';
    html += '</tbody>';
	
	$('#color tfoot').before(html);
	
	color_row++;
}
//--></script>
<script type="text/javascript"><!--
    $('.vtabs a').tabs();

    // Activate current tab
    $('.vtabs a').on('click', function (evt) {
        evt.preventDefault();

        document.cookie = 'kst_active_tab=' + $(this).attr('href');
    });

    var matches;
    if (matches = document.cookie.match(/kst_active_tab=([^;]+)/)) {
        $('.vtabs a[href="' + matches[1] + '"]').trigger('click');
    }

    $('#BuildSampleData').on('click', function (evt) {
        evt.preventDefault();

        $('#form')
            .attr('action', $(this).attr('href'))
            .submit();
    });
//--></script>
    <?php echo $footer; ?>