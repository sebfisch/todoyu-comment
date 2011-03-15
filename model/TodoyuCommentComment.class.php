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
 * Task comment object
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentComment extends TodoyuBaseObject {

	/**
	 * Initialize comment
	 *
	 * @param	Integer		$idComment		Comment ID
	 */
	public function __construct($idComment) {
		$idComment	= intval($idComment);

		parent::__construct($idComment, 'ext_comment_comment');
	}



	/**
	 * Get ID of the task the comment is added to
	 *
	 * @return	Integer
	 */
	public function getTaskID() {
		return intval($this->data['id_task']);
	}



	/**
	 * Get task the comment is added to
	 *
	 * @return	TodoyuProjectTask
	 */
	public function getTask() {
		return TodoyuProjectTaskManager::getTask($this->getTaskID());
	}



	/**
	 * Get ID of the project of the task the comment is added to
	 *
	 * @return	Integer
	 */
	public function getProjectID() {
		return $this->getTask()->getProjectID();
	}



	/**
	 * Get project of the task the comment is added to
	 *
	 * @return	TodoyuProjectProject
	 */
	public function getProject() {
		return $this->getTask()->getProject();
	}



	/**
	 * Get persons being stored to have a feedback requested from to this comment
	 *
	 * @return	Array
	 */
	public function getFeedbackPersons() {
		return TodoyuCommentFeedbackManager::getFeedbackPersons($this->getID());
	}



	/**
	 * Check if comment is locked because of its task
	 *
	 * @return	Boolean
	 */
	public function isLocked() {
		return TodoyuProjectTaskManager::isLocked($this->getTaskID());
	}



	/**
	 * Load comment foreign data: creator, feedback persons, approval state
	 */
	protected function loadForeignData() {
		$this->data['person_create']	= $this->getCreatePerson()->getTemplateData(false);

		$this->data['persons_feedback']	= TodoyuCommentFeedbackManager::getFeedbackPersons($this->getID());
		$this->data['persons_email']	= TodoyuCommentMailManager::getEmailPersons($this->getID());

			// Persons that the comment has been mailed to without a feedback request?
		$personIDsEmailedTo	= array_keys($this->data['persons_email']);
		$personIDsFeedback	= array_keys($this->data['persons_feedback']);
		$this->data['person_ids_mailonly']	= array_diff($personIDsEmailedTo, $personIDsFeedback);

		$this->data['unapproved']		= TodoyuCommentFeedbackManager::isCommentUnapproved($this->getID());
		$this->data['locked']			= $this->isLocked();
	}



	/**
	 * Prepare comments rendering template data (creation person, having been seen status, feedback persons)
	 *
	 * @param	Boolean		$loadForeignRecords
	 * @return	Array
	 */
	public function getTemplateData($loadForeignRecords = false) {
		if( $loadForeignRecords ) {
			$this->loadForeignData();
		}

		return parent::getTemplateData();
	}
}

?>