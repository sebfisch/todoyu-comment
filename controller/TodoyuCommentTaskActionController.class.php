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

		TodoyuDebug::printInFirebug('test');

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
	}

}

?>