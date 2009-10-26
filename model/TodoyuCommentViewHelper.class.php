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
	 * Get option array of users which can receive the comment email (project members)
	 *
	 * @param	Array		$formData
	 * @return	Array
	 */
	public static function getEmailReceiverOptions(TodoyuFormElement $field) {
		$idTask		= intval($field->getForm()->getHiddenField('id_task'));
		$options	= array();
		$users		= TodoyuCommentManager::getEmailReceivers($idTask);

		foreach($users as $user) {
			$options[] 	= array(
				'value'	=> $user['id'],
				'label'	=> $user['lastname'] . ' ' . $user['firstname'] . ' (' . $user['email'] . ')'
			);
		}

		return $options;
	}



	/**
	 * Get option array for feedback select in comment edit form
	 * The options are grouped in main groups with contain the options for
	 * the users
	 *
	 * @param	Array		$formData
	 * @return	Array
	 */
	public static function getFeedbackUsersGroupedOptions(TodoyuFormElement $field) {
		$formData	= $field->getForm()->getFormData();
		$idTask		= intval($formData['id_task']);
		$idProject	= TodoyuTaskManager::getProjectID($idTask);
		$options	= array();

			// Task users
		$users	= TodoyuTaskManager::getTaskUsers($idTask);

		$group	= Label('comment.group.taskmembers');
		foreach($users as $user) {
			$options[$group][] = array(
				'value'	=> $user['id'],
				'label'	=> $user['lastname'] . ' ' . $user['firstname']
			);
		}

			// Get project users
		$users	= TodoyuProjectManager::getProjectUsers($idProject);
		$group	= Label('comment.group.projectmembers');
		foreach($users as $user) {
			$options[$group][] = array(
				'value'	=> $user['id'],
				'label'	=> $user['lastname'] . ' ' . $user['firstname']
			);
		}

			// Get staff users
		$users	= TodoyuUserManager::getInternalUsers();
		$group	= Label('comment.group.employee');
		foreach($users as $user) {
			$options[$group][] = array(
				'value'	=> $user['id'],
				'label'	=> $user['lastname'] . ' ' . $user['firstname']
			);
		}

		return $options;
	}

}


?>