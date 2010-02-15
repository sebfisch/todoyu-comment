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
 * Manage task comments
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentManager {

	/**
	 * Default table
	 *
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

		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idComment);
		$data	= self::saveCommentForeignRecords($data, $idComment);


			// Extract feedback and email data
		$sendAsEmail	= intval($data['sendasemail']) === 1;
		$usersEmail		= array_unique(TodoyuArray::intExplode(',', $data['emailreceivers'], true, true));
		$usersFeedback	= array_unique(TodoyuArray::intExplode(',', $data['feedback'], true, true));

			// Remove special handled fields
		unset($data['sendasemail']);
		unset($data['emailreceivers']);
		unset($data['feedback']);

			// Update comment in database
		self::updateComment($idComment, $data);

			// Set all comments in task as seend
		TodoyuCommentFeedbackManager::setTaskCommentsAsSeen($data['id_task']);

			// Register feedback for current comment
		if( sizeof($usersFeedback) > 0 ) {
			TodoyuCommentFeedbackManager::addFeedbacks($idComment, $usersFeedback);
		}

			// Call saved hook
		TodoyuHookManager::callHook('comment', 'saved', array($idComment));

			// Send emails
		if( $sendAsEmail && sizeof($usersEmail) > 0 ) {
			TodoyuCommentMailer::sendEmails($idComment, $usersEmail);
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
		$sortDir= $desc ? 'DESC' : 'ASC';

		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'id_task = ' . $idTask . ' AND deleted = 0';
		$order	= 'date_create ' . $sortDir;

		return Todoyu::db()->getArray($fields, $table, $where, '', $order);
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

			// Limit comment it own and public if user can't see ALL comments
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
	 * Check if a user is the create of a comment
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idUser
	 * @return	Boolean
	 */
	public static function isCreator($idComment, $idUser = 0) {
		$idComment	= intval($idComment);
		$idUser		= personid($idUser);

		$fields	= 'id';
		$table	= self::TABLE;
		$where	= 'id = ' . $idComment . ' AND id_person_create = ' . $idUser;

		return Todoyu::db()->hasResult($fields, $table, $where);
	}



	/**
	 * Get users which could receive a comment email
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getEmailReceivers($idTask) {
		$idTask		= intval($idTask);
		$idProject	= TodoyuTaskManager::getProjectID($idTask);

		$taskUsers		= TodoyuTaskManager::getTaskPersons($idTask);
		$projectUsers	= TodoyuProjectManager::getProjectPersons($idProject);
		$internalUsers	= TodoyuPersonManager::getInternalPersons();

		$users = array();

			// Add task users
		foreach($taskUsers as $user) {
			if( ! empty($user['email']) ) {
				$users[$user['id']] = $user;
			}
		}

			// Add project users
		foreach($projectUsers as $user) {
			if( ! empty($user['email']) ) {
				$users[$user['id']] = $user;
			}
		}

			// Add internal users
		foreach($internalUsers as $user) {
			if( ! empty($user['email']) ) {
				$users[$user['id']] = $user;
			}
		}

		return $users;
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

		if( $task->isTask() ) {
			$ownItems	=& $GLOBALS['CONFIG']['EXT']['comment']['ContextMenu']['Task'];

			if( allowed('comment', 'task:addcomment') ) {
				$allowed['add']['submenu']['add-comment'] = $ownItems['add']['submenu']['add-comment'];
			}
		}

		return array_merge_recursive($items, $allowed);
	}



	public static function getFeedbackTaskIDs() {
		$conditions	= $GLOBALS['CONFIG']['EXT']['comment']['feedbackTabFilters'];
		$taskFilter	= new TodoyuTaskFilter($conditions);

		return $taskFilter->getTaskIDs();
	}



	/**
	 * Hook when tasks are rendered
	 * Add comment assets
	 *
	 */
	public static function onTasksRender() {
		TodoyuPage::addExtAssets('comment');
	}

}

?>