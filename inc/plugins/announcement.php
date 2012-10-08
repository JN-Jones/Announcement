<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
if(!$pluginlist)
    $pluginlist = $cache->read("plugins");

$plugins->add_hook("global_start", "announcement_global");
$plugins->add_hook("index_start", "announcement_index");
$plugins->add_hook("forumdisplay_start", "announcement_forumdisplay");
$plugins->add_hook("showthread_start", "announcement_showthread");

if(is_array($pluginlist['active']) && in_array("myplugins", $pluginlist['active'])) {
	$plugins->add_hook("myplugins_actions", "announcement_myplugins_actions");
	$plugins->add_hook("myplugins_permission", "announcement_admin_config_permissions");
} else {
	$plugins->add_hook("admin_config_menu", "announcement_admin_config_menu");
	$plugins->add_hook("admin_config_action_handler", "announcement_admin_config_action_handler");
	$plugins->add_hook("admin_config_permissions", "announcement_admin_config_permissions");
}

function announcement_info()
{
	return array(
		"name"			=> "Announcement",
		"description"	=> "Manage your own Announcements",
		"website"		=> "http://jonesboard.tk/",
		"author"		=> "Jones",
		"authorsite"	=> "http://jonesboard.tk/",
		"version"		=> "2.4",
		"guid" 			=> "26ead0fc6a84d60992d8f5f9835b7148",
		"compatibility" => "16*"
	);
}

function announcement_install()
{
	global $db;
	$db->query("CREATE TABLE `".TABLE_PREFIX."announcement` (
		`ID` int(11) NOT NULL AUTO_INCREMENT,
		`Sort` int(11) NOT NULL,
		`Announcement` text NOT NULL,
		`Global` tinyint(1) NOT NULL,
		`Forum` text NOT NULL,
		`tid` text NOT NULL default '',
		`Groups` text NOT NULL,
		`Langs` text NOT NULL,
		`Color` varchar(20) NOT NULL,
		`BackColor` varchar(20) NOT NULL,
		`Border` text NOT NULL,
		`BorderColor` varchar(20) NOT NULL,
		`Scroll` varchar(50) NOT NULL,
		`slow_down` tinyint(1) NOT NULL,
		`Css` text NOT NULL,
		`removable` tinyint(1) NOT NULL,
		`removedfrom` text NOT NULL default '',
		`Enabled` tinyint(1) NOT NULL,
		PRIMARY KEY (`ID`) ) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1");
}

function announcement_is_installed()
{
	global $db;
	return $db->table_exists("announcement");
}

function announcement_uninstall()
{
	global $db;
	$db->drop_table("announcement");
}

