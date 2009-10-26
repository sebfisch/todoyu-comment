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
		return TodoyuCache::getRecord('TodoyuComment', $idComment);
	}



	/**
	 * Get a comment as an array
	 *
	 * @param	Integer		$idComment
	 * @return	Array
	 */
	public static function getCommentArray($idComment) {
		return Todoyu::db()->getRecord(self::TABLE, $idComment);
	}



	/**
	 * Save a comment. If the comment already exists, update else create a new one.
	 * Automaticly adds feedback request and sends emails for the selected users
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idTask
	 * @param	String		$commentText
	 * @param	Boolean		$isPublic
	 * @param	Array		$feedbackUsers
	 * @param	Array		$emailUsers
	 * @return	Integer		Comment ID
	 */
//	public static function saveComment($idComment, $idTask, $commentText, $isPublic = false, array $feedbackUsers = array(), array $emailUsers = array()) {
	public static function saveCommentXXX($idComment, array $data) {
		$idComment		= intval($idComment);
		$idTask			= intval($data['id_task']);
		$comment		= trim($data['comment']);
		$isPublic		= intval($data['is_public']) === 1;
		$feedbackUsers	= is_array($data['feedback']) ? TodoyuArray::intval($data['feedback'], true, true) : array();
		$emailUsers		= is_array($data['emailinfo']) ? TodoyuArray::intval($data['emailinfo'], true, true) : array();

			// Add or update comment
		if( $idComment === 0 ) {
			$idComment = self::addComment($idTask, $comment, $isPublic);
		} else {
			$data = array(
				'comment'	=> $comment,
				'is_public'	=> $isPublic
			);

			self::updateComment($idComment, $data);
		}

			// Register feedbacks if requested
		if( sizeof($feedbackUsers) > 0 ) {
			TodoyuCommentFeedbackManager::addFeedbacks($idComment, $feedbackUsers);
		}

		if( sizeof($emailUsers) > 0 ) {
			TodoyuCommentMailer::sendEmails($idComment, $emailUsers);
		}

		return $idComment;
	}


	public static function saveComment(array $data) {
		$idComment	= intval($idComment);
		$xmlPath	= 'ext/comment/config/form/comment.xml';

		if( $idComment === 0 ) {
			$idComment = self::addComment();
		}

		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idComment);
		$data	= self::saveCommentForeignRecords($data, $idComment);

		self::updateComment($idComment, $data);

		return $idComment;
	}


	/**
	 * Add a new comment
	 *
	 * @param	Integer		$idTask
	 * @param	String		$comment
	 * @param	Boolean		$isPublic
	 * @return	Integer
	 */
	public static function addCommentX($idTask, $comment, $isPublic = false) {
		$idTask		= intval($idTask);
		$isPublic 	= $isPublic ? 1 : 0;

		$table	= self::TABLE;
		$data	= array(
			'date_create'		=> NOW,
			'date_update'		=> NOW,
			'deleted'			=> 0,
			'id_user_create'	=> userid(),
			'id_task'			=> $idTask,
			'comment'			=> $comment,
			'is_public' 		=> $isPublic
		);

		return Todoyu::db()->addRecord($table, $data);
	}



	/**
	 * Add comment
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addComment(array $data) {
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



	public static function saveCommentForeignRecords(array $data, $idComment) {
		TodoyuDebug::printInFirebug($data);


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
		$group	= '';
		$order	= 'date_create ' . $sortDir;
		$limit	= '';

		return Todoyu::db()->getArray($fields, $table, $where, $group, $order, $limit);
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
		$group	= '';
		$order	= 'date_create ' . $sortDir;
		$limit	= '';

		return Todoyu::db()->getColumn($fields, $table, $where, $group, $order, $limit);
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
	 * Change comment visibility for customers
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
		$idUser		= userid($idUser);

		$fields	= 'id';
		$table	= self::TABLE;
		$where	= 'id = ' . $idComment . ' AND id_user_create = ' . $idUser;

		return Todoyu::db()->hasResult($fields, $table, $where);
	}



	/**
	 * Get option array for feedback select in comment edit form
	 * The options are grouped in main groups with contain the options for
	 * the users
	 *
	 * @param	Array		$formData
	 * @return	Array
	 */
	public static function getFeedbackUsersGroupedOptions(array $formData) {
		$idTask		= intval($formData['id_task']);
		$idProject	= TodoyuTaskManager::getProjectID($idTask);
		$options	= array();

			// Task users
		$users	= TodoyuTaskManager::getTaskUsers($idTask);

		$group	= Label('comment.group.taskmembers');
		foreach($users as $user) {
			$options[$group][] = array(	'value'	=> $user['id'],
										'label'	=> $user['lastname'] . ' ' . $user['firstname']
										);
		}

			// Get project users
		$users	= TodoyuProjectManager::getProjectUsers($idProject);
		$group	= Label('comment.group.projectmembers');
		foreach($users as $user) {
			$options[$group][] = array(	'value'	=> $user['id'],
										'label'	=> $user['lastname'] . ' ' . $user['firstname']
										);
		}

			// Get staff users
		$users	= TodoyuUserManager::getInternalUsers();
		$group	= Label('comment.group.employee');
		foreach($users as $user) {
			$options[$group][] = array(	'value'	=> $user['id'],
										'label'	=> $user['lastname'] . ' ' . $user['firstname']
										);
		}

		return $options;
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

		$taskUsers		= TodoyuTaskManager::getTaskUsers($idTask);
		$projectUsers	= TodoyuProjectManager::getProjectUsers($idProject);

		$users = array();

		foreach($taskUsers as $user) {
			$users[$user['id']] = $user;
		}
		foreach($projectUsers as $user) {
			$users[$user['id']] = $user;
		}

		return $users;
	}



	/**
	 * Toggle customer visibility of comment
	 *
	 * @param	Integer	$idComment
	 * @return	Integer
	 */
	public static function toggleCustomerVisibility($idComment) {
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

			if( true ) {
				$allowed['add']['submenu']['add-comment'] = $ownItems['add']['submenu']['add-comment'];
			}
		}

		return array_merge_recursive($items, $allowed);
	}

}

?>