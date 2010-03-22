<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

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
	 * @param	Array		$formData
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
	 * @param	Array		$formData
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
	 * @param	Array		$formData
	 * @return	Array
	 */
	public static function getFeedbackPersonsGroupedOptions(TodoyuFormElement $field) {
		$formData	= $field->getForm()->getFormData();

		$idTask		= intval($formData['id_task']);
		$idProject	= TodoyuTaskManager::getProjectID($idTask);
		$options	= array();

			// Task persons
		$groupLabel	= Label('comment.group.taskmembers');
		$taskPersons= TodoyuTaskManager::getTaskPersons($idTask);
		foreach($taskPersons as $person) {
			$options[$groupLabel][$person['id']] = array(
				'value'	=> $person['id'],
				'label'	=> TodoyuPersonManager::getLabel($person['id'], false, true)
			);
		}

			// Get project persons
		$groupLabel		= Label('comment.group.projectmembers');
		$projectPersons	= TodoyuProjectManager::getProjectPersons($idProject);
		foreach($projectPersons as $person) {
			$options[$groupLabel][$person['id']] = array(
				'value'	=> $person['id'],
				'label'	=> TodoyuPersonManager::getLabel($person['id'])
			);
		}

			// Get staff persons (employees of internal company)
		$groupLabel	= Label('comment.group.employees');
		$options[$groupLabel]	= TodoyuContactViewHelper::getInternalPersonOptions($field);

		return $options;
	}



	/**
	 * Get task owner option for feedback select
	 *
	 * @param	Array		$formData
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