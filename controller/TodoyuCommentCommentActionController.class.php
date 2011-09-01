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
		Todoyu::restrict('comment', 'general:use');
	}



	/**
	 * Add (form for adding) a new comment
	 *
	 * @param	Array		$params
	 * @return	String
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
		Todoyu::restrict('comment', 'comment:deleteOwn');

		$idComment		= intval($params['comment']);
		$comment		= TodoyuCommentCommentManager::getComment($idComment);

		TodoyuCommentRights::restrictDelete($idComment);

		TodoyuCommentCommentManager::deleteComment($idComment);

		$idTask	= $comment->getTaskID();

		TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
		TodoyuHeader::sendTodoyuHeader('idComment', $idComment);
		TodoyuHeader::sendTodoyuHeader('tabLabel', TodoyuCommentTask::getLabel($idTask));
	}



	/**
	 * Save (update) comment (+save comment mail if option activated)
	 *
	 * @param	Array		$params
	 * @return	Void|String				Failure returns re-rendered form with error messages
	 */
	public function saveAction(array $params) {
		$xmlPath= 'ext/comment/config/form/comment.xml';
		$data	= $params['comment'];

		$idComment	= intval($data['id']);
		$idTask		= intval($data['id_task']);

		$sendAsEmail			= intval($data['sendasemail']) === 1;
		$mailReceiverPersonIDs	= array_unique(TodoyuArray::intval($data['emailreceivers'], true, true));

			// Check editing rights for existing comments
		if( $idComment !== 0 ) {
			TodoyuCommentRights::restrictEdit($idComment);
		} else {
			TodoyuCommentRights::restrictAddInTask($idTask);
		}

		$form	= TodoyuFormManager::getForm($xmlPath, $idComment);
		$form->setFormData($data);

			// Validate comment and save + send mail if activated / notify about failure
		if( $form->isValid() ) {
			$data	= $form->getStorageData();

				// Store comment
			TodoyuCommentCommentManager::saveComment($data);
			TodoyuHeader::sendTodoyuHeader('tabLabel', TodoyuCommentTask::getLabel($data['id_task']));

				// Send email(s) if mail option activated
			if( $sendAsEmail && sizeof($mailReceiverPersonIDs) > 0 ) {
				$sent	= TodoyuCommentMailer::sendEmails($idComment, $mailReceiverPersonIDs);
				if( $sent ) {
					TodoyuCommentMailManager::saveMailsSent($idComment, $mailReceiverPersonIDs);
					TodoyuHeader::sendTodoyuHeader('sentEmail', true);
				}
			}
			// Form data is invalid
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();
			TodoyuHeader::sendTodoyuHeader('idComment', $idComment);

			$form->setRecordID($idTask . '-' . $idComment);

			return $form->render();
		}
	}

}

?>