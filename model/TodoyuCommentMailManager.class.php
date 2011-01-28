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
 * Manage comment mail DB logs
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentMailManager {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE = 'ext_comment_mm_comment_personemail';



	/**
	 * Save log record about persons the given mail has been sent to
	 *
	 * @param	Integer		$idComment
	 * @param	Array		$personIDs			Persons the comment has been sent to
	 */
	public static function saveMailsSent($idComment, array $personIDs = array() ) {
		$idComment		= intval($idComment);
		$personIDs		= TodoyuArray::intval($personIDs);

		foreach($personIDs as $idPerson) {
			self::addMailSent($idComment, $idPerson);
		}
	}



	/**
	 * log sent comment email of given comment to given person
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idPerson
	 */
	public static function addMailSent($idComment, $idPerson) {
		$idComment	= intval($idComment);
		$idPerson	= intval($idPerson);

		$data	= array(
			'id_person_create'	=> personid(),
			'date_create'		=> NOW,
			'id_comment'		=> $idComment,
			'id_person_email'	=> $idPerson,
		);

		TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Get persons the given comment has been sent to by email
	 *
	 * @param	Integer		$idComment
	 * @return	Array
	 */
	public static function getEmailPersons($idComment) {
		$idComment	= intval($idComment);

		$fields	= '	p.id,
					p.username,
					p.email,
					p.firstname,
					p.lastname,
					e.date_create';
		$tables	= '	ext_contact_person p,
					ext_comment_mm_comment_personemail e';
		$where	= '		e.id_comment 		= ' . $idComment .
				  ' AND	e.id_person_email	= p.id
					AND	p.deleted			= 0';
		$group	= '	p.id';
		$order	= '	p.lastname,
					p.firstname';
		$indexField	= 'id';

		return Todoyu::db()->getArray($fields, $tables, $where, $group, $order, '', $indexField);
	}

}

?>