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
 * Manage comment mail DB logs
 *
 * @package		Todoyu
 * @subpackage	Comment
 */

class TodoyuCommentMailManager {

	/**
	 * Default working table
	 */
	const TABLE = 'ext_comment_mailed';



	/**
	 * Save log record about comment having been mailed when to which user
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idUserMailed
	 * @return	Integer		New record ID
	 */
	public static function saveMailSent($idComment, $idUserMailed) {
		$idComment		= intval($idComment);
		$idUserMailed	= intval($idUserMailed);

		$data	= array(
			'date_create'		=> NOW,
			'id_comment'		=> $idComment,
			'id_user_create'	=> userid(),
			'id_user_mailed'	=> $idUserMailed,
		);

		return Todoyu::db()->addRecord(self::TABLE, $data);
	}



	/**
	 * Get all mail sent log entries
	 *
	 * @param	Integer	$idComment
	 * @param	Integer	$idUserCreate
	 * @param	Integer	$idUserMailed
	 * @return	Array
	 */
	public static function getAllSent($idComment, $idUserCreate = 0, $idUserMailed = 0) {
		$idComment		= intval($idComment);
		$idUserCreate	= intval($idUserCreate);
		$idUserMailed	= intval($idUserMailed);

		$fields	= '*';
		$where	= 'id_comment = ' . $idComment .
				 ($idUserCreate !== 0 ? ' AND id_user_create = ' . $idUserCreate : '') .
				 ($idUserMailed !== 0 ? ' AND id_user_mailed IN (' . $idUserMailed . ')' : '');
		$groupBy	= 'id';
		$orderBy	= 'date_create';

		return Todoyu::db()->getArray($fields, self::TABLE, $where, $groupBy, $orderBy);
	}

}

?>