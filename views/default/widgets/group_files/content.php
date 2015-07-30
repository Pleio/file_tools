<?php

$widget = $vars["entity"];
$group = $widget->getOwnerEntity();

$number = sanitise_int($widget->file_count, false);
if(empty($number)){
	$number = 10;
}

$translation = array(
	"filename" => "o.title",
	"time_created" => "e.time_created"
);

$directions = array("ASC","DESC");

if (array_key_exists($widget->sort_on, $translation) && in_array($widget->sort_on_direction, $directions)) {
	$order_by = $translation[$widget->sort_on];
	$direction = $widget->sort_on_direction;
} elseif (array_key_exists($group->file_tools_sort_on, $translation) && in_array($group->file_tools_sort_on_direction, $directions)) {
	$order_by = $translation[$group->file_tools_sort_on];
	$direction = $group->file_tools_sort_on_direction;
} else {
	$order_by = $translation["filename"];
	$direction = "ASC";
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
	'order_by' => $order_by . " " . $direction,
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
	'text' => elgg_echo("file_tools:upload:file"),
	'id' => 'file_tools_list_upload_file_toggle',
	'class' => 'file-tools-widget-button elgg-button elgg-button-action'
));

if (elgg_is_active_plugin('odt_editor')) {
	echo elgg_view('output/url', array(
		'name' => 'file_tools:create:file',
		'text' => elgg_echo("odt_editor:newdocument"),
		'id' => 'file_tools_list_new_document_toggle',
		'class' => 'file-tools-widget-button elgg-button elgg-button-action'
	));
}


echo elgg_view("file_tools/list/files", array(
	"files" => $files,
	"view_only" => true,
	"limit" => $number
));