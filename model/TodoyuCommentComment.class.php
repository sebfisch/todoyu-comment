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
	 * Check whether comment has open feedbacks from external persons
	 *
	 * @return	Boolean
	 */
	public function hasOpenFeedbacksFromExternals() {
		$fields	= '	c.id';
		$tables	= '	ext_comment_mm_comment_feedback f,
					ext_contact_mm_company_person mmcp,
					ext_contact_company c';
		$where	= '		f.id_comment		= ' . $this->getID()
				. ' AND f.is_seen			= 0'
				. ' AND f.id_person_feedback= mmcp.id_person'
				. ' AND mmcp.id_company		= c.id'
				. ' AND c.is_internal		= 0';

		return Todoyu::db()->hasResult($fields, $tables, $where, '', 1);
	}



	/**
	 * Get update person ID
	 *
	 * @return	Integer
	 */
	public function getPersonUpdateID() {
		return $this->getInt('id_person_update');
	}



	/**
	 * Get update person
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPersonUpdate() {
		return TodoyuContactPersonManager::getPerson($this->getPersonUpdateID());
	}

	

	/**
	 * Check whether comment has an update person
	 *
	 * @return	Boolean
	 */
	public function hasPersonUpdate() {
		return $this->getPersonUpdateID() !== 0;
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
	 * @return	Boolean
	 */
	public function isPublic() {
		return intval($this->data['is_public']) === 1;
	}



	/**
	 * Check whether current person can delete this comment
	 *
	 * @return	Boolean
	 */
	public function canCurrentUserDelete() {
		$deleteAll	= Todoyu::allowed('comment', 'comment:deleteAll');
		$deleteOwn	= Todoyu::allowed('comment','comment:deleteOwn') && $this->isCurrentPersonCreator();

		return (! $this->isLocked()) && ($deleteAll || $deleteOwn);
	}



	/**
	 * Check whether the current user can edit the comment
	 *
	 * @return	Boolean
	 */
	public function canCurrentUserEdit() {
		$editAll	= Todoyu::allowed('comment', 'comment:editAll');
		$editOwn	= Todoyu::allowed('comment','comment:editOwn') && $this->isCurrentPersonCreator();

		return !$this->isLocked() && $editAll || $editOwn;
	}



	/**
	 * Check whether the current user can make the comment public
	 *
	 * @return	Boolean
	 */
	public function canCurrentUserMakePublic() {
		return $this->canCurrentUserEdit();
	}



	/**
	 * Get label for update info
	 *
	 * @return	String|Boolean
	 */
	public function getUpdateInfoLabel() {
		$label	= false;

		if( $this->hasPersonUpdate() ) {
			$data	= array(
				$this->getPersonUpdate()->getFullName(),
				TodoyuTime::format($this->getDateUpdate(), 'datetime')
			);
			$label	= TodoyuLabelManager::getFormatLabel('comment.ext.updateInfo', $data);
		}

		return $label;
	}



	/**
	 * Get warning label if problems with a comments occur
	 * Problems:
	 * - Feedback is requested from an external person
	 * but:
	 * - Task is not public
	 * - Comment is not public
	 *
	 * @return	String|Boolean
	 */
	public function getPublicFeedbackWarning() {
		$label			= false;

		if( TodoyuAuth::isInternal() ) {
			if( $this->hasOpenFeedbacksFromExternals() ) {
				if( !$this->getTask()->isPublic() ) {
					$label	= Todoyu::Label('comment.ext.publicFeedbackWarning.task');
				} elseif( !$this->isPublic() ) {
					$label	= Todoyu::Label('comment.ext.publicFeedbackWarning.comment');
				}
			}
		}

		return $label;
	}



	/**
	 * Load comment foreign data: creator, feedback persons, approval state
	 */
	protected function loadForeignData() {
		$this->data['person_create']	= $this->getPersonCreate()->getTemplateData(false);

		$this->data['persons_feedback']	= TodoyuCommentFeedbackManager::getFeedbackPersons($this->getID());
		$this->data['persons_email']	= TodoyuCommentMailManager::getEmailPersons($this->getID());

			// Persons that the comment has been mailed to without a feedback request?
		$personIDsEmailedTo	= array_keys($this->data['persons_email']);
		$personIDsFeedback	= array_keys($this->data['persons_feedback']);
		$this->data['person_ids_mailonly']	= array_diff($personIDsEmailedTo, $personIDsFeedback);

		$this->data['isUnapproved']		= TodoyuCommentFeedbackManager::isCommentUnseen($this->getID());
		$this->data['locked']			= $this->isLocked();
		$this->data['canDelete']		= $this->canCurrentUserDelete();
		$this->data['canEdit']			= $this->canCurrentUserEdit();
		$this->data['canMakePublic']	= $this->canCurrentUserMakePublic();
		$this->data['updateInfo']		= $this->getUpdateInfoLabel();
		$this->data['publicFeedbackWarning'] = $this->getPublicFeedbackWarning();
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