function announcement_activate()
{
	require MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("index", "#".preg_quote('{$header}')."#i", '{$header}{$announcement}');
	find_replace_templatesets("forumdisplay", "#".preg_quote('{$header}')."#i", '{$header}{$fdannouncement}');
	find_replace_templatesets("showthread", "#".preg_quote('{$header}')."#i", '{$header}{$announcement}');
	find_replace_templatesets("header", "#".preg_quote('{$pm_notice}')."#i", '{$announcement}{$pm_notice}');
	find_replace_templatesets('headerinclude', "#".preg_quote('{$newpmmsg}')."#i", '<script type="text/javascript">
function dismissANN(id)
{
	if(!$("Ann_"+id))
	{
		return false;
	}
	
	if(use_xmlhttprequest != 1)
	{
		return true;
	}

	new Ajax.Request("index.php?action=ann_dismiss", {method: "post", postBody: "ajax=1&my_post_key="+my_post_key+"&id="+id});
	Element.remove("Ann_"+id);
	return false;
}
</script>'."\n".'{$newpmmsg}');
}

function announcement_deactivate()
{
	require MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("index", "#".preg_quote('{$announcement}')."#i", "", 0);
	find_replace_templatesets("forumdisplay", "#".preg_quote('{$fdannouncement}')."#i", "", 0);
	find_replace_templatesets("showthread", "#".preg_quote('{$announcement}')."#i", "", 0);
	find_replace_templatesets("header", "#".preg_quote('{$announcement}')."#i", "", 0);
	find_replace_templatesets('headerinclude', "#".preg_quote('<script type="text/javascript">
function dismissANN(id)
{
	if(!$("Ann_"+id))
	{
		return false;
	}

	if(use_xmlhttprequest != 1)
	{
		return true;
	}

	new Ajax.Request("index.php?action=ann_dismiss", {method: "post", postBody: "ajax=1&my_post_key="+my_post_key+"&id="+id});
	Element.remove("Ann_"+id);
	return false;
}
</script>'."\n")."#i", '');
}

function announcement_myplugins_actions($actions)
{
	global $page, $lang, $info;
	$lang->load("config_announcement");
	
	$actions['announcement'] = array(
		"active" => "announcement",
		"file" => "../config/announcement.php"
	);

	$sub_menu = array();
	$sub_menu['10'] = array("id" => "announcement", "title" => $lang->announcement, "link" => "index.php?module=myplugins-announcement");

	$sidebar = new SidebarItem($lang->announcement);
	$sidebar->add_menu_items($sub_menu, $actions[$info]['active']);

	$page->sidebar .= $sidebar->get_markup();

	return $actions;
}

function announcement_admin_config_menu($sub_menu)
{
	global $lang;

	$lang->load("config_announcement");

	$sub_menu[] = array("id" => "announcement", "title" => $lang->announcement, "link" => "index.php?module=config-announcement");

	return $sub_menu;
}

function announcement_admin_config_action_handler($actions)
{
	$actions['announcement'] = array(
		"active" => "announcement",
		"file" => "announcement.php"
	);

	return $actions;
}

function announcement_admin_config_permissions($admin_permissions)
{
	global $lang;

	$lang->load("config_announcement");

	$admin_permissions['announcement'] = $lang->announcement_permission;

	return $admin_permissions;
}

function announcement_global()
{
	global $announcement, $mybb, $db;
	$announcement = announcement_create(true);
	
	if($mybb->input['action'] == "ann_dismiss") {
		if(!$mybb->input['id'] || $mybb->user['uid'] == 0)
		    exit;
		
		$query = $db->simple_select("announcement", "removable, removedfrom", "ID=".(int)$mybb->input['id']);
		$ann = $db->fetch_array($query);
		if(!$ann['removable'])
		    exit;
		$removedUser = @unserialize($ann['removedfrom']);
		
		if($removedUser && in_array($mybb->user['uid'], $removedUser))
			exit;

		verify_post_check($mybb->input['my_post_key']);
		
		$removedUser[] = $mybb->user['uid'];
		$updated_user = array(
			"removedfrom" => $db->escape_string(serialize($removedUser))
		);
		$db->update_query("announcement", $updated_user, "ID=".(int)$mybb->input['id']);

		if($mybb->input['ajax']) {
			echo 1;
			exit;
		} else {
			header("Location: index.php");
			exit;
		}
	}
}

function announcement_index()
{
	global $announcement, $mybb, $db;
	$announcement = announcement_create(false);
}

function announcement_forumdisplay()
{
	global $fdannouncement, $mybb, $db;
	$fid = (int)$mybb->input['fid'];
	$fdannouncement = announcement_create(false, $fid);
}

function announcement_showthread()
{
	global $announcement, $mybb, $db;
	$tid = (int)$mybb->input['tid'];
	$announcement = announcement_create(false, -1, $tid);
}

function announcement_create($global, $forum=-1, $tid=-1)
{
	global $db, $mybb, $lang, $theme;
	
	//global_start hat imgdir noch nicht definiert...
	//Hoffentlich existiert das Standardverzeichnis ;)
	if($global)
	    $theme['imgdir'] = "images";

	$return="";
	$query=$db->simple_select("announcement", "*", "Global='$global' AND Enabled='1'", array("order_by"=>"Sort"));
	while($announcements=$db->fetch_array($query)) {
		//Prüfen ob Mitglied einer Gruppe zum Zeigen
    	if(!announcement_member(@unserialize($announcements['Groups'])))
			continue;

		//Prüfen ob Ankündigung in Sprache
		if(!announcement_language(@unserialize($announcements['Langs'])))
		    continue;

		$in_forum = announcement_forum(@unserialize($announcements['Forum']), $forum);
		$in_thread = announcement_thread($announcements['tid'], $tid);

		//Prüfen ob in einem Forum oder Thema zum Zeigen (Globale werden immer übersprungen)
		//Just do this * when it's not global
		if(!$global) {
			if($forum == -1 && $tid == -1) {
				//We're on the index so test whether it's just showed here
				$forums = @unserialize($announcements['Forum']);
				if(($announcements['tid'] != 0 && $announcements['tid'] != "") || is_array($forums))
				    continue;
			} else {
   				if(!$in_forum && !$in_thread)
				    continue;
			}
		}	    

		$removedUser = @unserialize($announcements['removedfrom']);
		if($announcements['removable'] && $mybb->user['uid'] != 0) {
			if($removedUser && in_array($mybb->user['uid'], $removedUser))
				continue;
			else
				$remove = "<div class=\"float_right\"><a href=\"index.php?action=ann_dismiss&amp;my_post_key={$mybb->post_code}&amp;id={$announcements['ID']}\" title=\"{$lang->dismiss_notice}\" onclick=\"return dismissANN('{$announcements['ID']}')\"><img src=\"{$theme['imgdir']}/dismiss_notice.gif\" alt=\"{$lang->dismiss_notice}\" title=\"[x]\" /></a></div>";
		} else
			$remove = "";

		$text = $announcements['Announcement'];
		
		$scrollamount = "scrollamount=\"4\"";
		$scroll_additional = "";
    	if($announcements['slow_down']) {
			$scroll_additional = "onmouseover=\"this.setAttribute('scrollamount', '1', false)\" onmouseout=\"this.setAttribute('scrollamount', '4', false)\"";
		}
    	if($announcements['Scroll']=="right")
		    $text = "<marquee direction=\"right\" $scrollamount $scroll_additional>$text</marquee>";
		elseif($announcements['Scroll']=="left")
		    $text = "<marquee direction=\"left\" $scrollamount $scroll_additional>$text</marquee>";
		elseif($announcements['Scroll']=="both")
		    $text = "<marquee behavior=\"alternate\" $scrollamount $scroll_additional>$text</marquee>";

		$borderr = @unserialize($announcements['Border']);
		$border = "";
		if(is_array($borderr)) {
	   		if(in_Array("left", $borderr))
			    $border .= "border-left: 2px solid ".$announcements['BorderColor'].";";
			if(in_Array("right", $borderr))
			    $border .= "border-right: 2px solid ".$announcements['BorderColor'].";";
			if(in_Array("top", $borderr))
			    $border .= "border-top: 2px solid ".$announcements['BorderColor'].";";
			if(in_Array("bottom", $borderr))
			    $border .= "border-bottom: 2px solid ".$announcements['BorderColor'].";";
		}
		$background = "background: ".$announcements['BackColor'].";";
		$color = "color: ".$announcements['Color'].";";
		$additional = $announcements['Css'];
		$return .= "<div id=\"Ann_{$announcements['ID']}\" style=\"$border $background $color $additional\">{$text}{$remove}</div>";
	}
	return $return;
}

function announcement_forum($forums, $forum) {
	if($forum==-1 && !is_array($forums))
  		return false;
    if(!@in_Array($forum, $forums))
	    return false;
	return true;
}

function announcement_thread($threads, $tid) {
	if($threads != 0 && $threads != "") {
		if(strpos($threads, ",") == -1)
		    $threads = array($threads);
		else
			$threads = explode(",", trim($threads));
	}
    if($tid == -1 && !is_array($threads))
		return false;
    if(!is_array($threads) || !@in_Array($tid, $threads))
	    return false;
	return true;
}

function announcement_language($languages) {
	global $lang;
	if(!is_array($languages))
	    return true;
	
	$language = $lang->language;
	
	if(in_Array($language, $languages))
	    return true;
	return false;
}

function announcement_member($groups) {
	global $mybb;
	if(!is_array($groups))
	    return true;
	
	$user = $mybb->user;

    $memberships = explode(',', $user['additionalgroups']);
    $memberships[] = $user['usergroup'];

	if(sizeof(array_intersect($groups, $memberships))>0)
	    return true;
	return false;
}
?>