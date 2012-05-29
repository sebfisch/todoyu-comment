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
 * Render task comments
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentRenderer {

	/**
	 * Render a comment
	 *
	 * @param	Integer		$idComment
	 * @return	String
	 */
	public static function renderComment($idComment) {
		$idComment	= intval($idComment);

		$comment	= TodoyuCommentCommentManager::getComment($idComment);

		$tmpl		= 'ext/comment/view/comment.tmpl';
		$data		= $comment->getTemplateData(true);

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Render comment list in task tab
	 *
	 * @param	Integer		$idTask
	 * @param	Boolean		$desc
	 * @return	String
	 */
	public static function renderCommentList($idTask, $desc = true) {
		$idTask		= intval($idTask);
		$desc		= $desc ? true : false;

		$tmpl	= 'ext/comment/view/list.tmpl';
		$data	= array(
			'idTask'	=> $idTask,
			'desc'		=> $desc,
			'comments'	=> array(),
			'locked'	=> TodoyuProjectTaskManager::isLocked($idTask)
		);

		$commentIDs	= TodoyuCommentCommentManager::getTaskCommentIDs($idTask, $desc);

		foreach($commentIDs as $idComment) {
			$data['comments'][$idComment] = self::renderComment($idComment);
		}

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Render comment edit area (edit form) in the task tab
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idComment
	 * @param	Array		$formParams
	 * @return	String
	 */
	public static function renderEdit($idTask, $idComment = 0, array $formParams = array()) {
		$idTask		= intval($idTask);
		$idComment	= intval($idComment);
		$form		= TodoyuCommentCommentManager::getCommentForm($idComment, $idTask, array(), $formParams);

		if( $idComment === 0 ) {
				// New comment
			$idFeedbackPerson	= TodoyuCommentCommentManager::getOpenFeedbackRequestPersonID($idTask);
			$data	= array(
				'id'		=> 0,
				'id_task'	=> $idTask,
				'feedback'	=> array($idFeedbackPerson)
			);
		} else {
				// Edit comment
			$comment	= TodoyuCommentCommentManager::getComment($idComment);
			$data		= $comment->getTemplateData(true);
			$data['feedback'] = $comment->getFeedbackPersonsIDs();
		}

		$xmlPath= 'ext/comment/config/form/comment.xml';
		$data	= TodoyuFormHook::callLoadData($xmlPath, $data, $idComment, array(
			'task' => $idTask
		));

		$form->setFormData($data);
		$form->setRecordID($idTask . '-' . $idComment);

			// Render (edit-form wrapped inside the edit-template)
		$tmpl	= 'ext/comment/view/edit.tmpl';
		$data	= array(
			'idTask'	=> $idTask,
			'idComment'	=> $idComment,
			'formhtml'	=> $form->render()
		);

		return Todoyu::render($tmpl, $data);
	}


	/**
	 * Get tab label for portal feedback task: label and amount of feedbacks
	 *
	 * @param	Boolean		$count
	 * @return	String
	 */
	public static function renderPortalFeedbackTabLabel($count = true) {
		$label	= Todoyu::Label('comment.ext.portal.tab.feedback');

		if( $count ) {
			$numFeedbacks	= TodoyuCommentFeedbackManager::getOpenFeedbackCount();
			$label			= $label . ' (' . $numFeedbacks . ')';
		}

		return $label;
	}



	/**
	 * Render feedback tab content in portal
	 *
	 * @return	String
	 */
	public static function renderPortalFeedbackTabContent() {
		$amountFeedbackRequests	= TodoyuCommentFeedbackManager::getOpenFeedbackCount();
		TodoyuHeader::sendTodoyuHeader('items', $amountFeedbackRequests);

		TodoyuProjectPreferences::setForcedTaskTab('comment');

		$taskIDs	= TodoyuCommentCommentManager::getFeedbackTaskIDs();

		return TodoyuProjectTaskRenderer::renderTaskListing($taskIDs);
	}

}

?>