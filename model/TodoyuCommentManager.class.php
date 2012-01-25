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
 * General comment extension manager
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentManager {

	/**
	 * Get IDs of roles which require auto feedback to task owner
	 *
	 * @return	Integer[]
	 */
	public static function getAutoFeedbackRoleIDs() {
		$autoFeedbackRoleConfig	= TodoyuSysmanagerExtConfManager::getExtConfValue('comment', 'autorequestownerfeedback');

		return TodoyuArray::intExplode(',', $autoFeedbackRoleConfig);
	}



	/**
	 * Get IDs of roles which require auto mail to task owner
	 *
	 * @return	Integer[]
	 */
	public static function getAutoMailRoleIDs() {
		$autoMailRoleConfig	= TodoyuSysmanagerExtConfManager::getExtConfValue('comment', 'automailcommenttoowner');

		return TodoyuArray::intExplode(',', $autoMailRoleConfig);
	}



	/**
	 * Check whether the current user is in a group which requires auto feedback
	 *
	 * @return	Boolean
	 */
	public static function hasCurrentUserAutoFeedbackRole() {
		$roles	= self::getAutoFeedbackRoleIDs();

		return TodoyuContactPersonManager::hasAnyRole($roles);
	}



	/**
	 * Check whether the current user is in a group which requires auto email
	 *
	 * @return	Boolean
	 */
	public static function hasCurrentUserAutoMailRole() {
		$roles	= self::getAutoMailRoleIDs();

		return TodoyuContactPersonManager::hasAnyRole($roles);
	}



	/**
	 * Hide feedback field and add info comment about auto feedback
	 * Only if users is in a matching role
	 *
	 * @param	TodoyuForm		$form
	 * @param	Integer			$idComment
	 * @param	Array			$params
	 * @return	TodoyuForm		$form
	 */
	public static function hookAddAutoFeedbackFields(TodoyuForm $form, $idComment = 0, array $params = array()) {
		if( self::hasCurrentUserAutoFeedbackRole() ) {
			$idTask	= intval($params['task']);
			$task	= TodoyuProjectTaskManager::getTask($idTask);
			$owner	= $task->getOwnerPerson();

				// Hide field to prevent change
			$form->getField('feedback')->setAttribute('style', 'display:none');
				// Add comment field for info
			$form->getFieldset('main')->addFieldElement('feedbackinfo', 'comment', array(
				'comment'	=> $owner->getFullName(),
				'label'		=> 'comment.ext.edit.autoFeedbackFromOwner'
			));
		}

		return $form;
	}



	/**
	 * Hide email fieldset and add info comment about auto email
	 * Only if users is in a matching role
	 *
	 * @param	TodoyuForm		$form
	 * @param	Integer			$idComment
	 * @param	Array			$params
	 * @return	TodoyuForm		$form
	 */
	public static function hookAddAutoMailFields(TodoyuForm $form, $idComment = 0, array $params = array()) {
		if( self::hasCurrentUserAutoMailRole() ) {
			$idTask	= intval($params['task']);
			$task	= TodoyuProjectTaskManager::getTask($idTask);
			$owner	= $task->getOwnerPerson();

				// Hide field to prevent change
			$form->getFieldset('email')->setClass('hidden');
				// Add comment field for info
			$form->getFieldset('main')->addFieldElement('mailinfo', 'comment', array(
				'comment'	=> $owner->getFullName(),
				'label'		=> 'comment.ext.edit.autoSentEmailToTaskOwner'
			));
		}

		return $form;
	}
	


	/**
	 * Add task owner as feedback person
	 *
	 * @see		hookAddAutoFeedbackFields
	 * @param	Array		$data
	 * @param	Integer		$idComment
	 * @param	Array		$params
	 * @return	Array
	 */
	public static function hookLoadDataAutoFeedback(array $data, $idComment, array $params = array()) {
		if( self::hasCurrentUserAutoFeedbackRole() ) {
			$idTask	= intval($params['task']);
			$task	= TodoyuProjectTaskManager::getTask($idTask);
			$idOwner= $task->getPersonID('owner'); // project 1.3 $task->getOwnerPersonID();

			$data['feedback'] = array($idOwner);
		}

		return $data;
	}



	/**
	 * Add task owner as email receiver and enable send email
	 *
	 * @see		hookAddAutoMailFields
	 * @param	Array		$data
	 * @param	Integer		$idComment
	 * @param	Array		$params
	 * @return	Array
	 */
	public static function hookLoadDataAutoMail(array $data, $idComment, array $params = array()) {
		if( self::hasCurrentUserAutoMailRole() ) {
			$idTask	= intval($params['task']);
			$task	= TodoyuProjectTaskManager::getTask($idTask);
			$idOwner= $task->getPersonID('owner'); // project 1.3 $task->getOwnerPersonID();

			$data['sendasemail'] = 1;
			$data['emailreceivers'] = array($idOwner);
		}

		return $data;
	}



	/**
	 * Get comment form
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idTask
	 * @param	Array		$formData
	 * @param	Array		$params
	 * @return	TodoyuForm
	 */
	public static function getCommentForm($idComment, $idTask, array $formData = array(), array $params = array()) {
		$xmlPath= 'ext/comment/config/form/comment.xml';
		$params['task'] = $idTask;

		$form	= TodoyuFormManager::getForm($xmlPath, $idComment, $params);

		if( sizeof($formData) ) {
			$form->setFormData($formData);
		}

		return $form;
	}

}

?>