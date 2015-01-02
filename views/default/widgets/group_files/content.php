<?php

$widget = $vars["entity"];
$group = $widget->getOwnerEntity();

$number = sanitise_int($widget->file_count, false);
if(empty($number)){
	$number = 10;
}

$wheres = array();
$wheres[] = "NOT EXISTS (
			SELECT 1 FROM " . elgg_get_config("dbprefix") . "entity_relationships r
			WHERE r.guid_two = e.guid AND
			r.relationship = '" . FILE_TOOLS_RELATIONSHIP . "')";

$options = array(
	'type' => 'object',
	'subtype' => 'file',
	'container_guid' => $group->guid,
	'joins' => "INNER JOIN {$CONFIG->dbprefix}objects_entity o ON (o.guid = e.guid)",
	'order_by' => 'o.title',
	'wheres' => $wheres,
	'limit' => $number
);

$files = elgg_get_entities_from_metadata($options);

echo elgg_view('output/url', array(
	'name' => 'file_tools:new:title',
	'text' => elgg_echo("file_tools:new:title"),
	'id' => 'file_tools_list_new_folder_toggle',
	'class' => 'file-tools-widget-button elgg-button elgg-button-action'
));

echo elgg_view('output/url', array(
	'name' => 'file_tools:upload:file',
	'text' => elgg_echo("file:upload"),
	'id' => 'file_tools_list_upload_file_toggle',
	'class' => 'file-tools-widget-button elgg-button elgg-button-action'
));

echo elgg_view("file_tools/list/files", array(
	"files" => $files,
	"view_only" => true,
	"sort_by" => "title",
	"direction" => "ASC",
	"limit" => $number
));