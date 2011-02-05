<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES.,JSC. All rights reserved
 * @Createdate 2-9-2010 14:43
 */
if ( ! defined( 'NV_IS_FILE_ADMIN' ) ) die( 'Stop!!!' );
$page_title = $lang_module['categories'];

$error = $admins = "";
$savecat = 0;
list( $catid, $parentid, $title, $alias, $description, $keywords, $who_view, $groups_view ) = array( 
    0, 0, "", "", "", "", 0, "" 
);
$groups_list = nv_groups_list();
$savecat = $nv_Request->get_int( 'savecat', 'post', 0 );
if ( ! empty( $savecat ) )
{
    $catid = $nv_Request->get_int( 'catid', 'post', 0 );
    $parentid_old = $nv_Request->get_int( 'parentid_old', 'post', 0 );
    $parentid = $nv_Request->get_int( 'parentid', 'post', 0 );
    $title = filter_text_input( 'title', 'post', '', 1 );
    $keywords = filter_text_input( 'keywords', 'post', '', 1 );
    $alias = filter_text_input( 'alias', 'post', '' );
    $description = $nv_Request->get_string( 'description', 'post', '' );
    $description = nv_nl2br( nv_htmlspecialchars( strip_tags( $description ) ), '<br />' );
    $alias = ( $alias == "" ) ? change_alias( $title ) : change_alias( $alias );
    
    $who_view = $nv_Request->get_int( 'who_view', 'post', 0 );
    $groups_view = "";
    
    $groups = $nv_Request->get_typed_array( 'groups_view', 'post', 'int', array() );
    $groups = array_intersect( $groups, array_keys( $groups_list ) );
    $groups_view = implode( ",", $groups );
    
    if ( ! defined( 'NV_IS_ADMIN_MODULE' ) )
    {
        if ( ! ( isset( $array_cat_admin[$admin_id][$parentid] ) and $array_cat_admin[$admin_id][$parentid]['admin'] == 1 ) )
        {
            Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=" . $op . "&parentid=" . $parentid . "" );
            die();
        }
    }
    
    if ( $catid == 0 and $title != "" )
    {
        list( $weight ) = $db->sql_fetchrow( $db->sql_query( "SELECT max(`weight`) FROM `" . NV_PREFIXLANG . "_" . $module_data . "_cat` WHERE `parentid`=" . $db->dbescape( $parentid ) . "" ) );
        $weight = intval( $weight ) + 1;
        $viewcat = "viewcat_page_new";
        $subcatid = "";
        $query = "INSERT INTO `" . NV_PREFIXLANG . "_" . $module_data . "_cat` (`catid`, `parentid`, `title`, `alias`, `description`, `image`, `thumbnail`, `weight`, `order`, `lev`, `viewcat`, `numsubcat`, `subcatid`, `inhome`, `numlinks`, `keywords`, `admins`, `add_time`, `edit_time`, `del_cache_time`, `who_view`, `groups_view`)
         VALUES (NULL, " . $db->dbescape( $parentid ) . ", " . $db->dbescape( $title ) . ", " . $db->dbescape( $alias ) . ", " . $db->dbescape( $description ) . ", '', '', " . $db->dbescape( $weight ) . ", '0', '0', " . $db->dbescape( $viewcat ) . ", '0', " . $db->dbescape( $subcatid ) . ", '1', '3', " . $db->dbescape( $keywords ) . ", " . $db->dbescape( $admins ) . ", UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP() + 26000000, " . $db->dbescape( $who_view ) . "," . $db->dbescape( $groups_view ) . ")";
        $newcatid = intval( $db->sql_query_insert_id( $query ) );
        if ( $newcatid > 0 )
        {
            $db->sql_freeresult();
            nv_create_table_rows( $newcatid );
            nv_fix_cat_order();
            if ( ! defined( 'NV_IS_ADMIN_MODULE' ) )
            {
                $db->sql_query( "INSERT INTO `" . NV_PREFIXLANG . "_" . $module_data . "_admins` (`userid`, `catid`, `admin`, `add_content`, `pub_content`, `edit_content`, `del_content`, `comment`) VALUES ('" . $admin_id . "', '" . $newcatid . "', '1', '1', '1', '1', '1', '1')" );
            }
            nv_del_moduleCache( $module_name );
            nv_insert_logs( NV_LANG_DATA, $module_name,$lang_module['add_cat'], $title, $admin_info['userid'] );
            Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=" . $op . "&parentid=" . $parentid . "" );
            die();
        }
        else
        {
            $error = $lang_module['errorsave'];
        }
    }
    elseif ( $catid > 0 and $title != "" )
    {
        $query = "UPDATE `" . NV_PREFIXLANG . "_" . $module_data . "_cat` SET `parentid`=" . $db->dbescape( $parentid ) . ", `title`=" . $db->dbescape( $title ) . ", `alias` =  " . $db->dbescape( $alias ) . ", `description`=" . $db->dbescape( $description ) . ", `keywords`= " . $db->dbescape( $keywords ) . ", `who_view`=" . $db->dbescape( $who_view ) . ", `groups_view`=" . $db->dbescape( $groups_view ) . ", `edit_time`=UNIX_TIMESTAMP( ) WHERE `catid` =" . $catid . "";
        $db->sql_query( $query );
        if ( $db->sql_affectedrows() > 0 )
        {
            $db->sql_freeresult();
            if ( $parentid != $parentid_old )
            {
                list( $weight ) = $db->sql_fetchrow( $db->sql_query( "SELECT max(`weight`) FROM `" . NV_PREFIXLANG . "_" . $module_data . "_cat` WHERE `parentid`=" . $db->dbescape( $parentid ) . "" ) );
                $weight = intval( $weight ) + 1;
                $sql = "UPDATE `" . NV_PREFIXLANG . "_" . $module_data . "_cat` SET `weight`=" . $weight . " WHERE `catid`=" . intval( $catid );
                $db->sql_query( $sql );
                nv_fix_cat_order();
                nv_insert_logs( NV_LANG_DATA, $module_name,$lang_module['edit_cat'], $title, $admin_info['userid'] );
            }
            nv_del_moduleCache( $module_name );
            Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=" . $op . "&parentid=" . $parentid . "" );
            die();
        }
        else
        {
            $error = $lang_module['errorsave'];
        }
        $db->sql_freeresult();
    }
    else
    {
        $error = $lang_module['error_name'];
    }
}

