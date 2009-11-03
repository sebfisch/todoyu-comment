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

class TodoyuCommentSearch implements TodoyuSearchEngineIf {

	/**
	 * Search project in fulltext mode. Return the ID of the matching projects
	 *
	 * @param	Array		$find		Keywords which have to be in the projects
	 * @param	Array		$ignore		Keywords which must not be in the project
	 * @param	Integer		$limit
	 * @return	Array		Project IDs
	 */
	public static function searchComments(array $find, array $ignore = array(), $limit = 100) {
		$table	= 'ext_comment_comment';
		$fields	= array('comment');

		return TodoyuSearch::searchTable($table, $fields, $find, $ignore, $limit);
	}



	/**
	 * Get suggestions
	 *
	 * @param	Array	$find
	 * @param	Array	$ignore
	 * @param	Integer	$limit
	 * @return	Array
	 */
	public static function getSuggestions(array $find, array $ignore = array(), $limit = 5) {
		$limit			= intval($limit);
		$suggestions	= array();

		$commentIDs		= self::searchComments($find, $ignore, $limit);

			// Get comment details
		if( sizeof($commentIDs) > 0 ) {
			$fields	= '	c.id,
						c.comment,
						c.date_create,
						t.id as taskid,
						t.tasknumber,
						t.id_project,
						t.title as tasktitle,
						u.lastname,
						u.firstname,
						p.title as projecttitle,
						cust.shortname as customer';
			$table	= '	ext_comment_comment c,
						ext_project_task t,
						ext_project_project p,
						ext_user_user u,
						ext_user_customer cust';
			$where	= '	c.id IN(' . implode(',', $commentIDs) . ') AND
						c.id_task 			= t.id AND
						c.id_user_create 	= u.id AND
						t.id_project		= p.id AND
						p.id_customer		= cust.id';
			$order	= '	c.date_create DESC';

			$comments = Todoyu::db()->getArray($fields, $table, $where, '', $order);

			foreach($comments as $comment) {
				$textLong	= TodoyuDiv::getSubstring(strip_tags($comment['comment']), $find[0], 50, 60);
				$textShort	= TodoyuDiv::getSubstring(strip_tags($comment['comment']), $find[0], 20, 30);
				$textShort	= str_ireplace($find[0], '<strong>' . $find[0] . '</strong>', $textShort);
				$taskTitle	= substr($comment['tasktitle'], 0, 40);

				$suggestions[] = array(
					'labelTitle'=> TodoyuTime::format($comment['date_create'], 'D2M2Y2') . ': ' . $taskTitle . ' [' . $comment['id_project'] . '.' . $comment['tasknumber'] . ']',
					'labelInfo'	=> $textShort,
					'title'		=> $comment['firstname'] . ' ' . $comment['lastname'] . ', ' . $comment['customer'] . ': ' . $comment['projecttitle'] . ' # ' . $textLong,
					'onclick'	=> 'location.href=\'?ext=project&amp;project=' . $comment['id_project'] . '&amp;task=' . $comment['taskid'] . '&amp;tab=comment#task-comment-' . $comment['id'] . '\'');
			}
		}

		return $suggestions;
	}



	/**
	 * Get results
	 * @todo 	implement
	 *
	 * @param	Array	$find
	 * @param	Array	$ignore
	 * @param	Integer	$limit
	 */
	public static function getResults(array $find, array $ignore = array(), $limit = 20) {

	}

}

?>