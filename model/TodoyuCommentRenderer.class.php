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

		$comment	= TodoyuCommentManager::getComment($idComment);

		$tmpl		= 'ext/comment/view/comment.tmpl';
		$data		= $comment->getTemplateData(true);

		return render($tmpl, $data);
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
			'comments'	=> array()
		);

		$commentIDs	= TodoyuCommentManager::getTaskCommentIDs($idTask, $desc);

		foreach($commentIDs as $idComment) {
			$data['comments'][$idComment] = self::renderComment($idComment);
		}

		return render($tmpl, $data);
	}



	/**
	 * @todo make localized, check
	 *
	 * @return	String
	 */
	public static function renderNoCommentsInfo() {
		return 'No Comments';
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

		$form		= TodoyuFormManager::getForm($xmlPath, $idComment);
		$data		= array();

		if( $idComment !== 0 ) {
			$data		= TodoyuCommentManager::getComment($idComment)->getTemplateData();
		} else {
			$data['id_task']= $idTask;
			$data['id']		= $idComment;
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

		return render($tmpl, $data);
	}



	/**
	 * Render comment feedbacks and mailing log
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idComment
	 * @return	String
	 */
	public static function renderLog($idTask, $idComment = 0) {
		$idTask		= intval($idTask);
		$idComment	= intval($idComment);

			// Render
		$tmpl	= 'ext/comment/view/history.tmpl';

		$data	= array(
			'idTask'	=> $idTask,
			'idComment'	=> $idComment,
			'history'	=> TodoyuCommentHistoryManager::getHistory($idComment)
		);

		return render($tmpl, $data);
	}



	/**
	 * Extend comment edit form with attribute to auto-request feedback from task owner for users of configured groups
	 *
	 * @param	TodoyuForm	$form
	 * @param	Integer	$idComment
	 * @return	TodoyuForm	$form
	 */
	public static function extendEditFormWithAutoRequestedFeedbackFromOwner(TodoyuForm $form, $idComment = 0) {
		$extConf	= TodoyuExtConfManager::getExtConf('comment');
		$usergroups	= explode(',', $extConf['autorequestownerfeedback']);

		if ( TodoyuUserManager::isInAnyGroup(userid(), $usergroups) ) {
			$form->getFieldset('main')->removeField('feedback', true);
			$form->getFieldset('main')->addElementsFromXML('ext/comment/config/form/comment-autofeedbackfromowner.xml');
		}

		return $form;
	}



	/**
	 * Extend comment edit form with attribute to auto-mail comment by email to task owner for users of configured groups
	 *
	 * @param	TodoyuForm	$form
	 * @param	Integer	$idComment
	 * @return	TodoyuForm	$form
	 */
	public static function extendEditFormWithAutoMailedCommentToOwner(TodoyuForm $form, $idComment = 0) {
		$extConf	= TodoyuExtConfManager::getExtConf('comment');
		$usergroups	= explode(',', $extConf['automailcommenttoowner']);

		if ( TodoyuUserManager::isInAnyGroup(userid(), $usergroups) ) {
			$form->getFieldset('main')->removeField('sendasemail', true);
			$form->getFieldset('main')->removeField('emailreceivers', true);
			$form->getFieldset('main')->addElementsFromXML('ext/comment/config/form/comment-automailtoowner.xml');
		}

		return $form;
	}



	/**
	 * Get tab label for portal feedback task
	 *
	 * @param	Bool		$count
	 * @return	String
	 */
	public static function renderPortalFeedbackTabLabel($count = true) {
		$label	= TodoyuLocale::getLabel('comment.portal.tab.feedback');

		if( $count ) {
			$taskIDs= TodoyuCommentManager::getFeedbackTaskIDs();

			$label	= $label . ' (' . sizeof($taskIDs) . ')';
		}

		return $label;
	}



	/**
	 * Render feedback tab content in portal
	 *
	 * @return	String
	 */
	public static function renderPortalFeedbackTabContent() {
		$taskIDs= TodoyuCommentManager::getFeedbackTaskIDs();

		TodoyuProjectPreferences::setForcedTaskTab('comment');

		return TodoyuPortalRenderer::renderTaskList($taskIDs);
	}

}

?>