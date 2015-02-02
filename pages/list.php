<?php

	$page_owner 		= elgg_get_page_owner_entity();
	$folder_guid 		= (int) get_input("folder_guid", 0);
	$draw_page 			= get_input("draw_page", true);
	$limit				= file_tools_get_list_length();
	$offset				= (int) get_input("offset", 0);
	
	if(!empty($page_owner) && (elgg_instanceof($page_owner, "user") || elgg_instanceof($page_owner, "group"))) {
		group_gatekeeper();

		$translation = array(
			"filename" => "oe.title",
			"time_created" => "e.time_created"
		);

		if (in_array($page_owner->file_tools_sort_on, $translation)) {
			$order = $translation[$page_owner->file_tools_sort_on];
		} else {
			$order = "oe.title";
		}

		if (in_array($page_owner->file_tools_sort_on_direction, array('ASC','DESC'))) {
			$direction = $page_owner->file_tools_sort_on_direction;
		} else {
			$direction = "ASC";
		}
				
		$wheres = array();
		$wheres[] = "NOT EXISTS (
					SELECT 1 FROM " . elgg_get_config("dbprefix") . "entity_relationships r
					WHERE r.guid_two = e.guid AND
					r.relationship = '" . FILE_TOOLS_RELATIONSHIP . "')";

		$files_options = array(
			"type" => "object",
			"subtype" => "file",
			"limit" => $limit,
			"offset" => $offset,
			"joins" => array("JOIN " . elgg_get_config("dbprefix") . "objects_entity oe ON oe.guid = e.guid"),
			"order_by" => $order . " " . $direction,
			"container_guid" => $page_owner->getGUID()
		);

		$folder = false;
		if($folder_guid !== false) {
			if($folder_guid && ($folder = get_entity($folder_guid)) && elgg_instanceof($folder, "object", FILE_TOOLS_SUBTYPE) && ($folder->getContainerGUID() == $page_owner->getGUID())){
				$files_options["relationship"] = FILE_TOOLS_RELATIONSHIP;
				$files_options["relationship_guid"] = $folder_guid;
				$files_options["inverse_relationship"] = false;
			} else {
				$folder = false; // just to be save
				$files_options["wheres"] = $wheres;
			}
		}
		
		// get the files
		$files = elgg_get_entities_from_relationship($files_options);
		
		// get count
		$files_options["count"] = true;
		$files_count = elgg_get_entities_from_relationship($files_options);
		
		// do we need a more button
		$show_more = false;
		if ($limit) {
			$show_more = $files_count > ($offset + $limit);
		}
		
		if(!$draw_page) {
			echo elgg_view("file_tools/list/files", array(
				"folder" => $folder,
				"files" => $files,
				"show_more" => $show_more,
				"limit" => $limit,
				"offset" => $offset
			));
		} else {
			// build breadcrumb
			elgg_push_breadcrumb(elgg_echo("file"), "file/all");
			elgg_push_breadcrumb($page_owner->name);
			
			// register title button to add new folder
			if (elgg_is_logged_in()) {
				$owner = elgg_get_page_owner_entity();
				if ($owner && $owner->canWriteToContainer()) {
					$guid = $owner->getGUID();

					elgg_register_menu_item('title', array(
						'name' => 'file_tools:upload:file',
						'text' => elgg_echo("file:upload"),
						'id' => 'file_tools_list_upload_file_toggle',
						'link_class' => 'elgg-button elgg-button-action'
					));

					elgg_register_menu_item('title', array(
						'name' => 'file_tools:new:title',
						'text' => elgg_echo("file_tools:new:title"),
						'id' => 'file_tools_list_new_folder_toggle',
						'link_class' => 'elgg-button elgg-button-action'
					));
				}
			}

			// get data for tree
			$folders = file_tools_get_folders($page_owner->getGUID());

			// build page elements
			$title_text = elgg_echo("file:user", array($page_owner->name));
			
			$body = "<div id='file_tools_list_files_container' class='elgg-content'>" . elgg_view("graphics/ajax_loader", array("hidden" => false)) . "</div>";
			
			// make sidebar
			$sidebar = elgg_view("file_tools/list/tree", array("folder" => $folder, "folders" => $folders));
			$sidebar .= elgg_view("file_tools/sidebar/sort_options");
			$sidebar .= elgg_view("page/elements/tagcloud_block", array("subtypes" => "file", "owner_guid" => $page_owner->getGUID()));
			
			// build page params
			$params = array(
				"title" => $title_text,
				"content" => $body,
				"sidebar" => $sidebar
			);
			
			if(elgg_instanceof($page_owner, "user")){
				if($page_owner->getGUID() == elgg_get_logged_in_user_guid()){
					$params["filter_context"] = "mine";
				} else {
					$params["filter_context"] = $page_owner->username;
				}
			} else {
				$params["filter"] = false;
			}
			
			echo elgg_view_page($title_text, elgg_view_layout("content", $params));
		}
	} else {
		forward();
	}