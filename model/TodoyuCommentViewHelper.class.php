<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

/**
 * Helper functions for comment views
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentViewHelper {

	/**
	 * Get option array of persons which can receive the comment email (project members)
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getEmailReceiverOptions(TodoyuFormElement $field) {
		$idTask		= intval($field->getForm()->getHiddenField('id_task'));
		$options	= array();
		$persons	= TodoyuCommentManager::getEmailReceivers($idTask);

		foreach($persons as $person) {
			$options[] 	= array(
				'value'	=> $person['id'],
				'label'	=> TodoyuPersonManager::getLabel($person['id'], true, true)
			);
		}

		return $options;
	}



	/**
	 * Get option of task owner as comment email receiver
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getTaskOwnerEmailOption(TodoyuFormElement $field) {
		return TodoyuTaskViewHelper::getOwnerEmailOption($field);
	}



	/**
	 * Get option array for feedback select in comment edit form
	 * The options are grouped in main groups with contain the options for
	 * the persons
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getFeedbackPersonsGroupedOptions(TodoyuFormElement $field) {
		$formData	= $field->getForm()->getFormData();

		$idTask		= intval($formData['id_task']);
		$idProject	= TodoyuTaskManager::getProjectID($idTask);
		$options	= array();

			// Task persons
		$groupLabel	= Label('comment.group.taskmembers');
		$taskPersons= TodoyuTaskManager::getTaskPersons($idTask, true);
		foreach($taskPersons as $person) {
			if( $person['id'] != personid() ) {
				$options[$groupLabel][$person['id']] = array(
					'value'	=> $person['id'],
					'label'	=> TodoyuPersonManager::getLabel($person['id'], false, true)
				);
			}
		}

			// Get project persons
		$groupLabel		= Label('comment.group.projectmembers');
		$projectPersons	= TodoyuProjectManager::getProjectPersons($idProject, true, true);
		foreach($projectPersons as $person) {
			if( $person['id'] != personid() ) {
				$options[$groupLabel][$person['id']] = array(
					'value'	=> $person['id'],
					'label'	=> TodoyuPersonManager::getLabel($person['id'])
				);
			}
		}

			// Get staff persons (employees of internal company)
		if( allowed('contact', 'person:seeAllInternalPersons') ) {
			$groupLabel	= Label('comment.group.employees');
			$options[$groupLabel]	= TodoyuContactViewHelper::getInternalPersonOptions($field);
		}

		return $options;
	}



	/**
	 * Get task owner option for feedback select
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getFeedbackOwnerOption(TodoyuFormElement $field) {
		$idTask		= intval($field->getForm()->getHiddenField('id_task'));
		$taskOwner	= TodoyuTaskManager::getTaskOwner($idTask);

		$option = array(
			0 => array(
				'value'	=> $taskOwner[0]['id'],
				'label'	=> TodoyuPersonManager::getLabel($taskOwner[0]['id'])
			)
		);

		return $option;
	}

}

?>