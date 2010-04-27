<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
		restrict('comment', 'general:use');
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

		return TodoyuCommentRenderer::renderCommentList($idTask, $desc);
	}



	/**
	 * Toggle visibility of a comment
	 *
	 * @param	Array		$params
	 */
	public function togglepublicAction(array $params) {
		restrict('comment', 'comment:makePublic');

		$idComment	= intval($params['comment']);

		TodoyuCommentManager::togglePublic($idComment);
	}



	/**
	 * Mark an as feedback requested comment as seen
	 *
	 * @param	Array		$params
	 */
	public function seenAction(array $params) {
		$idComment	= intval($params['comment']);

		TodoyuCommentFeedbackManager::setAsSeen($idComment);

		$numOpenFeedbacks = sizeof(TodoyuCommentFeedbackManager::getCommentIDs());

		TodoyuHeader::sendTodoyuHeader('feedback', $numOpenFeedbacks);
	}

}

?>