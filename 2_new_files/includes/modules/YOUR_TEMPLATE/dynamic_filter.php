<?php

// Price Range/Attribute filter
$manufacturerGroup = str_replace(' ', '', DYNAMIC_FILTER_MANUFACTURER_GROUP);
$categoryGroup = str_replace(' ', '', DYNAMIC_FILTER_CATEGORY_GROUP);
$priceGroup = str_replace(' ', '', DYNAMIC_FILTER_PRICE_GROUP);
$prvKey = '';
$prvHaving = '';
$filter = '';
$having = '';
$filter_attr = false;

reset($_GET);
foreach($_GET as $key => $value) {
  if (substr($key, 0, strlen(DYNAMIC_FILTER_PREFIX)) == DYNAMIC_FILTER_PREFIX && array_filter($value)) {
    $key = str_replace(DYNAMIC_FILTER_PREFIX, '', $key);
    foreach ($value as $value) {

      if ($key == $manufacturerGroup || $key == $categoryGroup || $key == $priceGroup) {
        if ($key != $prvKey) {
          if ($prvKey != '') {
            $filter .= ') AND (';
          } else {
            $filter .= ' AND (';
          }
        } else {
          $filter .= ' OR ';
        }
      }
      // manufacturer
      if ($key == $manufacturerGroup) {
        $filter .= 'm.manufacturers_id = ' . (int)$value;
        $prvKey = $key;
        // category
      } else if ($key == $categoryGroup) {
        $filter .= 'p2c.categories_id = ' . (int)$value;
        $prvKey = $key;
        // price range
      } else if ($key == $priceGroup) {
        list($low, $high) = explode("--", $value);
        $filter .= 'p.products_price_sorter >= ' . $low . ' AND ' . 'p.products_price_sorter <= ' . $high; // @todo Add tax when needed
        $prvKey = $key;
        // attributes
      } else {
        if ($key != $prvHaving) {
          if ($prvHaving != '') {
            $having .= ') AND (';
          } else {
            $having .= 'HAVING (';
          }
        } else {
          $having .= ' OR ';
        }

// BOF fix to escape special characters in query by kevin_205 & design75
        $having .= ' FIND_IN_SET("' . $key . addslashes($value) . '", GROUP_CONCAT(CONCAT(REPLACE(po.products_options_name, " ", ""), pov.products_options_values_name)))';
// EOF fix
        $filter_attr = true;
        $prvHaving = $key;
      }
    }
  }
}
if ($filter != '') {
  $filter .= ')';
}
if ($having != '') {
  $having .= ')';
}
if ($filter_attr == true && defined('TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK')) {
  $filter .= ' AND p2as.quantity > 0 AND FIND_IN_SET(p2a.products_attributes_id, p2as.stock_attributes)';
}
