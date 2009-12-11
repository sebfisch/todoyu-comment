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

		$numComments = TodoyuCommentManager::getNumberOfTaskComments($idTask);

		TodoyuPage::addExtAssets('comment');

		if( $numComments === 0 ) {
			return Label('comment.tab.noComments');
		} elseif( $numComments === 1 ) {
			return '1 ' . Label('comment.tab.comment');
		} else {
			return $numComments . ' ' . Label('comment.tab.comments');
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
		$numComments= TodoyuCommentManager::getNumberOfTaskComments($idTask);

			// If no comments
		if( $numComments === 0 ) {
			if( allowed('comment', 'add') ) {
					// Show form to add first task if allowed
				return TodoyuCommentRenderer::renderEdit($idTask, 0);
			} else {
					// Show info message
				return TodoyuCommentRenderer::renderNoCommentsInfo();
			}
		} else {
			return TodoyuCommentRenderer::renderCommentList($idTask);
		}
	}

}

?>