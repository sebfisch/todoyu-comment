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

	public static function Filter_unseenFeedbackCurrentUser($idUser, $negate = false) {
		return self::Filter_unseenFeedbackUser(userid(), $negate);
	}



	/**
	 * Filter for tasks which have unseen comments (with feedback request) for a user
	 *
	 * @param	Integer		$value		ID user
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_unseenFeedbackUser($idUser, $negate = false) {
		$queryParts	= false;
		$idUser		= intval($idUser);
		$seenStatus	= $negate ? 1 : 0 ;

		if( $idUser !== 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_comment_comment',
				'ext_comment_feedback'
			);
			$where	= '	ext_comment_comment.id_task 			= ext_project_task.id AND
						ext_comment_feedback.id_comment 		= ext_comment_comment.id AND
						ext_comment_feedback.is_seen			= ' . $seenStatus . ' AND
						ext_comment_feedback.id_user_feedback	= ' . $idUser;

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: Tasks which have comment feedbacks which have not been seen by a member of a group
	 *
	 * @param	String		$groupIDs
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_unseenFeedbackGroups($groupIDs, $negate = false) {
		$queryParts	= false;
		$groupIDs	= TodoyuArray::intExplode(',', $groupIDs, true, true);

		if( sizeof($groupIDs) > 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_comment_comment',
				'ext_comment_feedback',
				'ext_contact_mm_person_role'
			);
			$where	= '	ext_comment_comment.id_task 			= ext_project_task.id AND
						ext_comment_feedback.id_comment 		= ext_comment_comment.id AND
						ext_comment_feedback.id_user_feedback	= ext_contact_mm_person_role.id_user AND
						ext_contact_mm_person_role.id_group IN(' . implode(',', $groupIDs) . ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: Tasks which have comments which contain the given text
	 *
	 * @param	String		$value		Keyword to search for
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_fulltext($keyword, $negate = false) {
		$queryParts	= false;
		$keyword	= trim($keyword);

		if( $keyword !== '' ) {
			$tables	= array(
				'ext_project_task',
				'ext_comment_comment'
			);

			$keywords	= explode(' ', $keyword);
			$fields		= array(
				'ext_comment_comment.comment'
			);
			$negator	= $negate ? 'NOT ' : '';


			$where	= '	(ext_comment_comment.id_task = ext_project_task.id AND
						' . $negator . Todoyu::db()->buildLikeQuery($keywords, $fields) . ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: Tasks which are written by an user
	 *
	 * @param	Integer		$idUser
	 * @param	Bool		$negate
	 * @return	Array		Or FALSE
	 */
	public static function Filter_commentWrittenUser($idUser, $negate = false) {
		$queryParts	= false;
		$idUser		= intval($idUser);

		if( $idUser !== 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_comment_comment'
			);
			$where	= '	ext_comment_comment.id_task 		= ext_project_task.id AND
						ext_comment_comment.id_user_create 	= ' . $idUser;

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: Tasks which have comments which are written by a member of one of the groups
	 *
	 * @param	String		$groupIDs
	 * @param	Bool		$negate
	 * @return	Array		Or FALSE
	 */
	public static function Filter_commentWrittenGroups($groupIDs, $negate = false) {
		$queryParts	= false;
		$groupIDs	= TodoyuArray::intExplode(',', $groupIDs, true, true);

		if( sizeof($groupIDs) > 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_comment_comment',
				'ext_contact_mm_person_role'
			);
			$where	= '	ext_comment_comment.id_task 		= ext_project_task.id AND
						ext_comment_comment.id_user_create 	= ext_contact_mm_person_role.id_user AND
						ext_contact_mm_person_role.id_group IN(' . implode(',', $groupIDs) . ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}

}


?>