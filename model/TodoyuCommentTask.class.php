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
 * Comments in task tab
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentTask {

	/**
	 * Get label for comment tab in the task
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function getLabel($idTask) {
		$idTask	= intval($idTask);

		$numComments = TodoyuCommentCommentManager::getNumberOfTaskComments($idTask);

		if( $numComments === 0 ) {
			return Label('comment.ext.tab.noComments');
		} elseif( $numComments === 1 ) {
			return '1 ' . Label('comment.ext.tab.comment');
		} else {
			return $numComments . ' ' . Label('comment.ext.tab.comments');
		}
	}



	/**
	 * Get tab content for a task
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function getContent($idTask) {
		$idTask		= intval($idTask);
		$numComments= TodoyuCommentCommentManager::getNumberOfTaskComments($idTask);

			// If no comments
		if( $numComments === 0 ) {
				// Show form to add first task if allowed
			return TodoyuCommentRenderer::renderEdit($idTask, 0);
		} else {
			return TodoyuCommentRenderer::renderCommentList($idTask);
		}
	}

}

?>