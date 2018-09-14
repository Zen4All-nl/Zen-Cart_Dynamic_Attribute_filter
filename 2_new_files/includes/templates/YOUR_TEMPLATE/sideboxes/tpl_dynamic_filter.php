<?php

/*
 * tpl_dynamic_filter.php
 *
 * Zen Cart dynamic filter module
 * Damian Taylor, March 2010
 *
 */
// draw filter form
$content = '';
$content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent">' . "\n";
$content .= zen_draw_form('product_filter_form', '', 'get');

// draw hidden fields
reset($_GET);
foreach ($_GET as $key => $value) {
  if (($key != 'main_page' || $key == 'main_page' && (!defined('CEON_URI_MAPPING_ENABLED') || CEON_URI_MAPPING_ENABLED == 0 || $current_page_base == 'advanced_search_result')) && ($key != zen_session_name()) && ($key != 'error') && ($key != 'currency') && ($key != 'x') && ($key != 'y') && ($key != 'filter_id')) {
    if ((substr($key, 0, strlen(DYNAMIC_FILTER_PREFIX)) != DYNAMIC_FILTER_PREFIX)) {
      $content .= zen_draw_hidden_field($key, $value);
    }
  }
}
/*
 * start manufacturer/category drop down/link/check boxes
 */

// Only display if standard zen cart category/manufacturer dropdown is disabled
if (PRODUCT_LIST_FILTER == 0) {
  if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] != '' || $current_page_base == 'products_all' || $current_page_base == 'products_new' || $current_page_base == 'specials' || $current_page_base == 'featured_products' || $current_page_base == 'advanced_search_result') {
    if (count($unfilteredCategories) > 0) {
      $group = DYNAMIC_FILTER_PREFIX . str_replace(' ', '', DYNAMIC_FILTER_CATEGORY_GROUP);
      $content .= '<div>';
      $content .= '<div class="dFilter">';
      $content .= '<p class="dFilterHeading">' . DYNAMIC_FILTER_TEXT_PREFIX . DYNAMIC_FILTER_TEXT_CATEGORY . DYNAMIC_FILTER_TEXT_SUFFIX . '</p>';
      if (isset($_GET[$group]) && array_filter($_GET[$group])) {
        $content .= '<div class="dFilterClear"><a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array($group)), 'NONSSL') . '">' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'clear_filter.png', DYNAMIC_FILTER_BUTTON_CLEAR_FILTER_ALT) . '</a></div>';
      }
      if (strtok(FILTER_STYLE, " ") == 'Dropdown') {
        $content .= '<select name="' . $group . '[]" class="dFilterDrop"' . (FILTER_STYLE == 'Dropdown - Single' ? ' onchange="this.form.submit();"' : '') . '>' . '<option value=""' . (!isset($_GET[$group]) || !array_filter($_GET[$group]) ? ' selected="selected"' : '') . '>' . $dropdownDefault . '</option>';
      } else {
        $content .= '<ul' . (count($unfilteredCategories) > FILTER_MAX_OPTIONS ? (FILTER_OPTIONS_STYLE == 'Scroll' ? ' class="dFilterScroll">' : ' class="dFilterExpand">') : '>');
      }
      foreach ($categories as $category) {
        if (isset($_GET[$group]) && in_array($category['categories_id'], $_GET[$group])) {
          $linkClass = 'selected';
        } else if ($category['flag'] == 'N') {
          $linkClass = 'disabled';
        } else {
          $linkClass = 'enabled';
        }

        $onClick = '';
        if (FILTER_GOOGLE_TRACKING != 'No') {
          $onClick .= $trackingStart . '"filterAction", "' . ($linkClass != 'selected' ? 'addFilter' : 'removeFilter') . '", "' . $pageName . ';' . DYNAMIC_FILTER_CATEGORY_GROUP . '=' . $category['categories_name'] . '"' . $trackingEnd;
        }
        if (FILTER_STYLE == 'Checkbox - Single') {
          $onClick .= ' this.form.submit();';
        }

        if (FILTER_METHOD != 'Hidden' || $linkClass != 'disabled') {
          $hrefLink = $group . '[]=' . $category['categories_id'];
          switch (strtok(FILTER_STYLE, " ")) {
            case 'Checkbox':
              $content .= '<li class="dFilterLink">' . zen_draw_checkbox_field($group . '[]', $category['categories_id'], (isset($_GET[$group]) && in_array($category['categories_id'], $_GET[$group]) ? true : false), ($linkClass == 'disabled' ? 'disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Checkbox - Single' ? ' onclick="' . $onClick . '"' : '')) . $category['categories_name'] . $numberOfCategoriesLeft . '</li>';
              break;
            case 'Link':
              $content .= '<li class="dFilterLink"><a class="' . $linkClass . '"' . ($linkClass != 'disabled' ? ' rel="nofollow" href="' . zen_href_link($_GET['main_page'], ($linkClass != 'selected' ? $parameters . $hrefLink : str_replace(array($hrefLink, '&' . $hrefLink), array("", ""), $parameters)), 'NONSSL') . '"' . ($onClick != '' ? ' onclick="' . $onClick . '"' : '') : '') . ' >' . $category['categories_name'] . $numberOfCategoriesLeft . '</a></li>';
              break;
            case 'Dropdown':
              $content .= '<option value="' . $category['categories_id'] . '"' . ($linkClass == 'selected' ? ' selected="selected"' : '') . ($linkClass == 'disabled' ? ' disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Dropdown - Single' ? ' onclick="' . $onClick . '"' : '') . ' >' . $category['categories_name'] . $numberOfCategoriesLeft . '</option>';
              break;
          }
        }
      }
      if (strtok(FILTER_STYLE, " ") == 'Dropdown') {
        $content .= '</select>';
      } else {
        $content .= '</ul>';
      }
      if (FILTER_OPTIONS_STYLE == 'Expand' && count($unfilteredCategories) > FILTER_MAX_OPTIONS) {
        $content .= '<a class="dFilterToggle" href="#">' . TEXT_DYNAMIC_FILTER_SHOW_MORE . zen_image(DIR_WS_TEMPLATE_IMAGES . 'arrow_more.gif', TEXT_DYNAMIC_FILTER_SHOW_MORE, '', '', 'class="dFilterToggleImg"') . '</a>';
      }
      $content .= '</div>';
      $content .= '</div>';
    }
  }
  if (!isset($_GET['manufacturers_id'])) {
    if (count($unfilteredManufacturers) > 0) {
      $group = DYNAMIC_FILTER_PREFIX . str_replace(' ', '', DYNAMIC_FILTER_MANUFACTURER_GROUP);
      $content .= '<hr width="90%" size="0" />';
      $content .= '<div>';
      $content .= '<div class="dFilter">';
      $content .= '<p class="dFilterHeading">' . DYNAMIC_FILTER_TEXT_PREFIX . DYNAMIC_FILTER_TEXT_MANUFACTURER . DYNAMIC_FILTER_TEXT_SUFFIX . '</p>';
      if (isset($_GET[$group]) && array_filter($_GET[$group])) {
        $content .= '<div class="dFilterClear"><a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array($group)), 'NONSSL') . '">' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'clear_filter.png', DYNAMIC_FILTER_BUTTON_CLEAR_FILTER_ALT) . '</a></div>';
      }
      if (strtok(FILTER_STYLE, " ") == 'Dropdown') {
        $content .= '<select name="' . $group . '[]" class="dFilterDrop"' . (FILTER_STYLE == 'Dropdown - Single' ? ' onchange="this.form.submit();"' : '') . '>' . '<option value=""' . (!isset($_GET[$group]) || !array_filter($_GET[$group]) ? ' selected="selected"' : '') . '>' . $dropdownDefault . '</option>';
      } else {
        $content .= '<ul' . (count($unfilteredManufacturers) > FILTER_MAX_OPTIONS ? (FILTER_OPTIONS_STYLE == 'Scroll' ? ' class="dFilterScroll">' : ' class="dFilterExpand">') : '>');
      }
      foreach ($manufacturers as $manufacturer) {
        if (isset($_GET[$group]) && in_array($manufacturer['manufacturers_id'], $_GET[$group])) {
          $linkClass = 'selected';
        } else if ($manufacturer['flag'] == 'N') {
          $linkClass = 'disabled';
        } else {
          $linkClass = 'enabled';
        }

        $onClick = '';
        if (FILTER_GOOGLE_TRACKING != 'No') {
          $onClick .= $trackingStart . '"filterAction", "' . ($linkClass != 'selected' ? 'addFilter' : 'removeFilter') . '", "' . $pageName . ';' . DYNAMIC_FILTER_MANUFACTURER_GROUP . '=' . $manufacturer['manufacturers_name'] . '"' . $trackingEnd;
        }
        if (FILTER_STYLE == 'Checkbox - Single') {
          $onClick .= ' this.form.submit();';
        }

        if (FILTER_METHOD != 'Hidden' || $linkClass != 'disabled') {
          $hrefLink = $group . '[]=' . $manufacturer['manufacturers_id'];
          switch (strtok(FILTER_STYLE, " ")) {
            case 'Checkbox':
              $content .= '<li class="dFilterLink">' . zen_draw_checkbox_field($group . '[]', $manufacturer['manufacturers_id'], (isset($_GET[$group]) && in_array($manufacturer['manufacturers_id'], $_GET[$group]) ? true : false), ($linkClass == 'disabled' ? 'disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Checkbox - Single' ? ' onclick="' . $onClick . '"' : '')) . $manufacturer['manufacturers_name'] . $numberOfManufacturersLeft . '</li>';
              break;
            case 'Link':
              $content .= '<li class="dFilterLink"><a class="' . $linkClass . '"' . ($linkClass != 'disabled' ? ' rel="nofollow" href="' . zen_href_link($_GET['main_page'], ($linkClass != 'selected' ? $parameters . $hrefLink : str_replace(array($hrefLink, '&' . $hrefLink), array("", ""), $parameters)), 'NONSSL') . '"' . ($onClick != '' ? ' onclick="' . $onClick . '"' : '') : '') . ' >' . $manufacturer['manufacturers_name'] . $numberOfManufacturersLeft . '</a></li>';
              break;
            case 'Dropdown':
              $content .= '<option value="' . $manufacturer['manufacturers_id'] . '"' . ($linkClass == 'selected' ? ' selected="selected"' : '') . ($linkClass == 'disabled' ? ' disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Dropdown - Single' ? ' onclick="' . $onClick . '"' : '') . ' >' . $manufacturer['manufacturers_name'] . $numberOfManufacturersLeft . '</option>';
              break;
          }
        }
      }
      if (strtok(FILTER_STYLE, " ") == 'Dropdown') {
        $content .= '</select>';
      } else {
        $content .= '</ul>';
      }
      if (FILTER_OPTIONS_STYLE == 'Expand' && count($unfilteredManufacturers) > FILTER_MAX_OPTIONS) {
        $content .= '<a class="dFilterToggle" href="#">' . TEXT_DYNAMIC_FILTER_SHOW_MORE . zen_image(DIR_WS_TEMPLATE_IMAGES . 'arrow_more.gif', TEXT_DYNAMIC_FILTER_SHOW_MORE, '', '', 'class="dFilterToggleImg"') . '</a>';
      }
      $content .= '</div>';
      $content .= '</div>';
    }
  }
}

