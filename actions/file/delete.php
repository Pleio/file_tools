<?php
/**
* Elgg file delete
* 
* @package ElggFile
*/

$guid = (int) get_input('guid');

$file = new FilePluginFile($guid);
if (!$file->guid) {
	register_error(elgg_echo("file:deletefailed"));
}

if (!$file->canEdit()) {
	register_error(elgg_echo("file:deletefailed"));
}

$container = $file->getContainerEntity();

$folders = $file->getEntitiesFromRelationship('folder_of', true);
if (count($folders) > 0) {
	$folder_guid = $folders[0]['guid'];
}

if (!$file->delete()) {
	register_error(elgg_echo("file:deletefailed"));
} else {
	system_message(elgg_echo("file:deleted"));
}

if (elgg_instanceof($container, 'group')) {
	if ($folder_guid) {
		forward("file/group/$container->guid/all#" . $folder_guid);
	} else {
		forward("file/group/$container->guid/all");
	}
} else {
	forward("file/owner/$container->username");
}
