<?php

/*
 * dynamic_filter.php
 *
 * Zen Cart dynamic filter module
 * Damian Taylor, March 2010
 *
 */

if (FILTER_CATEGORY == 'Yes' && $current_page_base == 'index'
                             && !$this_is_home_page
                             && ($category_depth == 'products'
                             || $category_depth == 'top')
   || (FILTER_ALL == 'Yes' && $current_page_base == 'products_all')
   || (FILTER_NEW == 'Yes' && $current_page_base == 'products_new')
   || (FILTER_FEATURED == 'Yes' && $current_page_base == 'featured_products')
   || (FILTER_SPECIALS == 'Yes' && $current_page_base == 'specials')
   || (FILTER_SEARCH == 'Yes' && $current_page_base == 'advanced_search_result')) {

//if (defined('CEON_URI_MAPPING_ENABLED') && CEON_URI_MAPPING_ENABLED == 1) $pageName = preg_replace('{^.*/([^\?]+)\??.*$}', '$1', $_SERVER['REQUEST_URI']);
  $pageName = substr(strrchr($breadcrumb->trail('/'), "/"), 1);
  if (empty($currency_type)) {
    $currency_type = $_SESSION['currency'];
  }
  $currency_symbol = $currencies->currencies[$currency_type]['symbol_left'];
  $conversion_rate = $currencies->get_value($_SESSION['currency']);
  $resetParms = array();
  $display_limit = zen_get_new_date_range();
  if (FILTER_GOOGLE_TRACKING == 'Asynchronous') {
    $trackingStart = '_gaq.push(["_trackEvent", ';
    $trackingEnd = ']);';
  } else if (FILTER_GOOGLE_TRACKING == 'ga.js') {
    $trackingStart = 'pageTracker._trackEvent(';
    $trackingEnd = ');';
  } else if (FILTER_GOOGLE_TRACKING == 'Universal') {
    echo '';
  }

  // use $listing_sql to populate dynamic filter
  $query_lower = strtolower($listing_sql);
  $pos_from = strpos($query_lower, ' from', 0);
  $pos_where = strpos($query_lower, ' where', 0);
  $pos_group = strpos($query_lower, ' group by', 0);
  $pos_to = strlen($query_lower);
  if ($pos_group == 0) {
    $pos_group = $pos_to;
  }

// list filtered and unfiltered products for category
  $unfiltered = $db->Execute(str_replace(array($filter, $having), array("", ""), "SELECT p.products_id, p.products_price_sorter, p.master_categories_id, p.manufacturers_id" . substr($listing_sql, $pos_from, ($pos_where - $pos_from)) . substr($listing_sql, $pos_where, ($pos_group - $pos_where))));

  $filtered = $db->Execute("SELECT p.products_id, p.products_price_sorter, p.master_categories_id, p.manufacturers_id" . substr($listing_sql, $pos_from, ($pos_where - $pos_from)) . substr($listing_sql, $pos_where, ($pos_to - $pos_where)));

  if ($filtered->RecordCount() == 0) {
    $filtered = $db->Execute(str_replace(array($filter, $having), array("", ""), "SELECT p.products_id, p.products_price_sorter, p.master_categories_id, p.manufacturers_id" . substr($listing_sql, $pos_from, ($pos_where - $pos_from)) . substr($listing_sql, $pos_where, ($pos_group - $pos_where))));
  }
// retrieve filtered and unfiltered product options
  $min = 0;
  $max = 0;
  while (!$unfiltered->EOF) {
    if ($min == 0 or round($unfiltered->fields['products_price_sorter'], 2) < $min) {
      $min = round($unfiltered->fields['products_price_sorter'], 2);
    }
    if (round($unfiltered->fields['products_price_sorter'], 2) > $max) {
      $max = round($unfiltered->fields['products_price_sorter'], 2);
    }
    $unfilteredProducts[] = $unfiltered->fields['products_id'];
    $unfilteredManufacturers[] = $unfiltered->fields['manufacturers_id'];
    $unfilteredCategories[] = $unfiltered->fields['master_categories_id'];

    $unfiltered->MoveNext();
  }
  while (!$filtered->EOF) {
    $priceArray[] = round($filtered->fields['products_price_sorter'], 2);
    $filteredProducts[] = $filtered->fields['products_id'];
    $filteredManufacturers[] = $filtered->fields['manufacturers_id'];
    $filteredCategories[] = $filtered->fields['master_categories_id'];

    $filtered->MoveNext();
  }

  if (count($unfilteredManufacturers) > 1) {
    $unfilteredManufacturers = array_filter(array_unique($unfilteredManufacturers));
  }
  if (count($unfilteredCategories) > 1) {
    $unfilteredCategories = array_filter(array_unique($unfilteredCategories));
  }
  if (count($unfilteredProducts) > 1) {
    $unfilteredProducts = array_filter(array_unique($unfilteredProducts));
  }
  if (count($filteredManufacturers) > 1) {
    $filteredManufacturers = array_filter(array_unique($filteredManufacturers));
  }
  if (count($filteredCategories) > 1) {
    $filteredCategories = array_filter(array_unique($filteredCategories));
  }
  if (count($filteredProducts) > 1) {
    $filteredProducts = array_filter(array_unique($filteredProducts));
  }
  if (count($priceArray) > 1) {
    $priceArray = array_filter(array_unique($priceArray));
  }

  if (PRODUCT_LIST_FILTER == 0) {
    if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] != '' || $current_page_base == 'products_all' || $current_page_base == 'products_new' || $current_page_base == 'specials' || $current_page_base == 'featured_products' || $current_page_base == 'advanced_search_result') {
      if (count($unfilteredCategories) > 0) {
        $resetParms[] = $group;
        $parameters = zen_get_all_get_params();
        $dropdownDefault = str_replace('%n', DYNAMIC_FILTER_CATEGORY_GROUP, DYNAMIC_FILTER_DROPDOWN_DEFAULT);

// BOF language fix by a_berezin
        $categories = $db->Execute("SELECT cd.categories_id, cd.categories_name,
                                    IF(cd.categories_id IN (" . implode(',', $filteredCategories) . "), 'Y', 'N') AS flag
                                    FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                    WHERE cd.categories_id IN (" . implode(',', $unfilteredCategories) . ")" . "
                                    AND cd.language_id=" . (int)$_SESSION['languages_id'] . "
                                    AND c.categories_id = cd.categories_id
                                    ORDER BY c.sort_order, cd.categories_name");
// EOF language fix
      }
    }
  }
  if (!isset($_GET['manufacturers_id'])) {
    if (count($unfilteredManufacturers) > 0) {
      $resetParms[] = $group;
      $parameters = zen_get_all_get_params(array($group));
      $dropdownDefault = str_replace('%n', DYNAMIC_FILTER_MANUFACTURER_GROUP, DYNAMIC_FILTER_DROPDOWN_DEFAULT);

// BOF fix by a_berezin
      if (sizeof($filteredManufacturers) > 0) {
        $manufacturers = $db->Execute("SELECT manufacturers_id, manufacturers_name,
                                       IF(manufacturers_id IN(" . implode(',', $filteredManufacturers) . "), 'Y', 'N') AS flag
                                       FROM " . TABLE_MANUFACTURERS . "
                                       WHERE manufacturers_id IN (" . implode(',', $unfilteredManufacturers) . ")
                                       ORDER BY manufacturers_name");
      } else {
        $manufacturers = $db->Execute("SELECT manufacturers_id, manufacturers_name, 'N' AS flag
                                       FROM " . TABLE_MANUFACTURERS . "
                                       WHERE manufacturers_id IN (" . implode(',', $unfilteredManufacturers) . ")" . "
                                       ORDER BY manufacturers_name");
      }
// EOF fix
    }
  }
  if (count($filteredProducts) > 0) {
    // Below line counts up all quantities of each item. e.g. if a glove is available in Small and Medium, quantity = 2.
    //$attributes = $db->Execute("SELECT po.products_options_name, pov.products_options_values_name, count( p2as.quantity ) as quantity" .
// BOF language fix by a_berezin
    $attributes = $db->Execute("SELECT count(DISTINCT p2a.products_id) AS quantity, po.products_options_name, pov.products_options_values_name,
                                SUM(IF(p2a.products_id IN(" . implode(',', $filteredProducts) . "), 1, 0)) AS flag
                                FROM " . TABLE_PRODUCTS_ATTRIBUTES . " p2a
                                JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON p2a.options_id = po.products_options_id
                                AND po.language_id=" . (int)$_SESSION['languages_id'] . "
                                JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON p2a.options_values_id = pov.products_options_values_id
                                AND pov.language_id=" . (int)$_SESSION['languages_id'] .
                                (defined('TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK') ? "
                                  JOIN " . TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK . " p2as ON p2a.products_id = p2as.products_id
                                  AND p2as.stock_attributes LIKE CONCAT('%', p2a.products_attributes_id, '%')" : "") . "
                                WHERE p2a.products_id IN (" . implode(',', $unfilteredProducts) . ")" . 
                                (FILTER_OPTIONS_INCLUDE != '' ? " AND p2a.options_id IN (" . FILTER_OPTIONS_INCLUDE . ")" : '') .
                                (FILTER_OPTIONS_EXCLUDE != '' ? " AND p2a.options_id NOT IN (" . FILTER_OPTIONS_EXCLUDE . ")" : '') .
                                (defined('TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK') ? "
                                  AND p2as.quantity > 0" : "") . "
                                AND po.products_options_type != '1'
                                AND po.products_options_type != '4'
                                GROUP BY po.products_options_name, pov.products_options_values_name
                                ORDER BY po.products_options_name, pov.products_options_values_sort_order");
// EOF language fix
if(FILTER_OPTIONS_LEFT == 'Yes'){
  $numberOfProductsLeft = '&nbsp;<span class="numberOfProductsLeft">(' . htmlspecialchars(html_entity_decode($attributes->fields['flag'], ENT_QUOTES)) . ')</span>';
} else {
  $numberOfProductsLeft = '';
}
    $savName = '';
    $savValue = '';
  }
  if (isset($attributes) && ($attributes->RecordCount() > 0)) {
    $title_link = false;
    require($template->get_template_dir('tpl_dynamic_filter.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_dynamic_filter.php');
    $title = BOX_HEADING_DYNAMIC_FILTER;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default);
  }
}