/*
 * end manufacturer/category drop down/link/check boxes
 */


/*
 * start price range link/check boxes
 */
if (SHOW_FILTER_BY_PRICE == 'Yes') {
  if (count($priceArray) > 0) {
    $priceGap = floor(($max - $min) / (FILTER_MAX_RANGES - 1));
    if (FILTER_MIN_PRICE > 0 && $priceGap < FILTER_MIN_PRICE) {
      $priceGap = FILTER_MIN_PRICE;
    }
    if (FILTER_MAX_PRICE > 0 && $priceGap > FILTER_MAX_PRICE) {
      $priceGap = FILTER_MAX_PRICE;
    }

    $resetParms[] = $group;
    $parameters = zen_get_all_get_params();
    $dropdownDefault = str_replace('%n', DYNAMIC_FILTER_PRICE_GROUP, DYNAMIC_FILTER_DROPDOWN_DEFAULT);
    $priceCount = 0;
    $prices = '';

    for ($start = $min - 0.5; $start < $max; $start = $end + 0.01) {
      $end = round($start + $priceGap);
      if ($end < $max) {
// BOF tax fix by Zen4All
        $text = $currency_symbol . round(zen_add_tax($start, $products_tax) * $conversion_rate) . TEXT_DYNAMIC_FILTER_DIVIDER . $currency_symbol . round(zen_add_tax($end, $products_tax) * $conversion_rate);
      } else {
        $text = $currency_symbol . round(zen_add_tax($start, $products_tax) * $conversion_rate) . TEXT_DYNAMIC_FILTER_AND_OVER;
// EOF tax fix
      }
      foreach ($priceArray as $price) {
        if ($start <= $price && $end >= $price) {
          if (isset($_GET[$group]) && in_array($start . '--' . $end, $_GET[$group])) {
            $linkClass = 'selected';
          } else {
            $linkClass = 'enabled';
          }
          break;
        } else {
          $linkClass = 'disabled';
        }
      }

      $onClick = '';
      if (FILTER_GOOGLE_TRACKING != 'No') {
        $onClick .= $trackingStart . '"filterAction", "' . ($linkClass != 'selected' ? 'addFilter' : 'removeFilter') . '", "' . $pageName . ';' . DYNAMIC_FILTER_PRICE_GROUP . '=' . $start . '-' . $end . '"' . $trackingEnd;
      }
      if (FILTER_STYLE == 'Checkbox - Single') {
        $onClick .= ' this.form.submit();';
      }

      if (FILTER_METHOD != 'Hidden' || $linkClass != 'disabled') {
        $hrefLink = $group . '[]=' . $start . '--' . $end;
        switch (strtok(FILTER_STYLE, " ")) {
          case 'Checkbox':
            $prices .= '<li class="dFilterLink">' . zen_draw_checkbox_field($group . '[]', $start . '--' . $end, (isset($_GET[$group]) && in_array($start . '--' . $end, $_GET[$group]) ? true : false), ($linkClass == 'disabled' ? 'disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Checkbox - Single' ? ' onclick="' . $onClick . '"' : '')) . $text . '</li>';
            break;
          case 'Link':
            $prices .= '<li class="dFilterLink"><a class="' . $linkClass . '"' . ($linkClass != 'disabled' ? ' rel="nofollow" href="' . zen_href_link($_GET['main_page'], ($linkClass != 'selected' ? $parameters . $hrefLink : str_replace(array($hrefLink, '&' . $hrefLink), array("", ""), $parameters)), 'NONSSL') . '"' . ($onClick != '' ? ' onclick="' . $onClick . '"' : '') : '') . ' >' . $text . '</a></li>';
            break;
          case 'Dropdown':
            $prices .= '<option value="' . $start . '--' . $end . '"' . ($linkClass == 'selected' ? ' selected="selected"' : '') . ($linkClass == 'disabled' ? ' disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Dropdown - Single' ? ' onclick="' . $onClick . '"' : '') . ' >' . $text . '</option>';
            break;
        }
      }
      ++$priceCount;
    }

    $group = DYNAMIC_FILTER_PREFIX . str_replace(' ', '', DYNAMIC_FILTER_PRICE_GROUP);
    $content .= '<hr width="90%" size="0" />';
    $content .= '<div>';
    $content .= '<div class="dFilter">';
    $content .= '<p class="dFilterHeading">' . DYNAMIC_FILTER_TEXT_PREFIX . DYNAMIC_FILTER_TEXT_PRICE . DYNAMIC_FILTER_TEXT_SUFFIX . '</p>';
    if (isset($_GET[$group]) && array_filter($_GET[$group])) {
      $content .= '<div class="dFilterClear"><a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array($group)), 'NONSSL') . '">' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'clear_filter.png', DYNAMIC_FILTER_BUTTON_CLEAR_FILTER_ALT) . '</a></div>';
    }
    if (strtok(FILTER_STYLE, " ") == 'Dropdown') {
      $content .= '<select name="' . $group . '[]" class="dFilterDrop"' . (FILTER_STYLE == 'Dropdown - Single' ? ' onchange="this.form.submit();"' : '') . '>' . '<option value=""' . (!isset($_GET[$group]) || !array_filter($_GET[$group]) ? ' selected="selected"' : '') . '>' . $dropdownDefault . '</option>';
      $content .= $prices;
      $content .= '</select>';
    } else {
      $content .= '<ul' . ($priceCount > FILTER_MAX_OPTIONS ? (FILTER_OPTIONS_STYLE == 'Scroll' ? ' class="dFilterScroll">' : ' class="dFilterExpand">') : '>');
      $content .= $prices;
      $content .= '</ul>';
    }
    if (FILTER_OPTIONS_STYLE == 'Expand' && $priceCount > FILTER_MAX_OPTIONS) {
      $content .= '<a class="dFilterToggle" href="#">' . TEXT_DYNAMIC_FILTER_SHOW_MORE . zen_image(DIR_WS_TEMPLATE_IMAGES . 'arrow_more.gif', TEXT_DYNAMIC_FILTER_SHOW_MORE, '', '', 'class="dFilterToggleImg"') . '</a>';
    }
    $content .= '</div>';
    $content .= '</div>';
  }
}

