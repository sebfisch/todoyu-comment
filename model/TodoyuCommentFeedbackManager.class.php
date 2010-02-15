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
 * Manage comment feedback requests
 *
 * @package		Todoyu
 * @subpackage	Comment
 */

class TodoyuCommentFeedbackManager {

	/**
	 * Default working table
	 *
	 */
	const TABLE = 'ext_comment_feedback';



	/**
	 * Add a new feedback request
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idFeedbackUser
	 * @return	Integer
	 */
	public static function addFeedback($idComment, $idFeedbackUser) {
		$idComment		= intval($idComment);
		$idFeedbackUser	= intval($idFeedbackUser);

		$table	= self::TABLE;
		$data	= array('date_create'		=> NOW,
						'date_update'		=> 0,
						'id_person_create'	=> personid(),
						'id_person_feedback'	=> $idFeedbackUser,
						'id_comment'		=> $idComment,
						'is_seen'			=> 0);

		return Todoyu::db()->doInsert($table, $data);
	}



	/**
	 * Add feedback requests for multiple users
	 *
	 * @param	Integer		$idComment
	 * @param	Array		$feedbackUserIDs
	 */
	public static function addFeedbacks($idComment, array $userIDs) {
		$idComment	= intval($idComment);
		$userIDs	= TodoyuArray::intval($userIDs, true, true);

		foreach($userIDs as $idUser) {
			self::addFeedback($idComment, $idUser);
		}
	}



	/**
	 * Get comment IDs which need a feedback from the user
	 *
	 * @param	Integer		$idUser
	 * @return	Array
	 */
	public static function getCommentIDs($idUser = 0) {
		$idUser	= personid($idUser);

		$field	= 'id_comment';
		$table	= self::TABLE;
		$where	= '	id_person_feedback= ' . $idUser . ' AND
					is_seen			= 0';
		$order	= 'date_create';

		return Todoyu::db()->getColumn($field, $table, $where, '', $order);
	}



	/**
	 * Get task IDs which have comments which need a feedback from the user
	 *
	 * @param	Integer		$idUser
	 * @return	Array
	 */
	public static function getTaskIDs($idUser = 0) {
		$idUser	= personid($idUser);

		$field	= '	c.id_task';
		$table	= 	self::TABLE . ' f,
					ext_comment_comment c';
		$where	= '	f.id_comment		= c.id AND
					f.id_person_feedback	= ' . $idUser . ' AND
					f.is_seen			= 0';
		$order	= '	f.date_create';

		return Todoyu::db()->getColumn($field, $table, $where, '', $order);
	}



	/**
	 * Check if a comment has a feedback request
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idUser
	 * @return	Boolean
	 */
	public static function hasFeedbackRequest($idComment, $idUser = 0) {
		$idComment	= intval($idComment);
		$idUser		= personid($idUser);

		$field	= 'id';
		$table	= self::TABLE;
		$where	= '	id_comment		= ' . $idComment . ' AND
					id_person_feedback= ' . $idUser . ' AND
					is_seen			= 0';

		return Todoyu::db()->hasResult($field, $table, $where);
	}



	/**
	 * Check if a comment has a feedback request
	 *
	 * @param	Integer		$idComment
	 * @param	Boolean		$onlyUnseen
	 * @return	Array
	 */
	public static function getFeedbackRequests($idComment, $onlyUnseen = false) {
		$idComment	= intval($idComment);

		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'id_comment = ' . $idComment .
				  ($onlyUnseen === true ? ' AND is_seen = 0' : '');

		return Todoyu::db()->getArray($fields, $table, $where);
	}



	/**
	 * Set a feedback request as seen
	 *
	 * @param	Integer		$idFeedback
	 * @return	Boolean
	 */
	public static function setAsSeen($idComment, $idUser = 0) {
		$idComment	= intval($idComment);
		$idUser		= personid($idUser);

		$table	= self::TABLE;
		$where	= '	id_comment = ' . $idComment. ' AND
					id_person_feedback = ' . $idUser;
		$data	= array(
			'date_update'	=> NOW,
			'is_seen' 		=> 1
		);

		return Todoyu::db()->doUpdate($table, $where, $data) === 1;
	}



	/**
	 * Set all comments in a task as seen by an user
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idUser
	 * @return	Integer		Number of updated comments
	 */
	public static function setTaskCommentsAsSeen($idTask, $idUser = 0) {
		$idTask	= intval($idTask);
		$idUser	= personid($idUser);

		$tables	= 	self::TABLE . ' f,
					ext_comment_comment c';
		$where	= '	f.id_comment 		= c.id AND
					f.id_person_feedback	= ' . $idUser . ' AND
					c.id_task			= ' . $idTask ;
		$data	= array(
			'f.is_seen'		=> 1,
			'f.date_update'	=> NOW
		);

		return Todoyu::db()->doUpdate($tables, $where, $data);
	}



	/**
	 * Get users whom feedback to given comment is requested from
	 *
	 * @param	Interger	$idComment
	 * @return	Array
	 */
	public static function getFeedbackUsers($idComment) {
		$idComment	= intval($idComment);

		$fields	= '	u.id,
					u.username,
					u.email,
					u.firstname,
					u.lastname,
					f.is_seen';
		$tables	= '	ext_contact_person u,
					ext_comment_feedback f';
		$where	= '	f.id_comment 		= ' . $idComment . ' AND
					f.id_person_feedback	= u.id AND
					u.deleted			= 0 AND
					u.active			= 1';
		$group	= '	u.id';
		$order	= '	u.lastname,
					u.firstname';

		return Todoyu::db()->getArray($fields, $tables, $where, $group, $order);
	}



	/**
	 * Check if the comment has a feedback request which is not "seen" yet
	 *
	 * @param	Integer		$idComment
	 * @return	Boolean		Open feedback request found
	 */
	public static function isCommentUnapproved($idComment)	{
		$idComment	= intval($idComment);
		$idUser		= personid();

		$field	= 'is_seen';
		$table	= self::TABLE;
		$where	= 'id_comment		= ' . $idComment . ' AND
				   id_person_feedback	= ' . $idUser;

		$isSeen =  Todoyu::db()->getColumn($field, $table, $where);

		return sizeof($isSeen) !== 0 && intval($isSeen[0]) === 0;
	}
}

?>