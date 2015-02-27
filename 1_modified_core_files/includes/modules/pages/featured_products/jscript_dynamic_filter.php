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

if (FILTER_FEATURED == 'Yes' && FILTER_STYLE != 'Dropdown' && FILTER_OPTIONS_STYLE == 'Expand') {
echo '<script type="text/javascript" src="' . DIR_WS_TEMPLATE  . 'jscript/dynamic_filter/jquery.dynamic_filter.php"></script>';
}