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

echo elgg_view("file_tools/list/files", array(
	"files" => $files,
	"view_only" => true,
	"sort_by" => "title",
	"direction" => "ASC",
	"limit" => $number
));