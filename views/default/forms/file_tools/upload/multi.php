<?php
$page_owner = elgg_get_page_owner_entity();
$container_guid = elgg_extract("container_guid", $vars, elgg_get_logged_in_user_guid());
$access_id = elgg_extract("access_id", $vars, ACCESS_DEFAULT);

if(elgg_instanceof($page_owner, "group", null, "ElggGroup")){
	$return_url = $vars["url"] . "file/group/" . $page_owner->getGUID() . "/all";
} else {
	$return_url = $vars["url"] . "file/owner/" . $page_owner->username;
}
?>
<fieldset>
	<div>
		<label><?php echo elgg_echo("file_tools:files"); ?></label>
		<div>
			<?php
				echo elgg_view("input/file", array(
					"name" => "upload[]",
					"multiple" => ""
				));
				echo elgg_view("input/button", array("value" => elgg_echo("file_tools:forms:empty_queue"), "class" => "elgg-button-action hidden", "id" => "file-tools-uploadify-cancel"));
			?>
		</div>
	</div>

	<?php if (file_tools_use_folder_structure()): ?>
		<div>
			<label><?php echo elgg_echo("file_tools:forms:edit:parent"); ?><br />
			<?php
				echo elgg_view("input/folder_select", array("name" => "folder_guid", "value" => get_input("parent_guid"), "id" => "file_tools_file_parent_guid"));
			?>
			</label>
		</div>
	<?php endif; ?>

	<div>
		<label>
			<?php echo elgg_echo("access"); ?><br />
			<?php echo elgg_view("input/access", array("name" => "access_id", "id" => "file_tools_file_access_id", "value" => $access_id)); ?>
		</label>
	</div>

	<div class="elgg-foot">
		<?php
			echo elgg_view("input/securitytoken");
			echo elgg_view("input/hidden", array("name" => "container_guid", "value" => $container_guid));
			echo elgg_view("input/hidden", array("name" => "PHPSESSID", "value" => session_id()));
			echo elgg_view("input/submit", array("value" => elgg_echo("save")));
		?>
	</div>
</fieldset>
