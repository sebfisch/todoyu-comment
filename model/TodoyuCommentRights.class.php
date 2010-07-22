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
 * Comment rights functions
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentRights {

	/**
	 * Deny access
	 * Shortcut for comment
	 *
	 * @param	String		$right		Denied right
	 */
	private static function deny($right) {
		TodoyuRightsManager::deny('comment', $right);
	}



	/**
	 * Check whether person is allowed to edit a comment
	 * Check whether person has editing rights and whether person can edit a status
	 *
	 * @param	Integer		$idComment
	 * @return	Boolean
	 */
	public static function isEditAllowed($idComment) {
		$idComment	= intval($idComment);
		$comment	= TodoyuCommentManager::getComment($idComment);
		$idTask		= $comment->getTaskID();

		return self::isEditInTaskAllowed($idTask);
	}



	/**
	 * Check whether person can edit comments in this task
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isEditInTaskAllowed($idTask) {
		$idTask	= intval($idTask);

		if( TodoyuTaskRights::isSeeAllowed($idTask) ) {
			if( allowed('comment', 'comment:editAll')) {
				return true;
			} else {
				$task	= TodoyuTaskManager::getTask($idTask);

				return $task->isEditable();
			}
		}

		return false;
	}



	/**
	 * Check whether adding comments in given task is allowed
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isAddInTaskAllowed($idTask) {
		$idTask	= intval($idTask);

		return TodoyuTaskRights::isSeeAllowed($idTask) && allowed('comment', 'general:use');
	}



	/**
	 * Check whether a person can see the comment
	 *
	 * @param	Integer		$idComment
	 * @return	Boolean
	 */
	public static function isSeeAllowed($idComment) {
		$idComment	= intval($idComment);
		$comment	= TodoyuCommentManager::getComment($idComment);
		$idTask		= $comment->getTaskID();

		if( TodoyuTaskRights::isSeeAllowed($idTask) ) {
			if( allowed('comment', 'comment:seeAll')) {
				return true;
			} else {
				$idPerson	= personid();
				return $comment->isCurrentPersonCreator() || in_array( $idPerson, $comment->getFeedbackPersons() );
			}
		}

		return false;
	}



	/**
	 * Restrict access to persons which are allowed to add comments in this task
	 *
	 * @todo	state deny message more precisely
	 * @param	Integer		$idComment
	 */
	public static function restrictEdit($idComment) {
		if( ! self::isEditAllowed($idComment) ) {
			self::deny('comment:edit');
		}
	}



	/**
	 * Restrict access to persons who are allowed to edit comments in
	 *
	 * @todo	state deny message more precisely
	 * @param	Integer		$idComment
	 */
	public static function restrictEditInTask($idTask) {
		if( ! self::isEditInTaskAllowed($idTask) ) {
			self::deny('comment:edit');
		}
	}



	/**
	 * Restrict access to persons who are allowed to add comments in this task
	 *
	 * @todo	state deny message more precisely
	 * @param	Integer		$idParentTask
	 */
	public static function restrictAddInTask($idTask) {
		$idTask	= intval($idTask);

		if( ! self::isAddAllowed($idTask) ) {
			self::deny('comment:add');
		}
	}



	/**
	 * Restrict access to person which are allowed to see the comment
	 *
	 * @todo	state deny message more precisely
	 * @param	Integer		$idComment
	 */
	public static function restrictSee($idComment) {
		$idComment	= intval($idComment);

		if( ! self::isSeeAllowed($idComment) ) {
			self::deny('comment:see');
		}
	}

}
?>