/*
 * end price range link/check boxes
 */


/*
 * start attribute link/check boxes
 */

if (count($filteredProducts) > 0) {
  foreach ($attributes as $attribute) {
    if (FILTER_OPTIONS_LEFT == 'Yes') {
      $numberOfProductsLeft = '&nbsp;<span class="numberOfProductsLeft">(' . htmlspecialchars(html_entity_decode($attribute['flag'], ENT_QUOTES)) . ')</span>';
      $numberOfManufacturersLeft = '&nbsp;<span class="numberOfProductsLeft">(' . htmlspecialchars(html_entity_decode($manufacturer['flag'], ENT_QUOTES)) . ')</span>';
      $numberOfCategoriesLeft = '&nbsp;<span class="numberOfProductsLeft">(' . htmlspecialchars(html_entity_decode($categorie['flag'], ENT_QUOTES)) . ')</span>';
    } else {
      $numberOfProductsLeft = '';
      $numberOfManufacturersLeft = '';
      $numberOfCategoriesLeft = '';
    }
    // output if option name changes!!!
    if ($attribute['products_options_name'] != $savName) {
      $options_array = array();
      if ($savName != '') {
        $content .= '<hr width="90%" size="0" />';
        $content .= '<div>';
        $content .= '<div class="dFilter">';
        $content .= '<p class="dFilterHeading">' . DYNAMIC_FILTER_TEXT_PREFIX . htmlspecialchars(html_entity_decode($savName, ENT_QUOTES)) . DYNAMIC_FILTER_TEXT_SUFFIX . '</p>';
        if (isset($_GET[$group]) && array_filter($_GET[$group])) {
          $content .= '<div class="dFilterClear"><a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array($group)), 'NONSSL') . '">' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'clear_filter.png', DYNAMIC_FILTER_BUTTON_CLEAR_FILTER_ALT) . '</a></div>';
        }
        if (strtok(FILTER_STYLE, " ") == 'Dropdown') {
          $content .= '<select name="' . $group . '[]" class="dFilterDrop"' . (FILTER_STYLE == 'Dropdown - Single' ? ' onchange="this.form.submit();"' : '') . '>' . '<option value=""' . (!isset($_GET[$group]) || !array_filter($_GET[$group]) ? ' selected="selected"' : '') . '>' . $dropdownDefault . '</option>';
          $content .= $filters;
          $content .= '</select>';
        } else {
          $content .= '<ul' . ($attrCount > FILTER_MAX_OPTIONS ? (FILTER_OPTIONS_STYLE == 'Scroll' ? ' class="dFilterScroll">' : ' class="dFilterExpand">') : '>');
          $content .= $filters;
          $content .= '</ul>';
        }
        if (FILTER_OPTIONS_STYLE == 'Expand' && $attrCount > FILTER_MAX_OPTIONS) {
          $content .= '<a class="dFilterToggle" href="#">' . TEXT_DYNAMIC_FILTER_SHOW_MORE . zen_image(DIR_WS_TEMPLATE_IMAGES . 'arrow_more.gif', TEXT_DYNAMIC_FILTER_SHOW_MORE, '', '', 'class="dFilterToggleImg"') . '</a>';
        }
        $content .= '</div>';
        $content .= '</div>';
      }

      $group = DYNAMIC_FILTER_PREFIX . str_replace(' ', '', $attribute['products_options_name']);
      $resetParms[] = $group;
      $parameters = zen_get_all_get_params();
      $dropdownDefault = str_replace('%n', $attribute['products_options_name'], DYNAMIC_FILTER_DROPDOWN_DEFAULT);
      $filters = '';
      $attrCount = 0;
    }

    if ($attribute['products_options_values_name'] != $savValue) {
      if (isset($_GET[$group]) && in_array($attribute['products_options_values_name'], $_GET[$group])) {
        $linkClass = 'selected';
      } else if (isset($_GET[$group]) && array_filter($_GET[$group]) && !in_array($attribute['products_options_values_name'], $_GET[$group]) || $attribute['flag'] == 0) {
        $linkClass = 'disabled';
        //} else if ($attribute['flag'] == 0) { $linkClass = 'disabled';
      } else {
        $linkClass = 'enabled';
      }

      $onClick = '';
      if (FILTER_GOOGLE_TRACKING != 'No') {
        $onClick .= $trackingStart . '"filterAction", "' . ($linkClass != 'selected' ? 'addFilter' : 'removeFilter') . '", "' . $pageName . ';' . $attribute['products_options_name'] . '=' . htmlspecialchars(html_entity_decode($attribute['products_options_values_name'], ENT_QUOTES)) . '"' . $trackingEnd;
      }
      if (FILTER_STYLE == 'Checkbox - Single') {
        $onClick .= ' this.form.submit();';
      }

      if (FILTER_METHOD != 'Hidden' || $linkClass != 'disabled') {
        $hrefLink = $group . '[]=' . rawurlencode($attribute['products_options_values_name']);
        switch (strtok(FILTER_STYLE, " ")) {
          case 'Checkbox':
            $filters .= '<li class="dFilterLink">' . zen_draw_checkbox_field($group . '[]', $attribute['products_options_values_name'], (isset($_GET[$group]) && in_array($attribute['products_options_values_name'], $_GET[$group]) ? true : false), ($linkClass == 'disabled' ? 'disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Checkbox - Single' ? ' onclick="' . $onClick . '"' : '')) . '&nbsp;' . htmlspecialchars(html_entity_decode($attribute['products_options_values_name'], ENT_QUOTES)) . $numberOfProductsLeft . '</li>';
            break;
          case 'Link':
            $filters .= '<li class="dFilterLink"><a class="' . $linkClass . '"' . ($linkClass != 'disabled' ? ' rel="nofollow" href="' . zen_href_link($_GET['main_page'], ($linkClass != 'selected' ? $parameters . $hrefLink : str_replace(array($hrefLink, '&' . $hrefLink), array("", ""), $parameters)), 'NONSSL') . '"' . ($onClick != '' ? ' onclick="' . $onClick . '"' : '') : '') . ' >' . htmlspecialchars(html_entity_decode($attribute['products_options_values_name'], ENT_QUOTES)) . $numberOfProductsLeft . '</a></li>';
            break;
          case 'Dropdown':
            $filters .= '<option value="' . htmlspecialchars(html_entity_decode($attribute['products_options_values_name'], ENT_QUOTES)) . '"' . ($linkClass == 'selected' ? ' selected="selected"' : '') . ($linkClass == 'disabled' ? ' disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Dropdown - Single' ? ' onclick="' . $onClick . '"' : '') . ' >' . htmlspecialchars(html_entity_decode($attribute['products_options_values_name'], ENT_QUOTES)) . $numberOfProductsLeft . '</option>';
            break;
        }
        ++$attrCount;
      }
    }
    $savValue = $attribute['products_options_values_name'];
    $savName = $attribute['products_options_name'];
  }
  if ($savName != "") {
    $content .= '<hr width="90%" size="0" />';
    $content .= '<div>';
    $content .= '<div class="dFilter">';
    $content .= '<p class="dFilterHeading">' . DYNAMIC_FILTER_TEXT_PREFIX . htmlspecialchars(html_entity_decode($savName, ENT_QUOTES)) . DYNAMIC_FILTER_TEXT_SUFFIX . '</p>';
    if (isset($_GET[$group]) && array_filter($_GET[$group])) {
      $content .= '<div class="dFilterClear"><a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array($group)), 'NONSSL') . '">' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'clear_filter.png', DYNAMIC_FILTER_BUTTON_CLEAR_FILTER_ALT) . '</a></div>';
    }
    if (strtok(FILTER_STYLE, " ") == 'Dropdown') {
      $content .= '<select name="' . $group . '[]" class="dFilterDrop"' . (FILTER_STYLE == 'Dropdown - Single' ? ' onchange="this.form.submit();"' : '') . '>' . '<option value=""' . (!isset($_GET[$group]) || !array_filter($_GET[$group]) ? ' selected="selected"' : '') . '>' . $dropdownDefault . '</option>';
      $content .= $filters;
      $content .= '</select>';
    } else {
      $content .= '<ul' . ($attrCount > FILTER_MAX_OPTIONS ? (FILTER_OPTIONS_STYLE == 'Scroll' ? ' class="dFilterScroll">' : ' class="dFilterExpand">') : '>');
      $content .= $filters;
      $content .= '</ul>';
    }
  }
  if (FILTER_OPTIONS_STYLE == 'Expand' && $attrCount > FILTER_MAX_OPTIONS) {
    $content .= '<a class="dFilterToggle" href="#">' . TEXT_DYNAMIC_FILTER_SHOW_MORE . zen_image(DIR_WS_TEMPLATE_IMAGES . 'arrow_more.gif', TEXT_DYNAMIC_FILTER_SHOW_MORE, '', '', 'class="dFilterToggleImg"') . '</a>';
  }
  $content .= '</div>';
  $content .= '</div>';
}

/*
 * end attribute link/check boxes
 */

/*
 * start filter buttons
 */

if (FILTER_STYLE == 'Dropdown - Multi' || FILTER_STYLE == 'Checkbox - Multi') {
  $content .= '<div id="dFilterButton">';
  $content .= zen_image_submit('button_filter.png', DYNAMIC_FILTER_BUTTON_FILTER_ALT) . '<br />';
  $content .= '</div>';
}
$content .= '<div id="dFilterClearAll">';

$resetParms = array_filter($resetParms);

foreach ($resetParms as $reset) {
  if (isset($_GET[$reset])) {
    $content .= '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params($resetParms), 'NONSSL') . '">' . zen_image_button('button_clear_all_filters.png', DYNAMIC_FILTER_BUTTON_CLEAR_ALL_FILTER_ALT) . '</a>';
    break;
  }
}
$content .= '</div>';

/*
 * end filter buttons
 */

$content .= '</form>';
$content .= '</div>';
