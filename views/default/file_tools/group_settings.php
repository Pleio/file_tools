<?php

	$group = elgg_extract("entity", $vars);
	if(!empty($group) && elgg_instanceof($group, "group")){
		// build form
		$sort_value = 'o.title';

		if($group->file_tools_sort_on){
			$sort = $group->file_tools_sort_on;
		} else {
			$sort = "filename";
		}
		
		if($group->file_tools_sort_on_direction){
			$direction = $group->file_tools_sort_on_direction;
		} else {
			$direction = "ASC";
		}

		$form_body = "<div>";
		$form_body .= elgg_echo("file_tools:settings:sort");
		$form_body .= elgg_view('input/dropdown', array('name' => 'sort',
													'value' =>  $sort,
													'options_values' => array(
														'filename' => elgg_echo('file_tools:settings:sort:filename'),
														'time_created' => elgg_echo('file_tools:settings:sort:time_created')
													)));		
		$form_body .= elgg_view('input/dropdown', array('name' => 'sort_direction',
													'value' =>  $direction,
													'options_values' => array(
														'ASC' 	=> elgg_echo('file_tools:settings:sort:asc'), 
														'DESC'	=> elgg_echo('file_tools:settings:sort:desc')
													))); 		
		$form_body .= "</div>";
		$form_body .= "<div class='elgg-foot'>";
		$form_body .= elgg_view("input/hidden", array("name" => "guid", "value" => $group->getGUID()));
		$form_body .= elgg_view("input/submit", array("value" => elgg_echo("save")));
		$form_body .= "</div>";
		
		$title = elgg_echo("file_tools:settings:sort:default");
		$body = elgg_view("input/form", array("action" => $vars["url"] . "action/file_tools/groups/save_sort", "body" => $form_body));
		
		echo elgg_view_module("info", $title, $body);
	}