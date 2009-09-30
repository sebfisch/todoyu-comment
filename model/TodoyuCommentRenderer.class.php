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

		$data		= $comment->getTemplateData();
		$data['isInternal'] =  Todoyu::user()->isInternal();

		return render('ext/comment/view/comment.tmpl', $data);
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

//		TodoyuPage::loadExtAssets('comment');
		$data	= array(
			'idTask'	=> $idTask,
			'desc'		=> $desc,
			'comments'	=> ''
		);

		$commentIDs	= TodoyuCommentManager::getTaskCommentIDs($idTask, $desc);

		foreach($commentIDs as $idComment) {
			$data['comments'] .= self::renderComment($idComment);
		}

		return render('ext/comment/view/list.tmpl', $data);
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

			// Construct form object
		$formXml	= 'ext/comment/config/form/edit.xml';
		$form		= new TodoyuForm($formXml);
		$form		= TodoyuFormHook::callBuildForm($xmlPath, $form, $idComment);

			// Prepare/ load form data
		if( $idComment === 0 ) {
				// Creating a new comment
			$formData		= array(
				'id'		=> 0,
				'id_task' 	=> $idTask
			);
		} else {
				// Editing an existing comment record
			$comment	= TodoyuCommentManager::getComment($idComment);
			$formData	= $comment->getTemplateData();
		}

		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, $idComment);

			// Set form data
		$form->setFormData($formData);
		$form->setRecordID( $idTask . '-' . $idComment );

			// Render (edit-form wrapped inside the edit-template)
		$data	= array(
			'idTask'	=> $idTask,
			'idComment'	=> $idComment,
			'formhtml'	=> $form->render()
		);

		return render('ext/comment/view/edit.tmpl', $data);
	}



	/**
	 * Render email box (form) to send to comments over email to the selected users when task is saved
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderEmailBox($idTask, $idComment) {
		$idTask		= intval($idTask);
		$idComment	= intval($idComment);

		$xmlPath	= 'ext/comment/config/form/edit.xml';

			// Construct form object
		$form		= new TodoyuForm( $xmlPath );
		$form		= TodoyuFormHook::callBuildForm($xmlPath, $form, $idComment);

			// Prepare/ Load (preset e.g) form data
		$formData	= array(
			'id_task' => $idTask
		);
		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, $idComment);

			// Set form data
		$form->setFormData( $formData );
		$form->setRecordID( $idTask . '-' . $idComment );

			// Render
		return $form->getField('emailinfo')->render();
	}

}

?>