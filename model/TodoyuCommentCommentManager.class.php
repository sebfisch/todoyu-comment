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
 * Manage task comments
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentCommentManager {

	/**
	 * @var	String		Default database table
	 */
	const TABLE = 'ext_comment_comment';

	/**
	 * @var	String
	 */
	const TABLE_FEEDBACK = 'ext_comment_mm_comment_feedback';



	/**
	 * Get a comment
	 *
	 * @param	Integer		$idComment
	 * @return	TodoyuCommentComment
	 */
	public static function getComment($idComment) {
		return TodoyuRecordManager::getRecord('TodoyuCommentComment', $idComment);
	}



	/**
	 * Get task ID the given comment belongs to
	 *
	 * @param	Integer		$idComment
	 * @return	Integer
	 */
	public static function getTaskID($idComment) {
		$idComment	= intval($idComment);

		return self::getComment($idComment)->getTaskID();
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
	 * @param	String		$text
	 * @return	String
	 */
	public static function filterHtmlTags($text) {
		return strip_tags($text, Todoyu::$CONFIG['EXT']['comment']['allowedtags']);
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

		$data['comment']	= self::filterHtmlTags($data['comment']);

			// Call hooked save data functions
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idComment);

			// Extract feedback and email data
		$personIDsFeedback	= array_unique(TodoyuArray::intExplode(',', $data['feedback'], true, true));

			// Remove special handled fields
		unset($data['sendasemail']);
		unset($data['emailreceivers']);
		unset($data['feedback']);

			// Update comment in database
		self::updateComment($idComment, $data);

			// Clear record cache
		TodoyuRecordManager::removeRecordCache('TodoyuCommentComment', $idComment);
		TodoyuRecordManager::removeRecordQueryCache(self::TABLE, $idComment);

			// Set all comments in task as seen
		TodoyuCommentFeedbackManager::setTaskCommentsAsSeen($data['id_task']);

			// Register feedback for current comment
		TodoyuCommentFeedbackManager::updateFeedbacks($idComment, $personIDsFeedback);

			// Call saved hook
		TodoyuHookManager::callHook('comment', 'comment.save', array($idComment));

		return $idComment;
	}



	/**
	 * Add comment
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addComment(array $data = array()) {
		$idComment = TodoyuRecordManager::addRecord(self::TABLE, $data);

		TodoyuHookManager::callHook('comment', 'comment.add', array($idComment));

		return $idComment;
	}



	/**
	 * Update a comment
	 *
	 * @param	Integer		$idComment
	 * @param	Array		$data
	 */
	public static function updateComment($idComment, array $data) {
		TodoyuRecordManager::updateRecord(self::TABLE, $idComment, $data);

		TodoyuHookManager::callHook('comment', 'comment.update', array($idComment, $data));
	}



	/**
	 * Delete a comment
	 *
	 * @param	Integer		$idComment
	 */
	public static function deleteComment($idComment) {
		TodoyuRecordManager::deleteRecord(self::TABLE, $idComment);

		TodoyuHookManager::callHook('comment', 'comment.delete', array($idComment));
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
		if( ! Todoyu::allowed('comment', 'comment:seeAll') ) {
			$where .= ' AND	(
							id_person_create	= ' . Todoyu::personid() . ' OR
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
	 * Get IDs of open requests for feedbacks to be given from given person
	 *
	 * @param	Integer		$idPerson
	 * @param	Boolean		$desc    	Sort ascending/descending (creation date)
	 * @return	Array
	 */
	public static function getOpenFeedbackCommentIDs($idPerson = 0, $desc = false) {
		$idPerson	= Todoyu::personid($idPerson);

		$field	= 'co.id id_comment';

		$tables	=		self::TABLE_FEEDBACK			. ' fe'
				. ',' . self::TABLE						. ' co '
				. ',' . TodoyuProjectTaskManager::TABLE . ' task';

		$where	= '		fe.id_person_feedback	= ' . $idPerson
				. ' AND fe.is_seen				= 0 '
				. ' AND co.id					= fe.id_comment '
				. ' AND co.id_task				> 0'
				. ' AND co.deleted				= 0'
				. ' AND task.id					= co.id_task'
				. ' AND task.deleted			= 0';

		$order	= 'co.date_create ' . ( $desc ? 'DESC' : 'ASC' );

		return array_keys(Todoyu::db()->getArray($field, $tables, $where, '', $order, '', 'id_comment'));
	}



	/**
	 * Change comments public flag
	 *
	 * @param	Integer		$idComment
	 * @param	Boolean		$public
	 */
	public static function setPublic($idComment, $public = true) {
		$idComment	= intval($idComment);
		$data		= array(
			'is_public' => ($public ? 1 : 0)
		);

		self::updateComment($idComment, $data);
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
		$idPerson	= Todoyu::personid($idPerson);

		$fields	= 'id';
		$table	= self::TABLE;
		$where	= 'id = ' . $idComment . ' AND id_person_create = ' . $idPerson;

		return Todoyu::db()->hasResult($fields, $table, $where);
	}



	/**
	 * Get details of persons which could receive a comment email
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getEmailReceiverIDs($idTask) {
		$idTask		= intval($idTask);
		$idProject	= TodoyuProjectTaskManager::getProjectID($idTask);

		$taskPersonIDs		= TodoyuProjectTaskManager::getTaskPersonIDs($idTask);
		$projectPersonIDs	= TodoyuProjectProjectManager::getProjectPersonIDs($idProject);

		$personIDs	= array();

			// Add task Persons
		foreach($taskPersonIDs as $idPerson) {
			$person	= TodoyuContactPersonManager::getPerson($idPerson);
			$email	= $person->getEmail(true);

			if( $email !== false ) {
				$personIDs[] = $idPerson;
			}
		}

			// Add project Persons
		foreach($projectPersonIDs as $idPerson) {
			$person	= TodoyuContactPersonManager::getPerson($idPerson);
			$email	= $person->getEmail(true);

			if( $email !== false ) {
				$personIDs[] = $idPerson;
			}
		}

			// Add internal Persons
		if( Todoyu::allowed('contact', 'person:seeAllInternalPersons') ) {
			$internalPersonIDs= TodoyuContactPersonManager::getInternalPersonIDs();

			foreach($internalPersonIDs as $idPerson) {
				$person	= TodoyuContactPersonManager::getPerson($idPerson);
				$email	= $person->getEmail(true);

				if( $email !== false ) {
					$personIDs[] = $idPerson;
				}
			}
		}

		return array_unique($personIDs);
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
		$task	= TodoyuProjectTaskManager::getTask($idTask);
		$allowed= array();

		if( $task->isTask() && ! $task->isLocked() ) {
			$ownItems	=& Todoyu::$CONFIG['EXT']['comment']['ContextMenu']['Task'];

			if( array_key_exists('add', $items) ) {
				$allowed['add']['submenu']['add-comment'] = $ownItems['add']['submenu']['add-comment'];
			}
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
		$taskFilter	= new TodoyuProjectTaskFilter($conditions);

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
		$tables	= 		self::TABLE_FEEDBACK . ' fb'
				. ',' . self::TABLE . ' co';
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



	/**
	 * Link comment IDs in given text
	 *
	 * @param	String		$text
	 * @return	String
	 */
	public static function linkCommentIDsInText($text) {
		if( Todoyu::allowed('project', 'general:area') ) {
			$pattern	= '/(<p>|<span>|\s|^)c(\d+)(<\/p>|<\/span>|\s|$)/';
			$replace	= '$1<a href="javascript:void(0)" onclick="Todoyu.Ext.comment.goToCommentInTaskByCommentNumber(\'$2\')">c$2</a>$3';

			$text	= preg_replace($pattern, $replace, $text);
		}

		return $text;
	}
}

?>