<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("global_start", "announcement_global");
$plugins->add_hook("index_start", "announcement_index");
$plugins->add_hook("forumdisplay_start", "announcement_forumdisplay");
$plugins->add_hook("admin_config_menu", "announcement_admin_config_menu");
$plugins->add_hook("admin_config_action_handler", "announcement_admin_config_action_handler");
$plugins->add_hook("admin_config_permissions", "announcement_admin_config_permissions");

function announcement_info()
{
	return array(
		"name"			=> "Announcement",
		"description"	=> "Manage your own Announcements",
		"website"		=> "http://mybbdemo.tk/forum-9.html",
		"author"		=> "Jones",
		"authorsite"	=> "http://mybbdemo.tk/",
		"version"		=> "2.3",
		"guid" 			=> "26ead0fc6a84d60992d8f5f9835b7148",
		"compatibility" => "16*"
	);
}

function announcement_install()
{
	global $db;
	$db->query("CREATE TABLE `".TABLE_PREFIX."announcement` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `Sort` int(11) NOT NULL,`Announcement` text NOT NULL, `Global` tinyint(1) NOT NULL, `Forum` text NOT NULL, `Groups` text NOT NULL, `Langs` text NOT NULL, `Color` varchar(20) NOT NULL, `BackColor` varchar(20) NOT NULL, `Border` text NOT NULL, `BorderColor` varchar(20) NOT NULL, `Scroll` varchar(50) NOT NULL, `slow_down` tinyint(1) NOT NULL, `Css` text NOT NULL, `Enabled` tinyint(1) NOT NULL, PRIMARY KEY (`ID`) ) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1");
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
	find_replace_templatesets("header", "#".preg_quote('{$pm_notice}')."#i", '{$announcement}{$pm_notice}');
}

function announcement_deactivate()
{
	require MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("index", "#".preg_quote('{$announcement}')."#i", "", 0);
	find_replace_templatesets("forumdisplay", "#".preg_quote('{$fdannouncement}')."#i", "", 0);
	find_replace_templatesets("header", "#".preg_quote('{$announcement}')."#i", "", 0);
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
}

function announcement_index()
{
	global $announcement, $mybb, $db;
	$announcement = announcement_create(false);
}

function announcement_forumdisplay()
{
	global $fdannouncement, $mybb, $db;
	$fid = intval($mybb->input['fid']);
	$fdannouncement = announcement_create(false, $fid);
}

function announcement_create($global, $forum=-1)
{
	global $db, $mybb;
	$return="";
	$query=$db->simple_select("announcement", "Announcement, Forum, Groups, Langs, Color, BackColor, Border, BorderColor, Scroll, slow_down, Css", "Global='$global' AND Enabled='1'", array("order_by"=>"Sort"));
	while($announcements=$db->fetch_array($query)) {
		//Prüfen ob Mitglied einer Gruppe zum Zeigen
    	if(!announcement_member(@unserialize($announcements['Groups'])))
			continue;

		//Prüfen ob in einem Forum zum Zeigen (Globale werden immer übersprungen)
		if(!announcement_forum(@unserialize($announcements['Forum']), $forum)&&!$global)
		    continue;

		//Prüfen ob Ankündigung in Sprache
		if(!announcement_language(@unserialize($announcements['Langs'])))
		    continue;
		
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
		$return .= "<div style=\"$border $background $color $additional\">$text</div>";
	}
	return $return;
}

function announcement_forum($forums, $forum) {
	if($forum==-1 && !is_array($forums))
	    return true;
    if(!@in_Array($forum, $forums))
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