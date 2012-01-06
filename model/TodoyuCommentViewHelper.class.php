<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
		$personIDs	= TodoyuCommentCommentManager::getEmailReceiverIDs($idTask);

		foreach($personIDs as $idPerson) {
			$person	= TodoyuContactPersonManager::getPerson($idPerson);
			$options[] 	= array(
				'value'	=> $idPerson,
				'label'	=> $person->getLabel(true, true)
			);
		}

		$options	= TodoyuArray::sortByLabel($options, 'label');

		return $options;
	}



	/**
	 * Get option of task owner as comment email receiver
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getTaskOwnerEmailOption(TodoyuFormElement $field) {
		return TodoyuProjectTaskViewHelper::getOwnerEmailOption($field);
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
		$idProject	= TodoyuProjectTaskManager::getProjectID($idTask);
		$options	= array();

			// Task persons
		$groupLabel	= Todoyu::Label('comment.ext.group.taskmembers');
		$taskPersons= TodoyuProjectTaskManager::getTaskPersons($idTask, true);
		foreach($taskPersons as $person) {
			$options[$groupLabel][$person['id']] = array(
				'value'	=> $person['id'],
				'label'	=> TodoyuContactPersonManager::getLabel($person['id'], false, true)
			);
		}

			// Get project persons
		$groupLabel		= Todoyu::Label('comment.ext.group.projectmembers');
		$projectPersons	= TodoyuProjectProjectManager::getProjectPersons($idProject, true, true);
		foreach($projectPersons as $person) {
			$options[$groupLabel][$person['id']] = array(
				'value'	=> $person['id'],
				'label'	=> TodoyuContactPersonManager::getLabel($person['id'])
			);
		}

			// Get staff persons (employees of internal company)
		if( Todoyu::allowed('contact', 'person:seeAllInternalPersons') ) {
			$groupLabel				= Todoyu::Label('comment.ext.group.employees');
			$options[$groupLabel]	=  TodoyuContactViewHelper::getInternalPersonOptions($field);;
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
		$taskOwner	= TodoyuProjectTaskManager::getTaskOwner($idTask);

		$option = array(
			0 => array(
				'value'	=> $taskOwner[0]['id'],
				'label'	=> TodoyuContactPersonManager::getLabel($taskOwner[0]['id'])
			)
		);

		return $option;
	}

}

?>