$parentid = $nv_Request->get_int( 'parentid', 'get,post', 0 );

$contents = "<div id=\"module_show_list\">";
$contents .= nv_show_cat_list( $parentid );
$contents .= "</div><br />\n";

$catid = $nv_Request->get_int( 'catid', 'get', 0 );
if ( $catid > 0 and isset( $global_array_cat[$catid] ) )
{
    $parentid = $global_array_cat[$catid]['parentid'];
    $title = $global_array_cat[$catid]['title'];
    $alias = $global_array_cat[$catid]['alias'];
    $description = $global_array_cat[$catid]['description'];
    $keywords = $global_array_cat[$catid]['keywords'];
    $who_view = $global_array_cat[$catid]['who_view'];
    $groups_view = $global_array_cat[$catid]['groups_view'];
    
    if ( ! defined( 'NV_IS_ADMIN_MODULE' ) )
    {
        if ( ! ( isset( $array_cat_admin[$admin_id][$parentid] ) and $array_cat_admin[$admin_id][$parentid]['admin'] == 1 ) )
        {
            Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=" . $op . "&parentid=" . $parentid . "" );
            die();
        }
    }
    
    $caption = $lang_module['edit_cat'];
    $array_in_cat = GetCatidInParent( $catid );
}
else
{
    $caption = $lang_module['add_cat'];
    $array_in_cat = array();
}
$groups_view = explode( ",", $groups_view );

