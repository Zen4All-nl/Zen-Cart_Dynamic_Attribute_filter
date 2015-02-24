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
$content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent">';
$content .= zen_draw_form('product_filter_form', '', 'get');

// draw hidden fields
reset($_GET);
while (list($key, $value) = each($_GET)) {
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
      while (!$categories->EOF) {
        if (isset($_GET[$group]) && in_array($categories->fields['categories_id'], $_GET[$group])) {
          $linkClass = 'selected';
        } else if ($categories->fields['flag'] == 'N') {
          $linkClass = 'disabled';
        } else {
          $linkClass = 'enabled';
        }

        $onClick = '';
        if (FILTER_GOOGLE_TRACKING != 'No') {
          $onClick .= $trackingStart . '"filterAction", "' . ($linkClass != 'selected' ? 'addFilter' : 'removeFilter') . '", "' . $pageName . ';' . DYNAMIC_FILTER_CATEGORY_GROUP . '=' . $categories->fields['categories_name'] . '"' . $trackingEnd;
        }
        if (FILTER_STYLE == 'Checkbox - Single') {
          $onClick .= ' this.form.submit();';
        }

        if (FILTER_METHOD != 'Hidden' || $linkClass != 'disabled') {
          $hrefLink = $group . '[]=' . $categories->fields['categories_id'];
          switch (strtok(FILTER_STYLE, " ")) {
            case 'Checkbox':
              $content .= '<li class="dFilterLink">' . zen_draw_checkbox_field($group . '[]', $categories->fields['categories_id'], (isset($_GET[$group]) && in_array($categories->fields['categories_id'], $_GET[$group]) ? true : false), ($linkClass == 'disabled' ? 'disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Checkbox - Single' ? ' onclick="' . $onClick . '"' : '')) . $categories->fields['categories_name'] . '</li>';
              break;
            case 'Link':
              $content .= '<li class="dFilterLink"><a class="' . $linkClass . '"' . ($linkClass != 'disabled' ? ' rel="nofollow" href="' . zen_href_link($_GET['main_page'], ($linkClass != 'selected' ? $parameters . $hrefLink : str_replace(array($hrefLink, '&' . $hrefLink), array("", ""), $parameters)), 'NONSSL') . '"' . ($onClick != '' ? ' onclick="' . $onClick . '"' : '') : '') . ' >' . $categories->fields['categories_name'] . '</a></li>';
              break;
            case 'Dropdown':
              $content .= '<option value="' . $categories->fields['categories_id'] . '"' . ($linkClass == 'selected' ? ' selected="selected"' : '') . ($linkClass == 'disabled' ? ' disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Dropdown - Single' ? ' onclick="' . $onClick . '"' : '') . ' >' . $categories->fields['categories_name'] . '</option>';
              break;
          }
        }
        $categories->MoveNext();
      }
      if (strtok(FILTER_STYLE, " ") == 'Dropdown') {
        $content .= '</select>';
      } else {
        $content .= '</ul>';
      }
      if (FILTER_OPTIONS_STYLE == 'Expand' && count($unfilteredCategories) > FILTER_MAX_OPTIONS) {
        $content .= '<a class="dFilterToggle" href="#">' . TEXT_DYNAMIC_FILTER_SHOW_MORE . zen_image(DIR_WS_TEMPLATE_IMAGES . 'arrow_more.gif', TEXT_DYNAMIC_FILTER_SHOW_MORE, '', '', 'class="dFilterToggleImg"') . '</a>';
      }
      $content .= '</div></div>';
    }
  }
  if (!isset($_GET['manufacturers_id'])) {
    if (count($unfilteredManufacturers) > 0) {
      $content .= '<hr width="90%" size="0" />';
      $content .= '<div><div class="dFilter"><p class="dFilterHeading">' . DYNAMIC_FILTER_TEXT_PREFIX . DYNAMIC_FILTER_TEXT_MANUFACTURER . DYNAMIC_FILTER_TEXT_SUFFIX . '</p>';
      if (isset($_GET[$group]) && array_filter($_GET[$group])) {
        $content .= '<div class="dFilterClear"><a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array($group)), 'NONSSL') . '">' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'clear_filter.png', DYNAMIC_FILTER_BUTTON_CLEAR_FILTER_ALT) . '</a></div>';
      }
      if (strtok(FILTER_STYLE, " ") == 'Dropdown') {
        $content .= '<select name="' . $group . '[]" class="dFilterDrop"' . (FILTER_STYLE == 'Dropdown - Single' ? ' onchange="this.form.submit();"' : '') . '>' . '<option value=""' . (!isset($_GET[$group]) || !array_filter($_GET[$group]) ? ' selected="selected"' : '') . '>' . $dropdownDefault . '</option>';
      } else {
        $content .= '<ul' . (count($unfilteredManufacturers) > FILTER_MAX_OPTIONS ? (FILTER_OPTIONS_STYLE == 'Scroll' ? ' class="dFilterScroll">' : ' class="dFilterExpand">') : '>');
      }
      while (!$manufacturers->EOF) {
        if (isset($_GET[$group]) && in_array($manufacturers->fields['manufacturers_id'], $_GET[$group])) {
          $linkClass = 'selected';
        } else if ($manufacturers->fields['flag'] == 'N') {
          $linkClass = 'disabled';
        } else {
          $linkClass = 'enabled';
        }

        $onClick = '';
        if (FILTER_GOOGLE_TRACKING != 'No') {
          $onClick .= $trackingStart . '"filterAction", "' . ($linkClass != 'selected' ? 'addFilter' : 'removeFilter') . '", "' . $pageName . ';' . DYNAMIC_FILTER_MANUFACTURER_GROUP . '=' . $manufacturers->fields['manufacturers_name'] . '"' . $trackingEnd;
        }
        if (FILTER_STYLE == 'Checkbox - Single') {
          $onClick .= ' this.form.submit();';
        }

        if (FILTER_METHOD != 'Hidden' || $linkClass != 'disabled') {
          $hrefLink = $group . '[]=' . $manufacturers->fields['manufacturers_id'];
          switch (strtok(FILTER_STYLE, " ")) {
            case 'Checkbox':
              $content .= '<li class="dFilterLink">' . zen_draw_checkbox_field($group . '[]', $manufacturers->fields['manufacturers_id'], (isset($_GET[$group]) && in_array($manufacturers->fields['manufacturers_id'], $_GET[$group]) ? true : false), ($linkClass == 'disabled' ? 'disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Checkbox - Single' ? ' onclick="' . $onClick . '"' : '')) . $manufacturers->fields['manufacturers_name'] . '</li>';
              break;
            case 'Link':
              $content .= '<li class="dFilterLink"><a class="' . $linkClass . '"' . ($linkClass != 'disabled' ? ' rel="nofollow" href="' . zen_href_link($_GET['main_page'], ($linkClass != 'selected' ? $parameters . $hrefLink : str_replace(array($hrefLink, '&' . $hrefLink), array("", ""), $parameters)), 'NONSSL') . '"' . ($onClick != '' ? ' onclick="' . $onClick . '"' : '') : '') . ' >' . $manufacturers->fields['manufacturers_name'] . '</a></li>';
              break;
            case 'Dropdown':
              $content .= '<option value="' . $manufacturers->fields['manufacturers_id'] . '"' . ($linkClass == 'selected' ? ' selected="selected"' : '') . ($linkClass == 'disabled' ? ' disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Dropdown - Single' ? ' onclick="' . $onClick . '"' : '') . ' >' . $manufacturers->fields['manufacturers_name'] . '</option>';
              break;
          }
        }
        $manufacturers->MoveNext();
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
    $content .= '<hr width="90%" size="0" />';
    $content .= '<div><div class="dFilter"><p class="dFilterHeading">' . DYNAMIC_FILTER_TEXT_PREFIX . DYNAMIC_FILTER_TEXT_PRICE . DYNAMIC_FILTER_TEXT_SUFFIX . '</p>';
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
  while (!$attributes->EOF) {
    // output if option name changes!!!
    if ($attributes->fields['products_options_name'] != $savName) {
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

      $group = DYNAMIC_FILTER_PREFIX . str_replace(' ', '', $attributes->fields['products_options_name']);
      $resetParms[] = $group;
      $parameters = zen_get_all_get_params();
      $dropdownDefault = str_replace('%n', $attributes->fields['products_options_name'], DYNAMIC_FILTER_DROPDOWN_DEFAULT);
      $filters = '';
      $attrCount = 0;
    }

    if ($attributes->fields['products_options_values_name'] != $savValue) {
      if (isset($_GET[$group]) && in_array($attributes->fields['products_options_values_name'], $_GET[$group])) {
        $linkClass = 'selected';
      } else if (isset($_GET[$group]) && array_filter($_GET[$group]) && !in_array($attributes->fields['products_options_values_name'], $_GET[$group]) || $attributes->fields['flag'] == 0) {
        $linkClass = 'disabled';
        //} else if ($attributes->fields['flag'] == 0) { $linkClass = 'disabled';
      } else {
        $linkClass = 'enabled';
      }

      $onClick = '';
      if (FILTER_GOOGLE_TRACKING != 'No') {
        $onClick .= $trackingStart . '"filterAction", "' . ($linkClass != 'selected' ? 'addFilter' : 'removeFilter') . '", "' . $pageName . ';' . $attributes->fields['products_options_name'] . '=' . htmlspecialchars(html_entity_decode($attributes->fields['products_options_values_name'], ENT_QUOTES)) . '"' . $trackingEnd;
      }
      if (FILTER_STYLE == 'Checkbox - Single') {
        $onClick .= ' this.form.submit();';
      }

      if (FILTER_METHOD != 'Hidden' || $linkClass != 'disabled') {
        $hrefLink = $group . '[]=' . rawurlencode($attributes->fields['products_options_values_name']);
        switch (strtok(FILTER_STYLE, " ")) {
          case 'Checkbox':
            $filters .= '<li class="dFilterLink">' . zen_draw_checkbox_field($group . '[]', $attributes->fields['products_options_values_name'], (isset($_GET[$group]) && in_array($attributes->fields['products_options_values_name'], $_GET[$group]) ? true : false), ($linkClass == 'disabled' ? 'disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Checkbox - Single' ? ' onclick="' . $onClick . '"' : '')) . '&nbsp;' . htmlspecialchars(html_entity_decode($attributes->fields['products_options_values_name'], ENT_QUOTES)) . '&nbsp;<span class="no_of_attributes">(' . htmlspecialchars(html_entity_decode($attributes->fields['flag'], ENT_QUOTES)) . ')</span>' . '</li>';
            break;
          case 'Link':
            $filters .= '<li class="dFilterLink"><a class="' . $linkClass . '"' . ($linkClass != 'disabled' ? ' rel="nofollow" href="' . zen_href_link($_GET['main_page'], ($linkClass != 'selected' ? $parameters . $hrefLink : str_replace(array($hrefLink, '&' . $hrefLink), array("", ""), $parameters)), 'NONSSL') . '"' . ($onClick != '' ? ' onclick="' . $onClick . '"' : '') : '') . ' >' . htmlspecialchars(html_entity_decode($attributes->fields['products_options_values_name'], ENT_QUOTES)) . ' {' . htmlspecialchars(html_entity_decode($attributes->fields['flag'], ENT_QUOTES)) . '}' . '</a></li>';
            break;
          case 'Dropdown':
            $filters .= '<option value="' . htmlspecialchars(html_entity_decode($attributes->fields['products_options_values_name'], ENT_QUOTES)) . '"' . ($linkClass == 'selected' ? ' selected="selected"' : '') . ($linkClass == 'disabled' ? ' disabled="disabled"' : '') . ($onClick != '' && FILTER_STYLE == 'Dropdown - Single' ? ' onclick="' . $onClick . '"' : '') . ' >' . $attributes->fields['products_options_values_name'] . ' {' . htmlspecialchars(html_entity_decode($attributes->fields['flag'], ENT_QUOTES)) . '}' . '</option>';
            break;
        }
        ++$attrCount;
      }
    }
    $savValue = $attributes->fields['products_options_values_name'];
    $savName = $attributes->fields['products_options_name'];
    $attributes->MoveNext();
  }
  if ($savName != "") {
    $content .= '<hr width="90%" size="0" />';
    $content .= '<div><div class="dFilter"><p class="dFilterHeading">' . DYNAMIC_FILTER_TEXT_PREFIX . htmlspecialchars(html_entity_decode($savName, ENT_QUOTES)) . DYNAMIC_FILTER_TEXT_SUFFIX . '</p>';
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
