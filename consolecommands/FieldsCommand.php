<?php
namespace Craft;

// TODO: is there a better way to include helpers?
require __DIR__.'/../helpers/PrettyJSONHelper.php';
require __DIR__.'/../helpers/FieldsHelper.php';

/**
 * Craft Field Manipulation by Hector Parra
 *
 * @package   Craft
 * @author    Hector Parra
 * @copyright Copyright (c) 2013, Hector Parra
 * @license   MIT
 */

/**
 *
 */
class FieldsCommand extends \CConsoleCommand {

	public $defaultAction = 'help';

	public function beforeAction($action, $params) {
		return true;
	}

	public function actionHelp() {
		echo $this->getHelp();
	}

	//
	// EXPORTING
	//

	/**
	 * Exports FieldGroups as JSON files
	 * @param string $path where to create JSON files
	 * @param bool $raw whether to not post-process JSON
	 * @return bool
	 */
	public function actionExportAllGroups($path="./", $raw=false, $verbose=true) {

		$groups = craft()->fields->getAllGroups();
		foreach ($groups as $group) {

			// get actual field data
			$group = $group->getAttributes();

			// translate or remove specific CMS instance data unless --raw
			if (!$raw) {
				unset($group["id"]);
			}

			$json = PrettyJSONHelper::pretty_json(json_encode($group)) . "\n";

			$filepath = $path.str_replace(' ','',$group["name"]).".json";

			// FIXME: proper path concat
			file_put_contents($filepath, $json);

			if ($verbose) {
				echo "Exported $filepath\n";
			}
		}

		return 0;
	}

	/**
	 * Exports all Fields as JSON files in directories named after FieldGroup
	 * @param string $name FieldGroup.name
	 * @param string $path where to create JSON files
	 * @param bool $raw whether to not post-process JSON
	 * @return bool
	 */
	public function actionExportAllFields($name, $path="./", $raw=false) {

		// get all the groups
		$groups = craft()->fields->getAllGroups();
		foreach ($groups as $group) {

			// get all fields in each group
			$fields = craft()->fields->getFieldsByGroupId($group->id);

			// create a folder in path with group name
			//

			foreach ($fields as $field) {


				if ($verbose) {
					echo "Exported $filepath\n";
				}
			}
		}
	}

	/**
	 * Exports Fields in FieldGroup as JSON files
	 * @param string $name FieldGroup.name
	 * @param string $path where to create JSON files
	 * @param bool $raw whether to not post-process JSON
	 * @return bool
	 */
	public function actionExportFieldsInGroup($name, $path="./", $raw=false) {

		$group = FieldsHelper::getGroupByName($name);
		if (!$group) {
			fwrite(STDERR, "Error: FieldGroup with name \"{$name}\" was not found.\n");
			return 1;
		}

		$fields = craft()->fields->getFieldsByGroupId($group->id);
		foreach ($fields as $field) {
			$fieldName = FieldsHelper::getFieldName($field);
			$json = processFieldAsJson($field);

			// FIXME: proper path concat
			file_put_contents($path . $fieldName . ".json", $json);
		}

		return 0;
	}

	/**
	 * Exports Field by handle as JSON file
	 * @param string $handle Field.handle
	 * @param bool $raw Whether or not to keep install specific attributes
	 * @return bool
	 */
	public function actionExportFieldByHandle($handle, $path="./", $raw=false) {

		// search for Field with this handle
		$field = craft()->fields->getFieldByHandle($handle);
		if (!$field) {
			fwrite(STDERR, "Error: field not found.\n");
			return 1;
		}

		$json = $this->processFieldAsJson($field, $raw);

		file_put_contents($path . $handle . ".json", $json);

		return 0;
	}

	/**
	 * Exports Field by name as JSON file
	 * @param string $handle Field.handle
	 * @param bool $raw Whether or not to keep install specific attributes
	 * @return bool
	 */
	public function actionExportFieldByName($name, $path="./", $raw=false) {

		// search for Field with this handle
		$field = FieldsHelper::getFieldByName($name);
		if (!$field) {
			fwrite(STDERR, "Error: field not found.\n");
			return 1;
		}

		$fieldName =  str_replace(' ', '', $name);
		$json = $this->processFieldAsJson($field, $raw);

		file_put_contents($path . $fieldName . ".json", $json);

		return 0;
	}

	//
	// IMPORTING
	//

	/**
	 * Create or update a Field in database
	 * Expects JSON from STDIN
	 * @return bool
	 */
	// public function actionSetField() {

	// 	// TODO: read file
	// 	// retrieve JSON from STDIN pipe/direction
	// 	$data = file_get_contents("php://stdin");

	// 	// parse JSON into array()
	// 	$newField = json_decode($data, true);

	// 	// check if this Field already exists via name
	// 	// TODO: check if fields always have unique names
	// 	// TODO: check if new Field already has id (e.g. from backed up JSON)
	// 	$allFields = craft()->fields->getAllFields();
	// 	foreach ($allFields as $group) {
	// 		// if field already exists than use its ID for updating
	// 		if ($newField["name"] == $group["name"]) {
	// 			$newField["id"] = $group["id"];
	// 			// TODO: optional optimized groupId lookup here instead of later
	// 			break;
	// 		}
	// 	}

	// 	// lookup groupId if Field JSON uses group name string instead of id number
	// 	if (is_string($newField["groupId"])) {
	// 		// TODO: replace with getGroupByName($newField["groupId"])
	// 		$groups = craft()->fields->getAllGroups();
	// 		foreach ($groups as $group) {
	// 			if ($group["name"] == $newField["groupId"]) {
	// 				$newField["groupId"] = (int) $group["id"];
	// 				break;
	// 			}
	// 		}
	// 		// if still a string then group with name was not found
	// 		if (is_string($newField["groupId"])) {
	// 			fwrite(STDERR, "Error: Field Group named \"" . $newField["groupId"] . "\" was not found\n");
	// 			return 1;
	// 		}
	// 	} else {
	// 		fwrite(STDERR, "Warning: You should not reference groups by numeric id in JSON schema\n");
	// 	}

	// 	// check for shorthanded options for Dropdown or Checkboxes
	// 	// NOTE: Craft may support array of strings for Dropdown
	// 	if ($newField["type"] == "Dropdown" || $newField["type"] == "Checkboxes") {
	// 		// if settings is a string assume JSON came from backup and do nothing
	// 		if (!is_string($newField["settings"])) {
	// 			$newOptions = array();
	// 			foreach ($newField["settings"]["options"] as $option) {
	// 				if (is_string($option)) {
	// 					array_push($newOptions, array(
	// 						"label" => $option,
	// 						"value" => $option,
	// 						"default" => ""
	// 					));
	// 				} else {
	// 					array_push($newOptions, $option);
	// 				}
	// 			}
	// 			$newField["settings"]["options"] = $newOptions;
	// 		} else {
	// 			fwrite(STDERR, "Warning: You should prefer actual JSON for settings spec\n");
	// 		}
	// 	}

	// 	if (!is_string($newField["settings"])) {
	// 		// re-encode "settings" field, it is actually stored in DB as JSON string
	// 		$newField["settings"] = json_encode($newField["settings"]);
	// 	}

	// 	// create new Field
	// 	$group = new FieldModel();
	// 	$group->setAttributes($newField);
	// 	$success = (int) craft()->fields->saveField($group);

	// 	// saveField return true/false which are casted at 0/1, valide for CConsoleCommand exit code
	// 	return $success;
	// }

}