$array_cat_list = array();
if ( defined( 'NV_IS_ADMIN_MODULE' ) )
{
    $array_cat_list[0] = $lang_module['cat_sub_sl'];
}
foreach ( $global_array_cat as $catid_i => $array_value )
{
    $lev_i = $array_value['lev'];
    if ( defined( 'NV_IS_ADMIN_MODULE' ) or ( isset( $array_cat_admin[$admin_id][$catid_i] ) and $array_cat_admin[$admin_id][$catid_i]['admin'] == 1 ) )
    {
        $xtitle_i = "";
        if ( $lev_i > 0 )
        {
            $xtitle_i .= "&nbsp;&nbsp;&nbsp;|";
            for ( $i = 1; $i <= $lev_i; $i ++ )
            {
                $xtitle_i .= "---";
            }
            $xtitle_i .= ">&nbsp;";
        }
        $xtitle_i .= $array_value['title'];
        $array_cat_list[$catid_i] = $xtitle_i;
    }
}

$contents .= "<div id=\"edit\">";
if ( $error != "" )
{
    $contents .= "<div class=\"quote\" style=\"width:780px;\">\n";
    $contents .= "<blockquote class=\"error\"><span>" . $error . "</span></blockquote>\n";
    $contents .= "</div>\n";
    $contents .= "<div class=\"clear\"></div>\n";
}
if ( ! empty( $array_cat_list ) )
{
    $a = 0;
    $contents .= "<form action=\"" . NV_BASE_ADMINURL . "index.php\" method=\"post\">";
    $contents .= "<input type=\"hidden\" name =\"" . NV_NAME_VARIABLE . "\"value=\"" . $module_name . "\" />";
    $contents .= "<input type=\"hidden\" name =\"" . NV_OP_VARIABLE . "\"value=\"" . $op . "\" />";
    $contents .= "<input type=\"hidden\" name =\"catid\" value=\"" . $catid . "\" />";
    $contents .= "<input type=\"hidden\" name =\"parentid_old\" value=\"" . $parentid . "\" />";
    $contents .= "<input name=\"savecat\" type=\"hidden\" value=\"1\" />\n";
    $contents .= "<table summary=\"\" class=\"tab1\">\n";
    $contents .= "<caption>" . $caption . "</caption>\n";
    
    $class = ( $a % 2 == 0 ) ? "" : " class=\"second\"";
    $a ++;
    $contents .= "<tbody" . $class . ">";
    $contents .= "<tr>";
    $contents .= "<td align=\"right\"><strong>" . $lang_module['name'] . ": </strong></td>\n";
    $contents .= "<td><input style=\"width: 600px\" name=\"title\" type=\"text\" value=\"" . $title . "\" maxlength=\"255\" id=\"idtitle\"/></td>\n";
    $contents .= "</tr>";
    $contents .= "</tbody>";
    
    $class = ( $a % 2 == 0 ) ? "" : " class=\"second\"";
    $a ++;
    $contents .= "<tbody" . $class . ">";
    $contents .= "<tr>";
    $contents .= "<td valign=\"top\" align=\"right\"><strong>" . $lang_module['alias'] . ": </strong></td>\n";
    $contents .= "<td><input style=\"width: 550px\" name=\"alias\" type=\"text\" value=\"" . $alias . "\" maxlength=\"255\" id=\"idalias\"/>";
    $contents .= "		<img src=\"" . NV_BASE_SITEURL . "images/refresh.png\" width=\"16\" style=\"cursor: pointer; vertical-align: middle;\" onclick=\"get_alias('cat',".$catid.");\" alt=\"\" height=\"16\" />\n";
    $contents .= "</td>\n";
    $contents .= "</tr>";
    $contents .= "</tbody>";
    $class = ( $a % 2 == 0 ) ? "" : " class=\"second\"";
    $a ++;
    $contents .= "<tbody" . $class . ">";
    $contents .= "<tr>";
    $contents .= "<td align=\"right\"><strong>" . $lang_module['cat_sub'] . ": </strong></td>\n";
    $contents .= "<td>";
    $contents .= "<select name=\"parentid\">\n";
    while ( list( $catid_i, $title_i ) = each( $array_cat_list ) )
    {
        if ( ! in_array( $catid_i, $array_in_cat ) )
        {
            $sl = "";
            if ( $catid_i == $parentid )
            {
                $sl = " selected=\"selected\"";
            }
            $contents .= "<option value=\"" . $catid_i . "\" " . $sl . ">" . $title_i . "</option>\n";
        }
    }
    $contents .= "</select>\n";
    $contents .= "</td>";
    $contents .= "</tr>";
    $contents .= "</tbody>";
    
    $class = ( $a % 2 == 0 ) ? "" : " class=\"second\"";
    $a ++;
    $contents .= "<tbody" . $class . ">";
    $contents .= "<tr>";
    $contents .= "<td align=\"right\"><strong>" . $lang_module['keywords'] . ": </strong></td>\n";
    $contents .= "<td><input style=\"width: 600px\" name=\"keywords\" type=\"text\" value=\"" . $keywords . "\" maxlength=\"255\" /></td>\n";
    $contents .= "</tr>";
    $contents .= "</tbody>";
    
    $class = ( $a % 2 == 0 ) ? "" : " class=\"second\"";
    $a ++;
    $contents .= "<tbody" . $class . ">";
    
    $contents .= "<tr>";
    $contents .= "<td valign=\"top\" align=\"right\"><br /><strong>" . $lang_module['description'] . " </strong></td>\n";
    $contents .= "<td>";
    $contents .= "<textarea style=\"width: 600px\" name=\"description\" cols=\"100\" rows=\"5\">" . $description . "</textarea>";
    $contents .= "</td>";
    $contents .= "</tr>";
    $contents .= "</tbody>";
    
    $class = ( $a % 2 == 0 ) ? "" : " class=\"second\"";
    $a ++;
    $contents .= "<tbody" . $class . ">";
    
    $contents .= "<tr>";
    $contents .= "<td valign=\"top\" align=\"right\"><br /><strong>" . $lang_global['who_view'] . " </strong></td>\n";
    $contents .= "<td>";
    $contents .= "			<div class=\"message_body\">\n";
    $contents .= "				<select name=\"who_view\" id=\"who_view\" onchange=\"nv_sh('who_view','groups_list')\" style=\"width: 250px;\">\n";
    foreach ( $array_who_view as $k => $w )
    {
        $sl = "";
        if ( $who_view == $k ) $sl = " selected=\"selected\"";
        $contents .= "				<option value=\"" . $k . "\" " . $sl . ">" . $w . "</option>\n";
    }
    $contents .= "				</select><br />\n";
    
    $contents .= "				<div id=\"groups_list\" style=\"" . ( $who_view == 3 ? "visibility:visible;display:block;" : "visibility:hidden;display:none;" ) . "\">\n";
    $contents .= "					" . $lang_global['groups_view'] . ":\n";
    $contents .= "					<table style=\"margin-bottom:8px; width:250px;\">\n";
    $contents .= "						<col valign=\"top\" width=\"150px\" />\n";
    $contents .= "							<tr>\n";
    $contents .= "								<td>\n";
    foreach ( $groups_list as $group_id => $grtl )
    {
        $contents .= "<p><input name=\"groups_view[]\" type=\"checkbox\" value=\"" . $group_id . "\"";
        if ( in_array( $group_id, $groups_view ) ) $contents .= " checked=\"checked\"";
        $contents .= " />&nbsp;" . $grtl . "</p>\n";
    }
    $contents .= "								</td>\n";
    $contents .= "							</tr>\n";
    $contents .= "					</table>\n";
    $contents .= "				</div>\n";
    $contents .= "			</div>\n";
    $contents .= "</td>";
    $contents .= "</tr>";
    $contents .= "</tbody>";
    
    $contents .= "</table>";
    $contents .= "<br /><center><input name=\"submit1\" type=\"submit\" value=\"" . $lang_module['save'] . "\" /></center>\n";
    $contents .= "</form>\n";
    $contents .= "</div>";
    if ( empty( $alias ) )
    {
        $contents .= "<script type=\"text/javascript\">\n";
        $contents .= '$("#idtitle").change(function () {
                    get_alias("cat",0);
                });';
        $contents .= "</script>\n";
    }
}
include ( NV_ROOTDIR . "/includes/header.php" );
echo nv_admin_theme( $contents );
include ( NV_ROOTDIR . "/includes/footer.php" );

?>