<?php
/**
 * jscript_dynamic_filter
 *
 * @package page
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Damian Taylor
 */
if (FILTER_SEARCH == 'Yes' && FILTER_STYLE != 'Dropdown' && FILTER_OPTIONS_STYLE == 'Expand') {
  ?>
  <script type="text/javascript">
    // Dynamic filter boxes
    $("div.dynamicfilterContent").eq(0).ready(function () {
  // Show more link if appropriate
      $('ul.dFilterExpand').each(function () {
        if ($(this).prop("scrollHeight") > 130)
          $(this).height(130).siblings('a.dFilterToggle').show();
      });
  // Expand/collapse
      $("a.dFilterToggle").click(function () {
        if ($(this).siblings("ul.dFilterExpand").height() == 130) {
          $('.dFilterToggleImg', $(this)).prop('src', $('.dFilterToggleImg').prop('src').replace('_more', '_less')).prop('alt', '<?php echo DYNAMIC_FILTER_TEXT_LESS; ?>').prop('title', '<?php echo DYNAMIC_FILTER_TEXT_LESS; ?>');
          $(this).html($(this).html().replace("<?php echo DYNAMIC_FILTER_TEXT_MORE; ?>", "<?php echo DYNAMIC_FILTER_TEXT_LESS; ?>"));
          $(this).siblings("ul.dFilterExpand").animate({height: $(this).siblings("ul.dFilterExpand").prop("scrollHeight")}, "slow");
        } else {
          $('.dFilterToggleImg', $(this)).prop('src', $('.dFilterToggleImg').prop('src').replace('_less', '_more')).prop('alt', '<?php echo DYNAMIC_FILTER_TEXT_MORE; ?>').prop('title', '<?php echo DYNAMIC_FILTER_TEXT_MORE; ?>');
          $(this).html($(this).html().replace("<?php echo DYNAMIC_FILTER_TEXT_LESS; ?>", "<?php echo DYNAMIC_FILTER_TEXT_MORE; ?>"));
          $(this).siblings("ul.dFilterExpand").animate({height: 130}, "slow");
        }
        return false;
      });
    });
  </script>
  <?php
}