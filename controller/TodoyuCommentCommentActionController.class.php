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

	public function editAction(array $params) {
		$idTask		= intval($params['task']);
		$idComment	= intval($params['comment']);

		return TodoyuCommentRenderer::renderEdit($idTask, $idComment);
	}

	public function deleteAction(array $params) {
		$idComment	= intval($params['comment']);

		TodoyuCommentManager::deleteComment($idComment);
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

		$form		= TodoyuFormManager::getForm($xmlPath, $idComment);

		$form->setFormData($data);

		if( $form->isValid() ) {
			$data	= $form->getStorageData();

			TodoyuCommentManager::saveComment($data);
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();
			TodoyuHeader::sendTodoyuHeader('idComment', $idComment);

			return $form->render();
		}
	}

}

?>