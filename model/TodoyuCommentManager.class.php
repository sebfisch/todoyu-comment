<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * Manage task comments
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentManager {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE = 'ext_comment_comment';



	/**
	 * Get a comment
	 *
	 * @param	Integer		$idComment
	 * @return	TodoyuComment
	 */
	public static function getComment($idComment) {
		return TodoyuRecordManager::getRecord('TodoyuComment', $idComment);
	}



	/**
	 * Get a comment as an array
	 *
	 * @param	Integer		$idComment
	 * @return	Array
	 */
	public static function getCommentData($idComment) {
		return TodoyuRecordManager::getRecordData(self::TABLE, $idComment);
	}



	/**
	 * Filter HTML tags inside comment text to keep only allowable ones
	 *
	 * @param	String		$comment
	 * @return	String
	 */
	public static function filterHtmlTags($text) {
		return strip_tags($text, Todoyu::$CONFIG['EXT']['contact']['allowabletags']);
	}



	/**
	 * Save comment
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function saveComment(array $data) {
		$idComment	= intval($data['id']);
		$xmlPath	= 'ext/comment/config/form/comment.xml';

		if( $idComment === 0 ) {
			$idComment = self::addComment();
		}

		$data['comment']	= TodoyuCommentManager::filterHtmlTags($data['comment']);

			// Call hooked save data functions
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idComment);
		$data	= self::saveCommentForeignRecords($data, $idComment);

			// Extract feedback and email data
		$sendAsEmail	= intval($data['sendasemail']) === 1;
		$personsEmail	= array_unique(TodoyuArray::intExplode(',', $data['emailreceivers'], true, true));
		$personsFeedback= array_unique(TodoyuArray::intExplode(',', $data['feedback'], true, true));

			// Remove special handled fields
		unset($data['sendasemail']);
		unset($data['emailreceivers']);
		unset($data['feedback']);

			// Update comment in database
		self::updateComment($idComment, $data);

			// Clear record cache
		TodoyuRecordManager::removeRecordCache('TodoyuComment', $idComment);
		TodoyuRecordManager::removeRecordQueryCache(self::TABLE, $idComment);

			// Set all comments in task as send
		TodoyuCommentFeedbackManager::setTaskCommentsAsSeen($data['id_task']);

			// Register feedback for current comment
		if( sizeof($personsFeedback) > 0 ) {
			TodoyuCommentFeedbackManager::updateFeedbacks($idComment, $personsFeedback);
		}

			// Call saved hook
		TodoyuHookManager::callHook('comment', 'saved', array($idComment));

		// Send emails
		if( $sendAsEmail && sizeof($personsEmail) > 0 ) {

			TodoyuCommentMailer::sendEmails($idComment, $personsEmail);
			TodoyuHeader::sendTodoyuHeader('sentEmail', true);
		}

		return $idComment;
	}



	/**
	 * Add comment
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addComment(array $data = array()) {
		return TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Update a comment
	 *
	 * @param	Integer		$idComment
	 * @param	Array		$data
	 * @return	Boolean
	 */
	public static function updateComment($idComment, array $data) {
		return TodoyuRecordManager::updateRecord(self::TABLE, $idComment, $data);
	}



	/**
	 * Delete a comment
	 *
	 * @param	Integer		$idComment
	 */
	public static function deleteComment($idComment) {
		TodoyuRecordManager::deleteRecord(self::TABLE, $idComment);
	}



	/**
	 * Save extra comment data
	 *
	 * @param	Array		$data
	 * @param	Integer		$idComment
	 * @return	Array
	 * @todo	check / remove
	 */
	public static function saveCommentForeignRecords(array $data, $idComment) {

		return $data;
	}



	/**
	 * Get all comments of a task ordered by creation date
	 *
	 * @param	Integer		$idTask
	 * @param	Boolean		$desc
	 * @return	Array
	 */
	public static function getTaskComments($idTask, $desc = false) {
		$idTask	= intval($idTask);

		$where	= 'id_task = ' . $idTask . ' AND deleted = 0';
		$order	= 'date_create ' . ($desc ? 'DESC' : 'ASC');

		return TodoyuRecordManager::getAllRecords(self::TABLE, $where, $order);
	}



	/**
	 * Get the IDs of all comments of a task
	 *
	 * @param	Integer		$idTask
	 * @param	Boolean		$desc
	 * @return	Array
	 */
	public static function getTaskCommentIDs($idTask, $desc = false) {
		$idTask	= intval($idTask);
		$sortDir= $desc ? 'DESC' : 'ASC';

		$fields	= 'id';
		$table	= self::TABLE;
		$where	= 'id_task = ' . $idTask . ' AND deleted = 0';
		$order	= 'date_create ' . $sortDir;

			// Limit comment it own and public if person can't see ALL comments
		if( ! allowed('comment', 'comment:seeAll') ) {
			$where .= ' AND	(
							id_person_create	= ' . personid() . ' OR
							is_public		= 1
						)';
		}

		return Todoyu::db()->getColumn($fields, $table, $where, '', $order);
	}



	/**
	 * Get the number of comments of a task
	 *
	 * @param	Integer		$idTask
	 * @return	Integer
	 */
	public static function getNumberOfTaskComments($idTask) {
		return sizeof(self::getTaskCommentIDs($idTask));
	}



	/**
	 * Change comments public flag
	 *
	 * @param	Integer		$idComment
	 * @param	Boolean		$public
	 * @return	Boolean
	 */
	public static function setPublic($idComment, $public = true) {
		$idComment	= intval($idComment);
		$data		= array(
			'is_public' => ($public ? 1 : 0)
		);

		return self::updateComment($idComment, $data);
	}



	/**
	 * Check whether a person is the create of a comment
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isCreator($idComment, $idPerson = 0) {
		$idComment	= intval($idComment);
		$idPerson	= personid($idPerson);

		$fields	= 'id';
		$table	= self::TABLE;
		$where	= 'id = ' . $idComment . ' AND id_person_create = ' . $idPerson;

		return Todoyu::db()->hasResult($fields, $table, $where);
	}



	/**
	 * Get Persons which could receive a comment email
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getEmailReceivers($idTask) {
		$idTask		= intval($idTask);
		$idProject	= TodoyuTaskManager::getProjectID($idTask);

		$taskPersons	= TodoyuTaskManager::getTaskPersons($idTask);
		$projectPersons	= TodoyuProjectManager::getProjectPersons($idProject);
		$internalPersons= TodoyuPersonManager::getInternalPersons();

		$persons = array();

			// Add task Persons
		foreach($taskPersons as $person) {
			if( ! empty($person['email']) ) {
				$persons[$person['id']] = $person;
			}
		}

			// Add project Persons
		foreach($projectPersons as $person) {
			if( ! empty($person['email']) ) {
				$persons[$person['id']] = $person;
			}
		}

			// Add internal Persons
		foreach($internalPersons as $person) {
			if( ! empty($person['email']) ) {
				$persons[$person['id']] = $person;
			}
		}

		return $persons;
	}



	/**
	 * Toggle comment public flag
	 *
	 * @param	Integer		$idComment
	 * @return	Integer
	 */
	public static function togglePublic($idComment) {
		$idComment	= intval($idComment);

		return Todoyu::db()->doBooleanInvert(self::TABLE, $idComment, 'is_public');
	}



	/**
	 * Get items for the task context menu
	 *
	 * @param	Integer		$idTask
	 * @param	Array		$items
	 * @return	Array
	 */
	public static function getTaskContextMenuItems($idTask, array $items) {
		$idTask	= intval($idTask);
		$task	= TodoyuTaskManager::getTask($idTask);
		$allowed= array();

		if( $task->isTask() && !$task->isLocked() ) {
			$ownItems	=& Todoyu::$CONFIG['EXT']['comment']['ContextMenu']['Task'];

			$allowed['add']['submenu']['add-comment'] = $ownItems['add']['submenu']['add-comment'];
		}

		return array_merge_recursive($items, $allowed);
	}



	/**
	 * Get IDs of tasks with a requested feedback from current person
	 *
	 * @return	Array
	 */
	public static function getFeedbackTaskIDs() {
		$conditions	= Todoyu::$CONFIG['EXT']['comment']['feedbackTabFilters'];
		$taskFilter	= new TodoyuTaskFilter($conditions);

		return $taskFilter->getTaskIDs('ext_comment_comment.date_create');
	}



	/**
	 * Get ID of the person which requested a feedback from the current user and the feedback is open
	 *
	 * @param	Integer		$idTask
	 * @return	Integer
	 */
	public static function getOpenFeedbackRequestPersonID($idTask) {
		$idTask	= intval($idTask);

		$field	= '	fb.id_person_create';
		$tables	= '	ext_comment_feedback fb,
					ext_comment_comment co';
		$where	= '		fb.id_comment			= co.id'
				. ' AND co.id_task				= ' . $idTask
				. ' AND	fb.id_person_feedback	= ' . TodoyuAuth::getPersonID()
				. ' AND	fb.is_seen				= 0';
		$order	= '	fb.date_create DESC';
		$limit	= 1;
		$resField	= 'id_person_create';

		$idPerson	= Todoyu::db()->getFieldValue($field, $tables, $where, '', $order, $limit, $resField);

		return intval($idPerson);
	}

}

?>