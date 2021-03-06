<?php

	define("FILE_TOOLS_SUBTYPE", 		"folder");
	define("FILE_TOOLS_RELATIONSHIP", 	"folder_of");
	define("FILE_TOOLS_BASEURL", 		elgg_get_site_url() . "file_tools/");

	require_once(dirname(__FILE__) . "/lib/functions.php");
	require_once(dirname(__FILE__) . "/lib/events.php");
	require_once(dirname(__FILE__) . "/lib/hooks.php");
	require_once(dirname(__FILE__) . "/lib/page_handlers.php");

	function file_tools_init() {
		// extend CSS
		elgg_extend_view("css/elgg", "file_tools/css/site");

		if (file_tools_use_folder_structure()) {
			elgg_extend_view("groups/edit", "file_tools/group_settings");
		}

		// extend js
		elgg_extend_view("js/elgg", "file_tools/js/site");

		// register JS libraries
		$vendors = elgg_get_site_url() . "mod/file_tools/vendors/";

		elgg_register_js("jquery.tree", $vendors . "jstree/jquery.tree.min.js");
		elgg_register_css("jquery.tree", $vendors . "jstree/themes/default/style.css");

		elgg_register_js("jquery.hashchange", $vendors . "hashchange/jquery.hashchange.js");

		// register page handler for nice URL's
		elgg_register_page_handler("file_tools", "file_tools_page_handler");

		// make our own URLs for folders
		elgg_register_entity_url_handler("object", "file", "file_tools_url_handler");
		elgg_register_entity_url_handler("object", FILE_TOOLS_SUBTYPE, "file_tools_folder_url_handler");

		// make our own URLs for folder icons
		elgg_register_plugin_hook_handler("entity:icon:url", "object", "file_tools_folder_icon_hook");

		// register group option to allow management of file tree structure
		add_group_tool_option("file_tools_structure_management", elgg_echo("file_tools:group_tool_option:structure_management"));

		// register group option to allow group members to overwrite all their files
		add_group_tool_option("file_tools_file_management", elgg_echo("file_tools:group_tool_option:file_management"), false);

		// register widgets
		// add folder widget
		// need to keep file_tree for the widget name to be compatible with previous filetree plugin users
		elgg_register_widget_type ("file_tree", elgg_echo("widgets:file_tree:title"), elgg_echo("widgets:file_tree:description"), "dashboard,profile,groups", true);

		// group files
		elgg_register_widget_type("group_files", elgg_echo("file:group"), elgg_echo("widgets:group_files:description"), "groups");

		// index files
		elgg_register_widget_type("index_file", elgg_echo("file"), elgg_echo("widgets:index_file:description"), "index", true);

		// register events
		elgg_register_event_handler("create", "object", "file_tools_object_handler");
		elgg_register_event_handler("update", "object", "file_tools_object_handler");
		elgg_register_event_handler("delete", "object", "file_tools_object_handler_delete");

		elgg_register_event_handler("upgrade", "system", "file_tools_upgrade_handler");


		// extend file page menu
		if (elgg_is_active_plugin('odt_editor')) {
			elgg_register_plugin_hook_handler("register", "menu:title", "odt_editor_file_menu_title_hook");
			elgg_extend_view("js/elgg", "js/file_tools");
		}

		// register hooks
		elgg_register_plugin_hook_handler("register", "menu:entity", "file_tools_entity_menu_hook");

		elgg_register_plugin_hook_handler("permissions_check", "object", "file_tools_can_edit_metadata_hook");

		elgg_register_plugin_hook_handler("route", "file", "file_tools_file_route_hook");
		elgg_register_plugin_hook_handler("widget_url", "widget_manager", "file_tools_widget_url_hook");

		elgg_register_plugin_hook_handler("register", "menu:file_tools_folder_breadcrumb", "file_tools_folder_breadcrumb_hook");
		elgg_register_plugin_hook_handler("register", "menu:file_tools_folder_sidebar_tree", "file_tools_folder_sidebar_tree_hook");

		// register actions
		elgg_register_action("file_tools/folder/edit", dirname(__FILE__) . "/actions/folder/edit.php");
		elgg_register_action("file_tools/folder/delete", dirname(__FILE__) . "/actions/folder/delete.php");
		elgg_register_action("file_tools/folder/reorder", dirname(__FILE__) . "/actions/folder/reorder.php");
		elgg_register_action("file_tools/folder/delete", dirname(__FILE__) . "/actions/folder/delete.php");

		elgg_register_action("file_tools/file/hide", dirname(__FILE__) . "/actions/file/hide.php");
		elgg_register_action("file_tools/file/upload", dirname(__FILE__) . "/actions/file/upload.php");
		elgg_register_action("file_tools/file/delete", dirname(__FILE__) . "/actions/file/delete.php");

		elgg_register_action("file/move", dirname(__FILE__) . "/actions/file/move.php");
		elgg_register_action("file/bulk_delete", dirname(__FILE__) . "/actions/file/bulk_delete.php");

		elgg_register_action("file_tools/groups/save_sort",  dirname(__FILE__) . "/actions/groups/save_sort.php");
	}

	function file_tools_pagesetup(){
		$page_owner = elgg_get_page_owner_entity();

		if(elgg_instanceof($page_owner, "group")){
			// check if the group hase files enabled
			if($page_owner->files_enable == "no"){
				// no, so remove the widgets
				elgg_unregister_widget_type("file_tree");
				elgg_unregister_widget_type("group_files");
			}
		}
	}

	function file_tools_folder_url_handler($entity) {
		$container = $entity->getContainerEntity();

		if(elgg_instanceof($container, "group")){
			$result = "file/group/" . $container->getGUID() . "/all#" . $entity->getGUID();
		} else {
			$result = "file/owner/" . $container->username . "#" . $entity->getGUID();
		}

		return $result;
	}

	function file_tools_url_handler($entity) {
		if (file_tools_is_odt($entity)) {
			return "file/view/" . $entity->getGUID() . "/" . urlencode($entity->title);
		}

		if (elgg_in_context("file_tools")) {
			return "file/download/" . $entity->getGUID() . "/" . urlencode($entity->title);
		} else {
			return "file/view/" . $entity->getGUID() . "/" . urlencode($entity->title);
		}
	}

	// register default Elgg events
	elgg_register_event_handler("init", "system", "file_tools_init");
	elgg_register_event_handler("pagesetup", "system", "file_tools_pagesetup");
