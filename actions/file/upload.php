<?php
/**
 * Elgg file uploader/edit action
 *
 * @package ElggFile
 */

// Get variables
$guid = (int) get_input('file_guid');
$title = htmlspecialchars(get_input('title', '', false), ENT_QUOTES, 'UTF-8');
$desc = get_input("description");
$access_id = (int) get_input("access_id");
$container_guid = (int) get_input('container_guid', 0);
$folder_guid = (int) get_input('folder_guid', 0);
$tags = get_input("tags");

$prefix = "file/";

if ($container_guid == 0) {
	$container_guid = elgg_get_logged_in_user_guid();
}

elgg_make_sticky_form('file');

if (!$guid) {
	$error = false;
	if (is_array($_FILES["upload"]["name"])) {
		foreach ($_FILES["upload"]["name"] as $number => $name) {
			if ($_FILES["upload"]["error"][$number] !== 0) {
        		register_error(elgg_echo("file:uploadfailed"));
        		continue;
			}

			$file = new FilePluginFile();
			$file->subtype = "file";
			$file->access_id = $access_id;
			$file->container_guid = $container_guid;
			$file->title = $name;
			$file->originalfilename = $name;

			$filestorename = elgg_strtolower(time() . $name);
			$file->setFilename($prefix . $filestorename);

			$mime_type = file_tools_get_mimetype($_FILES["upload"]["tmp_name"][$number], $_FILES["upload"]["type"][$number]);
			$file->setMimeType($mime_type);
			$file->simpletype = file_get_simple_type($mime_type);

			$guid = $file->save();

			// Open the file to guarantee the directory exists
			$file->open("write");
			$file->close();

			move_uploaded_file($_FILES["upload"]["tmp_name"][$number], $file->getFilenameOnFilestore());

			if ($guid && $file->simpletype == "image") {
				file_tools_generate_thumbs($file);
				$file->icontime = time();
				$file->save();
			}

	        add_to_river('river/object/file/create', 'create', elgg_get_logged_in_user_guid(), $file->guid);

		}
	} else {
		if ($_FILES["upload"]["error"] !== 0) {
    		register_error(elgg_echo("file:uploadfailed"));
    		continue;
		}

		// if no title on new upload, grab filename
		if (empty($title)) {
			$title = htmlspecialchars($_FILES["upload"]["name"], ENT_QUOTES, "UTF-8");
		}

		$file = new FilePluginFile();
		$file->subtype = "file";
		$file->access_id = $access_id;
		$file->container_guid = $container_guid;
		$file->title = $title;
		$file->tags = string_to_tag_array($tags);
		$file->originalfilename = $_FILES["upload"]["name"];

		$filestorename = elgg_strtolower(time() . $_FILES["upload"]["name"]);
		$file->setFilename($prefix . $filestorename);

		$mime_type = file_tools_get_mimetype($_FILES["upload"]["tmp_name"], $_FILES["upload"]["type"]);
		$file->setMimeType($mime_type);
		$file->simpletype = file_get_simple_type($mime_type);

		$guid = $file->save();

		// Open the file to guarantee the directory exists
		$file->open("write");
		$file->close();

		move_uploaded_file($_FILES["upload"]["tmp_name"], $file->getFilenameOnFilestore());

		if ($guid && $file->simpletype == "image") {
			file_tools_generate_thumbs($file);
			$file->icontime = time();
			$file->save();
		}

        add_to_river('river/object/file/create', 'create', elgg_get_logged_in_user_guid(), $file->guid);
	}

	system_message(elgg_echo("file:saved"));
} else {
	// load original file object
	$file = new FilePluginFile($guid);
	if (!$file) {
		register_error(elgg_echo('file:cannotload'));
		forward(REFERER);
	}

	// user must be able to edit file
	if (!$file->canEdit()) {
		register_error(elgg_echo('file:noaccess'));
		forward(REFERER);
	}

	if ($_FILES["upload"]["name"] && $_FILES["upload"]["error"] === 0) {
		unlink($file->getFilenameOnFilestore());
		move_uploaded_file($_FILES["upload"]["tmp_name"], $file->getFilenameOnFilestore());

		file_tools_generate_thumbs($file);
	}

	$file->title = $title;
	$file->access_id = $access_id;
	$file->tags = string_to_tag_array($tags);
	$file->save();
}

elgg_clear_sticky_form("file");

$container = get_entity($container_guid);
if (elgg_instanceof($container, "group")) {
    if ($folder_guid) {
        $forward_url = "file/group/{$container->guid}/all#{$folder_guid}";
    } else {
        $forward_url = "file/group/{$container->guid}/all";
    }
} else {
    if ($folder_guid) {
        $forward_url = "file/owner/{$container->username}#{$folder_guid}";
    } else {
        $forward_url = "file/owner/{$container->username}";
    }
}

forward($forward_url);