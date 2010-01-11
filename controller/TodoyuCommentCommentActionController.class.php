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
 * Comment action controller
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentCommentActionController extends TodoyuActionController {

	/**
	 * Load edit view of the comment
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function editAction(array $params) {
		$idTask		= intval($params['task']);
		$idComment	= intval($params['comment']);

		if( $idComment === 0 ) {
			restrict('comment', 'task:add');
		} else {
			$comment	= TodoyuCommentManager::getComment($idComment);
				// User is the creator or has right editAll
			if( ! $comment->isCurrentUserCreator() ) {
				restrict('comment', 'task:editAll');
			}
		}

		return TodoyuCommentRenderer::renderEdit($idTask, $idComment);
	}



	/**
	 * Delete a comment
	 *
	 * @param	Array		$params
	 */
	public function deleteAction(array $params) {
		$idComment	= intval($params['comment']);
		$comment	= TodoyuCommentManager::getComment($idComment);

			// User is the creator or has right deleteAll
		if( ! $comment->isCurrentUserCreator() ) {
			restrict('comment', 'task:deleteAll');
		}

		TodoyuCommentManager::deleteComment($idComment);

		$idTask	= $comment->getTaskID();

		TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
		TodoyuHeader::sendTodoyuHeader('tabLabel', TodoyuCommentTask::getLabel($idTask));
	}



	/**
	 * Show comment feedbacks and mailing history log
	 *
	 * @param	Array		$params
	 */
	public function logAction(array $params) {
		$idTask		= intval($params['task']);
		$idComment	= intval($params['comment']);

		$comment	= TodoyuCommentManager::getComment($idComment);
			// User is the creator or has right editAll
		if( ! $comment->isCurrentUserCreator() ) {
			restrict('comment', 'task:viewHistory');
		}

		return TodoyuCommentRenderer::renderLog($idTask, $idComment);
	}



	/**
	 * Save (update) comment
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function saveAction(array $params) {
		$xmlPath	= 'ext/comment/config/form/comment.xml';
		$data		= $params['comment'];
		$idComment	= intval($data['id']);
		$idTask		= intval($data['id_task']);

			// Check rights
		if( $idComment === 0 ) {
			restrict('comment', 'task:add');
		} else {
			$comment	= TodoyuCommentManager::getComment($idComment);
			if( ! $comment->isCurrentUserCreator() ) {
				restrict('comment', 'task:editAll');
			}
		}

		$form	= TodoyuFormManager::getForm($xmlPath, $idComment);

		$form->setFormData($data);


		if( $form->isValid() ) {
			$data	= $form->getStorageData();

			TodoyuCommentManager::saveComment($data);
			TodoyuHeader::sendTodoyuHeader('tabLabel', TodoyuCommentTask::getLabel($data['id_task']));
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();
			TodoyuHeader::sendTodoyuHeader('idComment', $idComment);

			$form->setRecordID($idTask . '-' . $idComment);

			return $form->render();
		}
	}

}

?>