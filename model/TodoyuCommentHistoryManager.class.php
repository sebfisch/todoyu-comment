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
 * Manage comment history
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentHistoryManager {

	/**
	 * Get comment IDs which need a feedback from the person
	 *
	 * @param	Integer		$idComment
	 * @return	Array
	 */
	public static function getHistory($idComment = 0) {
		$idComment	= intval($idComment);

		$commentData	= TodoyuCommentManager::getCommentData($idComment);
		$personCreate	= TodoyuPersonManager::getLabel($commentData['id_person_create'], false);

		$feedbackRequests	= TodoyuCommentFeedbackManager::getFeedbackRequests($idComment, false);
		$mailsSend			= TodoyuCommentMailManager::getAllSent($idComment);

		$log	= self::mergeAndSortLogEntries($feedbackRequests, $mailsSend);

		$history	= array(
			'id'			=> $idComment,
			'id_task'		=> $commentData['id_task'],
			'date_create'	=> $commentData['date_create'],
			'person_create'	=> $personCreate,
			'log'			=> $log,
		);

		return $history;
	}



	/**
	 * Merge and sort (by creation timestamp) log record entries of two tables
	 *
	 * @param 	Array	$log1
	 * @param	Array	$log2
	 * @param	String	$table1
	 * @param	String	$table2
	 * @return	Array
	 */
	private static function mergeAndSortLogEntries($log1, $log2, $table1 = 'ext_comment_feedback', $table2 = 'ext_comment_mailed') {
		if ( count($log1) == 0 && count($log2) == 0 ) {
				// Both logs empty
			$log	= array();
		} elseif ( count($log1) > 0 && count($log2) == 0 ) {
				// Only log1 contains entries
			$log	= $log1;
		} elseif ( count($log1) == 0 && count($log2) > 0 ) {
				// Only log2 contains entries
			$log	= $log2;
		} elseif ( count($log1) > 0 && count($log2) > 0 ) {
				// Both logs contain entries
			$log	= array();
				// Merge the two logs
			foreach($log1 as $data) {
				$data['table']	= $table1;
				$log[ $data['date_create'] ][]	= $data;
			}
			foreach($log2 as $num	=> $data) {
				$data['table']	= $table2;
				$log[ $data['date_create'] ][]	= $data;
			}

			ksort($log);
		}

		return $log;
	}

}

?>