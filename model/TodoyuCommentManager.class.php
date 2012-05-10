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
 * General comment extension manager
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentManager {

	/**
	 * Get comment form
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idTask
	 * @param	Array		$formData
	 * @param	Array		$params
	 * @return	TodoyuForm
	 */
	public static function getCommentForm($idComment, $idTask, array $formData = array(), array $params = array()) {
		$xmlPath= 'ext/comment/config/form/comment.xml';
		$params['task'] = $idTask;

		$form	= TodoyuFormManager::getForm($xmlPath, $idComment, $params, $formData);

		if( sizeof($formData) ) {
			$form->setFormData($formData);
		}

		return $form;
	}



	/**
	 * Load configs of comment related filter widgets of project items
	 */
	public static function hookLoadProjectFilterConfig() {
		$filePath	= realpath(PATH_EXT_COMMENT . DIR_SEP . 'config' . DIR_SEP . 'filters-project.php');

		include_once($filePath);
	}

}

?>