<?php 
	$file_count = sanitise_int($vars["entity"]->file_count);
	if(empty($file_count)){
		$file_count = 4;
	}

	echo "<div>";
	echo elgg_echo("file:num_files"); 
	echo elgg_view("input/dropdown", array("name" => "params[file_count]", "options" => range(1, 15), "value" => $file_count));
	echo "</div>";

	echo "<div>";
	echo elgg_echo("file_tools:settings:sort");
	echo elgg_view("input/dropdown", array(
		"name" => "params[sort_on]",
		"options_values" => array(
			"filename" => elgg_echo("file_tools:settings:sort:filename"),
			"time_created" => elgg_echo("file_tools:settings:sort:time_created")
		), 
		"value" => $vars["entity"]->sort_on
	));
	echo elgg_view("input/dropdown", array(
		"name" => "params[sort_on_direction]",
		"options_values" => array(
			"ASC" => elgg_echo("file_tools:settings:sort:asc"),
			"DESC" => elgg_echo("file_tools:settings:sort:desc")
		), 
		"value" => $vars["entity"]->sort_on
	));
	echo "</div>";	
?>
