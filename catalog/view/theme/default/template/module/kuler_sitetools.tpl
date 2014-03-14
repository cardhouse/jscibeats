<?php if ($enableSiteTools) {

// Hard set the resolutions
$resolutions = array(
    array(
        'class' => 'tablet-landscape',
        'width' => '1024',
        'height' => '768',
        'description' => 'Tablet Landscape'
    ),
    array(
        'class' => 'tablet-portrait',
        'width' => '768',
        'height' => '1024',
        'description' => 'Tablet Portrait'
    ),
    array(
        'class' => 'mobile-landscape',
        'width' => '480',
        'height' => '320',
        'description' => 'Mobile Landscape'
    ),
    array(
        'class' => 'mobile-portrait',
        'width' => '320',
        'height' => '480',
        'description' => 'Mobile Portrait'
    ),
);
?>
<div id="sitetools" class="clearafter">
	<img id="close-sitetools" src="catalog/view/kulercore/image/icons/icon-close.png" alt="Close Sitetools" />
    <?php if (!isset($show_logo) || $show_logo) { ?>
    <div id="sitetools-logo"><a href="http://www.kulerthemes.com" target="_blank"><img src="catalog/view/kulercore/image/kulerthemes-logo.png" alt="Sitetools" /></a></div>
    <?php } ?>
    <?php if (!isset($show_buy_button) || $show_buy_button) { ?>
    <div id="buy-now"><a href="<?php echo $buyUrl; ?>" target="_blank"><img src="catalog/view/kulercore/image/buy-now.png" alt="Buy Now" /></a></div>
    <?php } ?>
	<ul id="color-selector" class="clearafter">
		<?php foreach ($colors as $color) { ?>
		<li><a class="<?php echo $color['body_class'] ?><?php echo isset($color['active'])? ' active' : ''; ?>" style="background-color: <?php echo $color['description']; ?>"><span></span></a></li>
		<?php } ?>
	</ul>
	<ul id="viewport-selector" class="clearafter">
		<?php foreach ($resolutions as $resolution) { ?>
		<li><a class="<?php echo $resolution['class']; ?><?php echo isset($resolution['active'])? ' active' : ''; ?>" data-description="<?php echo $resolution['description']; ?>" data-width="<?php echo $resolution['width']; ?>" data-height="<?php echo $resolution['height']; ?>"></a></li>
		<?php } ?>
	</ul>
</div>
<script type="text/javascript">
    $(function () {
        $('#header').before($('#sitetools'));

        // added extra query to fix firefox doesn't show iframe
        var base = '<?php echo $base ?>?firefox=1&enable_sitetools=0';

        var ColorChooser = (function () {
            var defaultClass;

            return {
                $trigger: null,
                init: function () {
                    var self = this;
                    self.$trigger = $('#color-selector a');
                    defaultClass = self.$trigger.filter('.active').attr('class').match(/color-\w+/);

                    self.autoSet();

                    self.$trigger.bind('click', function () {
                        self.changeColor($(this).attr('class').match(/color-\w+/));
                        return false;
                    });
                },
                changeColor: function (colorClass) {
                    var $body = $('body'),
                        bodyClass = $body.attr('class');

                    if (bodyClass) {
                        bodyClass = bodyClass.replace(/color-\w+/, colorClass);
                    } else {
                        bodyClass = colorClass;
                    }

                    $body.attr('class', bodyClass);

                    this.setActiveTrigger(colorClass);

                    document.cookie = 'color_class=' + colorClass;
                },
                autoSet: function () {
                    var colorClass = document.cookie.match(/color-\w+/);

                    if (!colorClass) {
                        colorClass = defaultClass;
                    }

                    this.changeColor(colorClass);
                },
                setActiveTrigger: function (colorClass) {
                    this.$trigger
                        .removeClass('active')
                        .filter('.' + colorClass)
                        .addClass('active');
                }
            };
        })();

        var ViewPortChooser = (function () {
            return {
                init: function () {
                    $('#viewport-selector a').on('click', function (evt) {
                        var $this = $(this);

                        if ($this.data('width') && $this.data('height')) {
                            $('#content').prepend('<div id="DemoFrame" style="padding: 0;"><iframe src="'+ base +'" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');

                            $('#DemoFrame').dialog({
                                title: $this.data('description'),
                                close: function (event, ui) {
                                    $('#DemoFrame').remove();
                                },
                                bgiframe: false,
                                width: $this.data('width'),
                                height: parseInt($this.data('height')) + 37,
                                resizable: false,
                                modal: true
                            });
                        } else {
                            $('#DemoFrame').data('dialog').close();
                        }

                        evt.preventDefault();
                    });
                }
            };
        })();
		
		$('#close-sitetools').click(function(){
				$('#sitetools').css('margin-top', -$('#sitetools').height());

                document.cookie = 'kst_force_close=1';
			}
		);
		
        ColorChooser.init();
        ViewPortChooser.init();
    });
</script>
<?php } else { ?>
<script>
    (function () {
        // still color in the demo frame

        var colorClass = document.cookie.match(/color-\w+/);

        if (colorClass) {
            var $body = $('body'),
                bodyClass = $body.attr('class');

            if (bodyClass) {
                bodyClass = bodyClass.replace(/color-\w+/, colorClass);
            } else {
                bodyClass = colorClass;
            }

            $body.attr('class', bodyClass);
        }
    })();
</script>
<?php } ?>