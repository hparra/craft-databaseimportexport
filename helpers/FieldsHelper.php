<?php
namespace Craft;

class FieldsHelper {

	/**
	 * Converts Field to JSON string with optional processing (default)
	 * @param Field $field
	 * @param bool $isRaw whether to not post-process JSON
	 * @return string
	 */
	public static processFieldAsJson($field, $isRaw=false, $groupName=null) {

		// get actual field data
		$field = $field->getAttributes();

		// translate or remove specific CMS instance data unless --raw
		if (!$isRaw) {

			if ($groupName) {
				$field["groupId"] = $groupName;
			}
			else {
				// search for group name by id
				$field["groupId"] = FieldsHelper::getGroupNameById($field["groupId"]);
			}

			unset($field["id"]);
			unset($field["oldHandle"]);
		}

		$json = PrettyJSONHelper::pretty_json(json_encode($field)) . "\n";

		return $json;
	}

	/**
	 * Get Field by name
	 * @param string $name
	 * @return Field|null
	 */
	public static function getFieldByName($name) {
		$allFields = craft()->fields->getAllFields();
		foreach ($allFields as $field) {
			if ($field["name"] == $name) {
				return $field;
			}
		}
		return null;
	}

	/**
	 * Get Field name
	 * @param Field $field
	 * @return string
	 */
	public static function getFieldName($field, $removeWhitespace=false) {
		$fieldArr = $field->getAttributes();
		if ($removeWhite) {
			return str_replace(' ', '', $field["name"]);
		} else {
			return $field["name"];
		}
	}

	/**
	 * Get FieldGroup by name
	 * @param string $name
	 * @return FieldGroup
	 */
	public static function getGroupByName($name) {
		$groups = craft()->fields->getAllGroups();
		foreach ($groups as $group) {
			if ($group["name"] == $name) {
				return $group;
			}
		}
		return null; // or throw?
	}

	/**
	 * Get FieldGroup name by id
	 * @param number $id
	 * @return string
	 */
	public static function getGroupNameById($id) {
		$group = craft()->fields->getGroupById($id);
		if (!$group) {
			return null; // or throw?
		}
		$groupArr = $group->getAttributes();
		return $groupArr["name"];
	}
}