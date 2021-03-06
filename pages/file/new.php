<?php 

	gatekeeper();

	$page_owner = elgg_get_page_owner_entity();
	
	if(!empty($page_owner))	{
		// build breadcrumb
		elgg_push_breadcrumb(elgg_echo("file"), "file/all");
		if(elgg_instanceof($page_owner, "group", null, "ElggGroup")){
			elgg_push_breadcrumb($page_owner->name, "file/group/" . $page_owner->getGUID() . "/all");
		} else {
			elgg_push_breadcrumb($page_owner->name, "file/owner/" . $page_owner->username);
		}
		elgg_push_breadcrumb(elgg_echo("file:upload"));
		
		// get data
		$upload_type = get_input("upload_type", "single");
		
		// build page elements
		$title_text = elgg_echo("file:upload");
		
		// body
		$form_vars = array(
			"enctype" => "multipart/form-data",
			"class" => "hidden"
		);

		$body_vars = array();
		$multi_vars = $form_vars;
		$multi_vars["id"] = "file-tools-multi-form";
		$multi_vars["action"] = "action/file_tools/file/upload";
		$zip_vars = $form_vars;
		$zip_vars["id"] = "file-tools-zip-form";
		$single_vars = $form_vars;
		$single_vars["id"] = "file-tools-single-form";
		
		switch ($upload_type) {
			case "multi":
				unset($multi_vars["class"]);
				
				break;
			case "zip":
				unset($zip_vars["class"]);
				
				break;
			default:
				elgg_load_library("elgg:file");
				
				$body_vars = file_prepare_form_vars();				

				unset($single_vars["class"]);
				break;
		}

		$parent = get_input("folder_guid", 0);
		if(!empty($parent) && ($parent_entity = get_entity($parent))) {
			$body_vars['access_id'] = $parent_entity->access_id;
		} else {
			$body_vars['access_id'] = ACCESS_DEFAULT;
		}		
		
		// build different forms
		$body = "<div id='file-tools-upload-wrapper'>";
		$body .= elgg_view_form("file_tools/file/upload", $single_vars, $body_vars);
		$body .= elgg_view_form("file_tools/upload/multi", $multi_vars, $body_vars);
		$body .= "</div>";
		
		$tabs = elgg_view("file_tools/upload_tabs", array("upload_type" => $upload_type));
				
		// draw page
		if(elgg_is_xhr()){
			$page_data = elgg_view("page/layouts/content/filter", array(
				"title" => $title_text,
				"filter_override" => $tabs
			));
			$page_data .= $body;
						
			echo "<div style='width: 550px; height:500px;'>";
			echo elgg_view_title($title_text);
			echo $page_data;
			echo "</div>";
		} else {	
			$page_data = elgg_view_layout("content", array(
				"title" => $title_text,
				"content" => $body,
				"filter" => $tabs
			));

			echo elgg_view_page($title_text, $page_data);
		}
	} else {
		forward();
	}