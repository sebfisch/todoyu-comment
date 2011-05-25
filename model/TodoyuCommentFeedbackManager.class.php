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
 * Manage comment feedback requests
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentFeedbackManager {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE = 'ext_comment_mm_comment_feedback';


	/**
	 * Save feedback requests of given comment
	 *
	 * @param	Integer		$idComment
	 * @param	Array		$feedbackPersonIDs
	 */
	public static function updateFeedbacks($idComment, $feedbackPersonIDs = array()) {
			// Get already stored unseen feedbacks
		$seenFeedbackPersonIDs	= self::getSeenFeedbacksPersonIDs($idComment);

			// Remove feedbacks that have been seen already from list, those don't need to be removed/resaved
		$feedbackPersonIDs	= array_diff($feedbackPersonIDs, $seenFeedbackPersonIDs);

			// Remove old open feedbacks from DB
		self::removeUnseenFeedbacks($idComment);

			// Add newly added feedback requests
		self::addFeedbacks($idComment, $feedbackPersonIDs);
	}



	/**
	 * Get IDs of persons from whom a feedback is requested and who saw it already
	 *
	 * @param	Integer		$idComment
	 * @return	Array
	 */
	public static function getSeenFeedbacksPersonIDs($idComment) {
		$idComment	= intval($idComment);

		$requests	= self::getSeenFeedbackRequests($idComment);

		return self::extractPersonIDsFromFeedbacks($requests);
	}



	/**
	 * Get IDs of persons from whom a feedback is requested (and not yet seen)
	 *
	 * @param	Integer		$idComment
	 * @return	Array
	 */
	public static function getUnseenFeedbacksPersonIDs($idComment) {
		$idComment	= intval($idComment);
		$requests	= self::getFeedbackRequests($idComment, true);

		return self::extractPersonIDsFromFeedbacks($requests);
	}



	/**
	 * Extract person IDs from given feedbacks
	 *
	 * @param	Array	$requests
	 * @return	Array
	 */
	public static function extractPersonIDsFromFeedbacks(array $requests = array()) {
		$requests	= TodoyuArray::sortByLabel($requests, 'id_person_feedback');

		$personIDs	= array();
		foreach($requests as $request) {
			$personIDs[]	= $request['id_person_feedback'];
		}

		return array_unique($personIDs);
	}



	/**
	 * Remove all unseen feedback request from given comment
	 *
	 * @param	Integer		$idComment
	 * @return	Integer		Num affected rows
	 */
	public static function removeUnseenFeedbacks($idComment) {
		$where	=
				'		id_comment	= ' . $idComment
			.	' AND	is_seen		= 0'
		;

		return Todoyu::db()->doDelete(self::TABLE, $where);
	}



	/**
	 * Add a new feedback request
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idFeedbackPerson
	 * @return	Integer
	 */
	public static function addFeedback($idComment, $idFeedbackPerson) {
		$idComment			= intval($idComment);
		$idFeedbackPerson	= intval($idFeedbackPerson);

		$data	= array(
			'id_person_feedback'=> $idFeedbackPerson,
			'id_comment'		=> $idComment,
			'is_seen'			=> 0
		);

		return TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Add feedback requests for multiple persons
	 *
	 * @param	Integer		$idComment
	 * @param	Array		$personIDs
	 */
	public static function addFeedbacks($idComment, array $personIDs) {
		$idComment	= intval($idComment);
		$personIDs	= TodoyuArray::intval($personIDs, true, true);

		foreach($personIDs as $idPerson) {
			self::addFeedback($idComment, $idPerson);
		}
	}



	/**
	 * Get comment IDs which need a feedback from the person
	 *
	 * @param	Integer		$idPerson
	 * @return	Array
	 */
	public static function getCommentIDs($idPerson = 0) {
		$idPerson	= Todoyu::personid($idPerson);

		$field	= 'f.id_comment';
		$table	= self::TABLE . ' as f,
					ext_comment_comment as c';
		$where	= '	f.id_person_feedback	= ' . $idPerson
				. ' AND	f.is_seen				= 0
					AND c.id = f.id_comment
					AND c.deleted = 0
					AND c.id_task != 0';
		$order	= 'f.date_create';

		return Todoyu::db()->getColumn($field, $table, $where, '', $order);
	}



	/**
	 * Get task IDs which have comments which need a feedback from the person
	 *
	 * @param	Integer		$idPerson
	 * @return	Array
	 */
	public static function getTaskIDs($idPerson = 0) {
		$idPerson	= Todoyu::personid($idPerson);

		$field	= '	c.id_task';
		$table	= 	self::TABLE . ' f,
					ext_comment_comment c';
		$where	= '		f.id_comment		= c.id'
				. '	AND	f.id_person_feedback= ' . $idPerson
				. ' AND	f.is_seen			= 0';
		$order	= '	f.date_create';

		return Todoyu::db()->getColumn($field, $table, $where, '', $order);
	}



	/**
	 * Check whether a comment has a feedback request
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function hasFeedbackRequest($idComment, $idPerson = 0) {
		$idComment	= intval($idComment);
		$idPerson	= Todoyu::personid($idPerson);

		$field	= self::TABLE . '.id';
		$table	= self::TABLE . ' as f,
					ext_comment_comment as c';
		$where	= '		f.id_comment			= ' . $idComment
				. ' AND	f.id_person_feedback	= ' . $idPerson
				. ' AND	f.is_seen				= 0
					AND c.id = f.id_comment
					AND c.deleted = 0
					AND c.id_task != 0';

		return Todoyu::db()->hasResult($field, $table, $where);
	}



	/**
	 * Get feedback requests, optionally only not yet seen ones
	 *
	 * @param	Integer		$idComment
	 * @param	Boolean		$onlyUnseen
	 * @return	Array
	 */
	public static function getFeedbackRequests($idComment, $onlyUnseen = false) {
		$idComment	= intval($idComment);

		$where	= 'id_comment = ' . $idComment . ($onlyUnseen === true ? ' AND is_seen = 0' : '');

		return TodoyuRecordManager::getAllRecords(self::TABLE, $where, '');
	}



	/**
	 * Get all seen feedback requests
	 *
	 * @param	Integer		$idComment
	 * @return	Array
	 */
	public static function getSeenFeedbackRequests($idComment) {
		$idComment	= intval($idComment);

		$where	= 'id_comment = ' . $idComment . ' AND is_seen = 1';

		return TodoyuRecordManager::getAllRecords(self::TABLE, $where, '');
	}



	/**
	 * Set a feedback request as seen
	 *
	 * @param	Integer		$idFeedback
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function setAsSeen($idComment, $idPerson = 0) {
		$idComment	= intval($idComment);
		$idPerson	= Todoyu::personid($idPerson);

		$table	= self::TABLE;
		$where	= '		id_comment 			= ' . $idComment
				. ' AND	id_person_feedback 	= ' . $idPerson;
		$data	= array(
			'date_update'	=> NOW,
			'is_seen' 		=> 1
		);

		return Todoyu::db()->doUpdate($table, $where, $data) === 1;
	}



	/**
	 * Set all comments in a task as seen by a person
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idPerson
	 * @return	Integer		Number of updated comments
	 */
	public static function setTaskCommentsAsSeen($idTask, $idPerson = 0) {
		$idTask	= intval($idTask);
		$idPerson	= Todoyu::personid($idPerson);

		$tables	= 	self::TABLE . ' f,
					ext_comment_comment c';
		$where	= '		f.id_comment 		= c.id'
				. '	AND	f.id_person_feedback= ' . $idPerson
				. ' AND	c.id_task			= ' . $idTask ;
		$data	= array(
			'f.is_seen'		=> 1,
			'f.date_update'	=> NOW
		);

		return Todoyu::db()->doUpdate($tables, $where, $data);
	}



	/**
	 * Get persons whom feedback to given comment is requested from
	 *
	 * @param	Integer	$idComment
	 * @return	Array
	 */
	public static function getFeedbackPersons($idComment) {
		$idComment	= intval($idComment);

		$fields	= '	p.id,
					p.username,
					p.email,
					p.firstname,
					p.lastname,
					f.is_seen';
		$tables	= '	ext_contact_person				p,
					ext_comment_mm_comment_feedback	f';
		$where	= '		f.id_comment 		= ' . $idComment .
				  ' AND	f.id_person_feedback= p.id
					AND	p.deleted			= 0';
		$group	= '	p.id';
		$order	= '	p.lastname,
					p.firstname';
		$indexField	= 'id';

		return Todoyu::db()->getArray($fields, $tables, $where, $group, $order, '', $indexField);
	}



	/**
	 * Check whether the comment has a feedback request which is not "seen" yet
	 *
	 * @param	Integer		$idComment
	 * @return	Boolean		Open feedback request found
	 */
	public static function isCommentUnapproved($idComment) {
		$idComment	= intval($idComment);
		$idPerson		= Todoyu::personid();

		$field	= 'is_seen';
		$table	= self::TABLE;
		$where	= '		id_comment			= ' . $idComment
				. ' AND	id_person_feedback	= ' . $idPerson;

		$isSeen =  Todoyu::db()->getColumn($field, $table, $where);

		return sizeof($isSeen) !== 0 && intval($isSeen[0]) === 0;
	}
}

?>