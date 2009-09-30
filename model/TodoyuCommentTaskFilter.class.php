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
 * Filters for tasks for comment stuff
 *
 * @package		Todoyu
 * @subpackage	Comment
 */

class TodoyuCommentTaskFilter {

	/**
	 * Filter for tasks which have unseen comments (with feedback request) for the current user
	 *
	 * @param	String		$value			Always null because of widget type, ignore
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_unseenFeedbackCurrentUser($value, $negate = false) {
		$idUser	= TodoyuAuth::getUserID();

		return self::Filter_unseenFeedbackUser($idUser, $negate);
	}



	/**
	 * Filter for tasks which have unseen comments (with feedback request) for a user
	 *
	 * @param	Integer		$value		ID user
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_unseenFeedbackUser($value, $negate = false) {
		$queryParts	= false;
		$idUser		= intval($value);
		$seenStatus	= $negate ? 1 : 0 ;

		$tables	= array('ext_comment_comment', 'ext_comment_feedback', 'ext_project_task');
		$where	= '	ext_comment_feedback.id_comment 		= ext_comment_comment.id AND
					ext_comment_comment.id_task 			= ext_project_task.id AND
					ext_comment_feedback.is_seen			= ' . $seenStatus . ' AND
					ext_comment_feedback.id_user_feedback	= ' . $idUser;

		$queryParts	= array(
			'tables'=> $tables,
			'where'	=> $where
		);

		return $queryParts;
	}



	/**
	 * Filter for tasks which have comments with the keyword in it
	 *
	 * @param	String		$value		Keyword to search for
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_fulltext($value, $negate = false) {
		$queryParts	= false;
		$keyword	= trim($value);

		if( $keyword !== '' ) {
			$keywords	= explode(' ', $keyword);
			$fields		= array('ext_comment_comment.comment');
			$negator	= $negate ? 'NOT ' : '';

			$tables	= array('ext_comment_comment', 'ext_comment_feedback', 'ext_project_task');
			$where	= '	ext_comment_feedback.id_comment 		= ext_comment_comment.id AND
						ext_comment_comment.id_task 			= ext_project_task.id AND ' . $negator .
						Todoyu::db()->buildLikeQuery($keywords, $fields);

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}
}


?>