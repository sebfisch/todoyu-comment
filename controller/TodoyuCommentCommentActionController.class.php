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
 * Comment action controller
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentCommentActionController extends TodoyuActionController {

	/**
	 * Init comment controller: restrict rights
	 *
	 * @param	Array	$params
	 */
	public function init(array $params) {
		restrict('comment', 'general:use');
	}



	/**
	 * Add a new comment
	 *
	 * @param	Array		$params
	 */
	public function addAction(array $params) {
		$idTask		= intval($params['task']);

		TodoyuCommentRights::restrictAddInTask( $idTask );

		return TodoyuCommentRenderer::renderEdit($idTask, 0);
	}



	/**
	 * Load edit view of the comment
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function editAction(array $params) {
		$idTask		= intval($params['task']);
		$idComment	= intval($params['comment']);

			// Person is the creator + has right editOwn or has right editAll
		TodoyuCommentRights::restrictEdit($idComment);

		return TodoyuCommentRenderer::renderEdit($idTask, $idComment);
	}



	/**
	 * Delete a comment
	 *
	 * @param	Array		$params
	 */
	public function deleteAction(array $params) {
		restrict('comment', 'comment:deleteOwn');

		$idComment		= intval($params['comment']);
		$comment		= TodoyuCommentCommentManager::getComment($idComment);

		TodoyuCommentRights::restrictDelete($idComment);

		TodoyuCommentCommentManager::deleteComment($idComment);

		$idTask	= $comment->getTaskID();

		TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
		TodoyuHeader::sendTodoyuHeader('tabLabel', TodoyuCommentTask::getLabel($idTask));
	}



	/**
	 * Save (update) comment
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function saveAction(array $params) {
		$xmlPath= 'ext/comment/config/form/comment.xml';
		$data	= $params['comment'];

		$idComment	= intval($data['id']);
		$idTask		= intval($data['id_task']);

			// Check editing rights for existing comments
		if( $idComment !== 0 ) {
			TodoyuCommentRights::restrictEdit($idComment);
		} else {
			TodoyuCommentRights::restrictAddInTask($idTask);
		}

		$form	= TodoyuFormManager::getForm($xmlPath, $idComment);
		$form->setFormData($data);

			// Validate comment and save / notify about failure
		if( $form->isValid() ) {
			$data	= $form->getStorageData();

				// Store comment and send email if mail-option activated
			TodoyuCommentCommentManager::saveComment($data);

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