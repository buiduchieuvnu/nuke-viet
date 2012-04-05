<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES.,JSC. All rights reserved
 * @Createdate 2-9-2010 14:43
 */
 
if ( ! defined( 'NV_IS_FILE_ADMIN' ) ) die( 'Stop!!!' );

$q = filter_text_input( 'q', 'get', "", 1 );
if ( empty( $q ) ) return;

$sql = "SELECT link FROM `" . NV_PREFIXLANG . "_" . $module_data . "_sources` WHERE  `title` LIKE '%" . $db->dblikeescape( $q ) . "%' OR `link` LIKE '%" . $db->dblikeescape( $q ) . "%' ORDER BY `weight` ASC";
$result = $db->sql_query( $sql );

while ( list( $link ) = $db->sql_fetchrow( $result ) )
{
    echo "" . $link . "\n";
}

?>