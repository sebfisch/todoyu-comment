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
	public static function renderCommentList($idTask, $desc = false) {
		$idTask		= intval($idTask);
		$desc		= $desc ? true : false;

		$tmpl	= 'ext/comment/view/list.tmpl';
		$data	= array(
			'idTask'	=> $idTask,
			'desc'		=> $desc,
			'descClass'	=> $desc ? 'desc' : 'asc',
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
	 * @return	String
	 */
	public static function renderEdit($idTask, $idComment = 0) {
		$idTask		= intval($idTask);
		$idComment	= intval($idComment);
		$xmlPath	= 'ext/comment/config/form/comment.xml';

		$form	= TodoyuFormManager::getForm($xmlPath, $idComment);

		if( $idComment === 0 ) {
				// New comment
			$idFeedbackPerson	= TodoyuCommentCommentManager::getOpenFeedbackRequestPersonID($idTask);
			$data	= array(
				'id'		=> 0,
				'id_task'	=> $idTask,
				'feedback'	=> $idFeedbackPerson
			);
		} else {
				// Edit comment
			$comment	= TodoyuCommentCommentManager::getComment($idComment);
			$data		= $comment->getTemplateData(true);
			$data['feedback'] = TodoyuArray::getColumn($data['persons_feedback'], 'id');
		}

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
	 * Extend comment edit form with attribute to auto-request feedback from task owner for persons of configured groups
	 *
	 * @param	TodoyuForm		$form
	 * @param	Integer			$idComment
	 * @return	TodoyuForm		$form
	 */
	public static function extendEditFormWithAutoRequestedFeedbackFromOwner(TodoyuForm $form, $idComment = 0) {
		$extConf= TodoyuSysmanagerExtConfManager::getExtConf('comment');
		$roles	= explode(',', $extConf['autorequestownerfeedback']);

		if( TodoyuContactPersonManager::hasAnyRole($roles) ) {
			$form->getFieldset('main')->removeField('feedback', true);
			$form->getFieldset('main')->addElementsFromXML('ext/comment/config/form/comment-autofeedbackfromowner.xml');
		}

		return $form;
	}



	/**
	 * Extend comment edit form with attribute to auto-mail comment by email to task owner for persons of configured groups
	 *
	 * @param	TodoyuForm	$form
	 * @param	Integer		$idComment
	 * @return	TodoyuForm	$form
	 */
	public static function extendEditFormWithAutoMailedCommentToOwner(TodoyuForm $form, $idComment = 0) {
		$extConf	= TodoyuSysmanagerExtConfManager::getExtConf('comment');
		$roles		= explode(',', $extConf['automailcommenttoowner']);

		if( TodoyuContactPersonManager::hasAnyRole($roles) ) {
			$form->getFieldset('email')->removeField('sendasemail', true);
			$form->getFieldset('email')->removeField('emailreceivers', true);
			$form->getFieldset('main')->addElementsFromXML('ext/comment/config/form/comment-automailtoowner.xml');
		}

		return $form;
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
			$feedbackIDs= TodoyuCommentCommentManager::getOpenFeedbackCommentIDs(Todoyu::personid());
			$label	= $label . ' (' . sizeof($feedbackIDs) . ')';
		}

		return $label;
	}



	/**
	 * Render feedback tab content in portal
	 *
	 * @return	String
	 */
	public static function renderPortalFeedbackTabContent() {
		$taskIDs= TodoyuCommentCommentManager::getFeedbackTaskIDs();

		TodoyuHeader::sendTodoyuHeader('items', sizeof($taskIDs));

		TodoyuProjectPreferences::setForcedTaskTab('comment');

		return TodoyuProjectTaskRenderer::renderTaskListing($taskIDs);
	}

}

?>