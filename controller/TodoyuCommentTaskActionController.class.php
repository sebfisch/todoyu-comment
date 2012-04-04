<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * Controller for task comments
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentTaskActionController extends TodoyuActionController {

	/**
	 * Initialize controller, check use right
	 *
	 * @param	Array		$params
	 */
	public function init(array $params) {
		Todoyu::restrict('comment', 'general:use');
	}



	/**
	 * Get IDs of project and task of comment number
	 *
	 * @param	Array		$params
	 * @return	Integer
	 */
	public function getcommentprojecttaskidsAction(array $params) {
		$idComment	= trim($params['commentnumber']);

		TodoyuCommentRights::restrictSee($idComment);

			// Get IDs of task containing the comment and its project
		$idTask		= TodoyuCommentCommentManager::getTaskID($idComment);
		$idProject	= TodoyuProjectTaskManager::getProjectID($idTask);

			// Send IDs as todoyu header
		TodoyuHeader::sendTodoyuHeader('project', $idProject);
		TodoyuHeader::sendTodoyuHeader('task', $idTask);
	}



	/**
	 * Get task ID to comment number
	 *
	 * @param	Array		$params
	 * @return	Integer
	 */
	public function getcommenttaskidAction(array $params) {
		$idComment	= trim($params['commentnumber']);

		TodoyuCommentRights::restrictSee($idComment);

		return TodoyuCommentCommentManager::getTaskID($idComment);
	}



	/**
	 * Get comment list for tasktab
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function listAction(array $params) {
		$idTask	= intval($params['task']);
		$desc	= intval($params['desc']) === 1;

		TodoyuProjectTaskRights::restrictSee($idTask);

		return TodoyuCommentRenderer::renderCommentList($idTask, $desc);
	}



	/**
	 * Toggle visibility of a comment
	 *
	 * @param	Array		$params
	 */
	public function togglepublicAction(array $params) {
		$idComment	= intval($params['comment']);

		Todoyu::restrict('comment', 'comment:makePublic');
		TodoyuCommentRights::restrictSee($idComment);

		TodoyuCommentCommentManager::togglePublic($idComment);
		TodoyuCache::flush();

		$publicFeedbackWarning	= TodoyuCommentCommentManager::getComment($idComment)->getPublicFeedbackWarning();
		if( $publicFeedbackWarning !== false ) {
			TodoyuHeader::sendTodoyuHeader('publicFeedbackWarning', $publicFeedbackWarning);
		}
	}



	/**
	 * Mark an as feedback requested comment as seen
	 *
	 * @param	Array		$params
	 */
	public function seenAction(array $params) {
		$idComment	= intval($params['comment']);

		TodoyuCommentRights::restrictSee($idComment);

		TodoyuCommentFeedbackManager::setAsSeen($idComment);

		$numOpenFeedbacks = TodoyuCommentFeedbackManager::getOpenFeedbackCount();

		TodoyuHeader::sendTodoyuHeader('feedback', $numOpenFeedbacks);
	}

}

?>