<?php
if(!defined("IN_MYBB"))
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

if(function_exists("myplugins_info"))
    define(MODULE, "myplugins-announcement");
else
    define(MODULE, "config-announcement");

$page->add_breadcrumb_item($lang->announcement, "index.php?module=".MODULE);

/* Insert new announcement */
if($mybb->input['action'] == "do_add") {
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}

    if(!strlen(trim($mybb->input['announcement'])))
	{
		flash_message($lang->announcement_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['global'])))
	{
		flash_message($lang->announcement_global_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['color'])))
	{
		flash_message($lang->announcement_color_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['back_color'])))
	{
		flash_message($lang->announcement_back_color_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['border_color'])))
	{
		flash_message($lang->announcement_border_color_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['scroll'])))
	{
		flash_message($lang->announcement_scroll_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['slow_down'])))
	{
		flash_message($lang->announcement_slow_down_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['removable'])))
	{
		flash_message($lang->announcement_removable_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['enable'])))
	{
		flash_message($lang->announcement_enable_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	
	$insert = array(
		"Announcement" => $db->escape_string($mybb->input['announcement']),
		"Sort" => "0",
		"Global" => $mybb->input['global'],
		"Forum" => $db->escape_string(@serialize($mybb->input['forum'])),
		"tid" => $db->escape_string($mybb->input['thread']),
		"Groups" => $db->escape_string(@serialize($mybb->input['group'])),
		"Langs" => $db->escape_string(@serialize($mybb->input['langs'])),
		"Color" => $db->escape_string($mybb->input['color']),
		"BackColor" => $db->escape_string($mybb->input['back_color']),
		"Border" => $db->escape_string(@serialize($mybb->input['border_select'])),
		"BorderColor" => $db->escape_string($mybb->input['border_color']),
		"Scroll" => $mybb->input['scroll'],
		"slow_down" => $mybb->input['slow_down'],
		"Css" => $db->escape_string($mybb->input['css']),
		"removable" => $mybb->input['removable'],
		"Enabled" => $mybb->input['enable']
	);
	$db->insert_query("announcement", $insert);

	flash_message($lang->announcement_add_success, 'success');
	admin_redirect("index.php?module=".MODULE."&action=list");

/* Show mask to add a new announcement */
} elseif($mybb->input['action'] == "add") {
	$page->add_breadcrumb_item($lang->announcement_add, "index.php?module=".MODULE."&action=add");
	$page->output_header($lang->announcement_add);
	generate_tabs("add");
	
	$form = new Form("index.php?module=".MODULE."&amp;action=do_add", "post");
	$form_container = new FormContainer($lang->announcement_add);

	$add_announcement = $form->generate_text_area("announcement");
	$form_container->output_row($lang->announcement_simple." <em>*</em>", $lang->announcement_desc, $add_announcement);

	$id = "global";
	$add_global = $form->generate_yes_no_radio("global", 1, true, array("id" => $id."_yes", "class" => $id), array("id" => $id."_no", "class" => $id));
	$form_container->output_row($lang->announcement_global." <em>*</em>", '', $add_global);

	$add_forum = $form->generate_forum_select("forum[]", array(), array("multiple"=>true));
	$form_container->output_row($lang->announcement_forum, $lang->announcement_forum_desc, $add_forum, '', array(), array('id' => 'forum'));

	$add_thread = $form->generate_text_box("thread");
	$form_container->output_row($lang->announcement_thread, $lang->announcement_thread_desc, $add_thread, '', array(), array('id' => 'thread'));

	$add_group = $form->generate_group_select("group[]", array(), array("multiple"=>true));
	$form_container->output_row($lang->announcement_group, $lang->announcement_group_desc, $add_group);

	$languages = $lang->get_languages();
	$add_languages = $form->generate_select_box("langs[]", $languages, array(), array("multiple"=>true));
	$form_container->output_row($lang->announcement_languages, $lang->announcement_languages_desc, $add_languages);

	$add_color = $form->generate_text_box("color", "#FFFFFF");
	$form_container->output_row($lang->announcement_color." <em>*</em>", $lang->announcement_color_desc, $add_color);

	$add_back_color = $form->generate_text_box("back_color", "#666666");
	$form_container->output_row($lang->announcement_back_color." <em>*</em>", $lang->announcement_back_color_desc, $add_back_color);

	$option_list = array(
			"left" => $lang->left,
			"right" => $lang->right,
			"top" => $lang->top,
			"bottom" => $lang->bottom);
	$selected = array("left", "right", "top", "bottom");
	$add_border_select = $form->generate_select_box("border_select[]", $option_list, $selected, array("multiple"=>true, "id"=>"border"));
	$form_container->output_row($lang->announcement_border_select." <em>*</em>", $lang->announcement_border_select_desc, $add_border_select);

	$add_border_color = $form->generate_text_box("border_color", "#000000");
	$form_container->output_row($lang->announcement_border_color." <em>*</em>", $lang->announcement_border_color_desc, $add_border_color, '', array(), array('id' => 'border_color'));

	$option_list = array(
			"none" => $lang->scroll_none,
			"right" => $lang->scroll_right,
			"left" => $lang->scroll_left,
			"both" => $lang->scroll_both);
	$selected = "none";
	$add_scroll = $form->generate_select_box("scroll", $option_list, $selected, array("id"=>"scroll"));
	$form_container->output_row($lang->announcement_scroll." <em>*</em>", $lang->announcement_scroll_desc, $add_scroll);

	$add_slowdown = $form->generate_yes_no_radio("slow_down");
	$form_container->output_row($lang->announcement_slow_down." <em>*</em>", $lang->announcement_slow_down_desc, $add_slowdown, '', array(), array('id' => 'slow_down'));

	$add_css = $form->generate_text_area("css", "margin-bottom: 10px;\ntext-align: center;\npadding: 8px;");
	$form_container->output_row($lang->announcement_css, $lang->announcement_css_desc, $add_css);

	$add_removable = $form->generate_yes_no_radio("removable", 0);
	$form_container->output_row($lang->announcement_removable." <em>*</em>", $lang->announcement_removable_desc, $add_removable);

	$add_enable = $form->generate_yes_no_radio("enable");
	$form_container->output_row($lang->announcement_enable." <em>*</em>", '', $add_enable);

	$form_container->end();

	$buttons[] = $form->generate_submit_button($lang->announcement_submit);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();

	echo '<script type="text/javascript" src="./jscripts/peeker.js"></script>
	<script type="text/javascript">
		Event.observe(window, "load", function() {
			loadPeekers();
		});
		function loadPeekers()
		{
			new Peeker($$(".global"), $("forum"), /0/, true);
			new Peeker($$(".global"), $("thread"), /0/, true);
			new Peeker($("scroll"), $("slow_down"), /[^none]/, false);
			new Peeker($("border"), $("border_color"), /[^0]/, false);
		}
	</script>';

/* Delete an announcement */
} elseif($mybb->input['action']=="delete") {
	if(!strlen(trim($mybb->input['aid'])))
	{
		flash_message($lang->announcement_error, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$aid=intval($mybb->input['aid']);
	$db->delete_query("announcement", "ID='{$aid}'");
	flash_message($lang->announcement_delete_success, 'success');
	admin_redirect("index.php?module=".MODULE);

/* Enable an announcement */
} elseif($mybb->input['action']=="enable") {
	if(!strlen(trim($mybb->input['aid'])))
	{
		flash_message($lang->announcement_error, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$aid=intval($mybb->input['aid']);
	$query = $db->simple_select("announcement", "Enabled", "ID='{$aid}'");
	if($db->num_rows($query) != 1)
	{
		flash_message($lang->announcement_error, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$announcement = $db->fetch_array($query);
	if($announcement['Enabled']) {
	    $enabled=false;
	    $lang->announcement_enable_success=$lang->sprintf($lang->announcement_enable_success, $lang->announcement_deactivate);
	} else {
		$enabled=true;
	    $lang->announcement_enable_success=$lang->sprintf($lang->announcement_enable_success, $lang->announcement_activate);
	}
	$db->update_query("announcement", array("Enabled"=>$enabled), "ID='{$aid}'");
	flash_message($lang->announcement_enable_success, 'success');
	admin_redirect("index.php?module=".MODULE);

/* Save changes on an announcement */
} elseif($mybb->input['action']=="do_edit") {
	if(!strlen(trim($mybb->input['aid'])))
	{
		flash_message($lang->announcement_error, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$aid=intval($mybb->input['aid']);
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=".MODULE."&action=edit&aid=$aid");
	}

    if(!strlen(trim($mybb->input['announcement'])))
	{
		flash_message($lang->announcement_not, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	if(!strlen(trim($mybb->input['global'])))
	{
		flash_message($lang->announcement_global_not, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	if(!strlen(trim($mybb->input['color'])))
	{
		flash_message($lang->announcement_color_not, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	if(!strlen(trim($mybb->input['back_color'])))
	{
		flash_message($lang->announcement_back_color_not, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	if(!strlen(trim($mybb->input['border_color'])))
	{
		flash_message($lang->announcement_border_color_not, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	if(!strlen(trim($mybb->input['scroll'])))
	{
		flash_message($lang->announcement_scroll_not, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	if(!strlen(trim($mybb->input['slow_down'])))
	{
		flash_message($lang->announcement_slow_down_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['removable'])))
	{
		flash_message($lang->announcement_removable_not, 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['enable'])))
	{
		flash_message($lang->announcement_enable_not, 'error');
		admin_redirect("index.php?module=".MODULE);
	}

	$update = array(
		"Announcement" => $db->escape_string($mybb->input['announcement']),
		"Global" => $mybb->input['global'],
		"Forum" => $db->escape_string(@serialize($mybb->input['forum'])),
		"tid" => $db->escape_string($mybb->input['thread']),
		"Groups" => $db->escape_string(@serialize($mybb->input['group'])),
		"Langs" => $db->escape_string(@serialize($mybb->input['langs'])),
		"Color" => $db->escape_string($mybb->input['color']),
		"BackColor" => $db->escape_string($mybb->input['back_color']),
		"Border" => $db->escape_string(@serialize($mybb->input['border_select'])),
		"BorderColor" => $db->escape_string($mybb->input['border_color']),
		"Scroll" => $mybb->input['scroll'],
		"slow_down" => $mybb->input['slow_down'],
		"Css" => $db->escape_string($mybb->input['css']),
		"removable" => $mybb->input['removable'],
		"Enabled" => $mybb->input['enable']
	);
	$db->update_query("announcement", $update, "ID='{$aid}'");
	flash_message($lang->announcement_edit_success, 'success');
	admin_redirect("index.php?module=".MODULE);

/* Show mask for edit an announcement */
} elseif($mybb->input['action']=="edit") {
	if(!strlen(trim($mybb->input['aid'])))
	{
		flash_message($lang->announcement_error, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$aid=intval($mybb->input['aid']);
	$query = $db->simple_select("announcement", "*", "ID='{$aid}'");
	if($db->num_rows($query) != 1)
	{
		flash_message($lang->announcement_error, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$announcement = $db->fetch_array($query);

	$page->add_breadcrumb_item($lang->edit, "index.php?module=".MODULE."&amp;action=edit&amp;aid=$aid");
	$page->output_header($lang->announcement);
	generate_tabs("list");

	$form = new Form("index.php?module=".MODULE."&amp;action=do_edit", "post");
	$form_container = new FormContainer($lang->announcement);

	$add_announcement = $form->generate_text_area("announcement", $announcement['Announcement']);
	$form_container->output_row($lang->announcement_simple." <em>*</em>", $lang->announcement_desc, $add_announcement);

	$id = "global";
	$add_global = $form->generate_yes_no_radio("global", $announcement['Global'], true, array("id" => $id."_yes", "class" => $id), array("id" => $id."_no", "class" => $id));
	$form_container->output_row($lang->announcement_global." <em>*</em>", '', $add_global);

	$add_forum = $form->generate_forum_select("forum[]", @unserialize($announcement['Forum']), array("multiple"=>true));
	$form_container->output_row($lang->announcement_forum, $lang->announcement_forum_desc, $add_forum, '', array(), array('id' => 'forum'));

	$add_thread = $form->generate_text_box("thread", $announcement['tid']);
	$form_container->output_row($lang->announcement_thread, $lang->announcement_thread_desc, $add_thread, '', array(), array('id' => 'thread'));

	$add_group = $form->generate_group_select("group[]", @unserialize($announcement['Groups']), array("multiple"=>true));
	$form_container->output_row($lang->announcement_group, $lang->announcement_group_desc, $add_group);

	$languages = $lang->get_languages();
	$add_languages = $form->generate_select_box("langs[]", $languages, @unserialize($announcement['Langs']), array("multiple"=>true));
	$form_container->output_row($lang->announcement_languages, $lang->announcement_languages_desc, $add_languages);

	$add_color = $form->generate_text_box("color", $announcement['Color']);
	$form_container->output_row($lang->announcement_color." <em>*</em>", $lang->announcement_color_desc, $add_color);

	$add_back_color = $form->generate_text_box("back_color", $announcement['BackColor']);
	$form_container->output_row($lang->announcement_back_color." <em>*</em>", $lang->announcement_back_color_desc, $add_back_color);

	$option_list = array(
			"left" => $lang->left,
			"right" => $lang->right,
			"top" => $lang->top,
			"bottom" => $lang->bottom);
	$add_border_select = $form->generate_select_box("border_select[]", $option_list, @unserialize($announcement['Border']), array("multiple"=>true, "id"=>"border"));
	$form_container->output_row($lang->announcement_border_select." <em>*</em>", $lang->announcement_border_select_desc, $add_border_select);

	$add_border_color = $form->generate_text_box("border_color", $announcement['BorderColor']);
	$form_container->output_row($lang->announcement_border_color." <em>*</em>", $lang->announcement_border_color_desc, $add_border_color, '', array(), array('id' => 'border_color'));

	$option_list = array(
			"none" => $lang->scroll_none,
			"right" => $lang->scroll_right,
			"left" => $lang->scroll_left,
			"both" => $lang->scroll_both);
	$add_scroll = $form->generate_select_box("scroll", $option_list, $announcement['Scroll'], array("id"=>"scroll"));
	$form_container->output_row($lang->announcement_scroll." <em>*</em>", $lang->announcement_scroll_desc, $add_scroll);

	$add_slowdown = $form->generate_yes_no_radio("slow_down", $announcement['slow_down']);
	$form_container->output_row($lang->announcement_slow_down." <em>*</em>", $lang->announcement_slow_down_desc, $add_slowdown, '', array(), array('id' => 'slow_down'));

	$add_css = $form->generate_text_area("css", $announcement['Css']);
	$form_container->output_row($lang->announcement_css, $lang->announcement_css_desc, $add_css);

	$add_removable = $form->generate_yes_no_radio("removable", $announcement['removable']);
	$form_container->output_row($lang->announcement_removable." <em>*</em>", $lang->announcement_removable_desc, $add_removable);

	$add_enable = $form->generate_yes_no_radio("enable", $announcement['Enabled']);
	$form_container->output_row($lang->announcement_enable." <em>*</em>", '', $add_enable);

	echo $form->generate_hidden_field("aid", $aid);
	$form_container->end();

	$buttons[] = $form->generate_submit_button($lang->announcement_submit);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();

	echo '<script type="text/javascript" src="./jscripts/peeker.js"></script>
	<script type="text/javascript">
		Event.observe(window, "load", function() {
			loadPeekers();
		});
		function loadPeekers()
		{
			new Peeker($$(".global"), $("forum"), /0/, true);
			new Peeker($$(".global"), $("thread"), /0/, true);
			new Peeker($("scroll"), $("slow_down"), /[^none]/, false);
			new Peeker($("border"), $("border_color"), /[^0]/, false);
		}
	</script>';

/* Save new Order */
} elseif($mybb->input['action']=="order") {
	foreach($mybb->input['disporder'] as $ID => $Sort) {
		$db->update_query("announcement", array("Sort"=>$Sort), "ID='{$ID}'");
	}
	flash_message($lang->announcement_order_success, 'success');
	admin_redirect("index.php?module=".MODULE);
	
/* Show a list of announcements */
} else {
	$page->output_header($lang->announcement);
	generate_tabs("list");

	$form = new Form("index.php?module=".MODULE."&amp;action=order", "post");
	$form_container = new FormContainer("");

	$form_container->output_row_header($lang->announcement_simple, array("colspan" => 2));
	$form_container->output_row_header($lang->order, array('class' => 'align_center'));
	$form_container->output_row_header($lang->announcement_location, array('class' => 'align_center'));
	$form_container->output_row_header($lang->controls, array("colspan" => 2, 'class' => 'align_center'));

	$query = $db->simple_select("announcement", "ID, Sort, Announcement, Global, Enabled", "", array("order_by"=>"Sort"));
	if($db->num_rows($query) > 0)
	{
		while($announcement = $db->fetch_array($query))
		{
			if($announcement['Enabled']) {
				$icon = "<img src=\"styles/{$page->style}/images/icons/bullet_on.gif\" alt=\"(Active)\" title=\"Active Announcement\" /> ";
			} else {
				$icon = "<img src=\"styles/{$page->style}/images/icons/bullet_off.gif\" alt=\"(Inactive)\" title=\"Inactive Announcement\" /> ";
			}
			$form_container->output_cell("<a href=\"index.php?module=".MODULE."&amp;action=enable&amp;aid={$announcement['ID']}\">$icon</a>", array('width' => '2%'));
			$form_container->output_cell($announcement['Announcement']);
			$form_container->output_cell("<input type=\"text\" name=\"disporder[".$announcement['ID']."]\" value=\"".$announcement['Sort']."\" class=\"text_input align_center\" style=\"width: 80%; font-weight: bold;\" />", array('width' => '5%'));
			if($announcement['Global'])
				$form_container->output_cell($lang->yes, array('class' => 'align_center', 'width' => '5%'));
			else
				$form_container->output_cell($lang->no, array('class' => 'align_center', 'width' => '5%'));
			$form_container->output_cell("<a href=\"index.php?module=".MODULE."&amp;action=edit&amp;aid={$announcement['ID']}\">{$lang->edit}</a>", array('class' => 'align_center', 'width' => '10%'));
			$form_container->output_cell("<a href=\"index.php?module=".MODULE."&amp;action=delete&amp;aid={$announcement['ID']}\">{$lang->delete}</a>", array('class' => 'align_center', 'width' => '10%'));
			$form_container->construct_row();
		}
	} else {
		$form_container->output_cell($lang->announcement_no, array('class' => 'align_center', 'colspan' => 6));
		$form_container->construct_row();
	}
	$form_container->end();
	$buttons[] = $form->generate_submit_button($lang->announcement_submit);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();
}

$page->output_footer();

function generate_tabs($selected)
{
	global $lang, $page;

	$sub_tabs = array();
	$sub_tabs['list'] = array(
		'title' => $lang->announcement_list,
		'link' => "index.php?module=".MODULE."&amp;action=list",
		'description' => $lang->announcement_list_desc
	);
	$sub_tabs['add'] = array(
		'title' => $lang->announcement_add,
		'link' => "index.php?module=".MODULE."&amp;action=add",
		'description' => $lang->announcement_add_desc
	);

	$page->output_nav_tabs($sub_tabs, $selected);
}
?>