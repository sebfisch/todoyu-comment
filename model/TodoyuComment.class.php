<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
class TodoyuComment extends TodoyuBaseObject {

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
	 * @return	TodoyuTask
	 */
	public function getTask() {
		return TodoyuTaskManager::getTask($this->getTaskID());
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
	 * @return	TodoyuProject
	 */
	public function getProject() {
		return $this->getTask()->getProject();
	}



	/**
	 * Get ID of the person which added the comment
	 *
	 * @return	Integer
	 */
	public function getCreatePersonID() {
		return $this->getPersonID('create');
	}



	/**
	 * Get the person which added the comment
	 *
	 * @return	TodoyuPerson
	 */
	public function getCreatePerson() {
		return $this->getPerson('create');
	}



	/**
	 * Get persons being stored to have a feedback requested from to this comment
	 *
	 * @return	Array
	 */
	public function getFeedbackPersons() {
		return TodoyuCommentFeedbackManager::getFeedbackPersons($this->id);
	}



	/**
	 * Load comment foreign data: creator, feedback persons, approval state
	 */
	protected function loadForeignData() {
		$this->data['person_create']	= $this->getCreatePerson()->getTemplateData(false);
		$this->data['persons_feedback']	= TodoyuCommentFeedbackManager::getFeedbackPersons($this->id);
		$this->data['unapproved']		= TodoyuCommentFeedbackManager::isCommentUnapproved($this->id);
	}



	/**
	 * Prepare comments rendering template data (creation person, having been seen status, feedback persons)
	 *
	 * @return Array
	 */
	public function getTemplateData($loadForeignRecords = false) {
		if( $loadForeignRecords ) {
			$this->loadForeignData();
		}

		return parent::getTemplateData();;
	}
}

?>