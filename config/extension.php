<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
 * General configuration for comment extension
 *
 * @package		Todoyu
 * @subpackage	Admin
 */

	// System name and email to be used in sender attribute inside emails
Todoyu::$CONFIG['EXT']['comment']['infomail']['fromname'] 		= Todoyu::$CONFIG['SYSTEM']['name'];
Todoyu::$CONFIG['EXT']['comment']['infomail']['email'] 			= Todoyu::$CONFIG['SYSTEM']['email'];



	// Allowable tags inside comments text when saving
Todoyu::$CONFIG['EXT']['contact']['allowabletags']	= '<p><b><i><u><strike><ol><ul><li><br>';



/**
 * Configuration for 'feedbacks' tab in portal
 */
Todoyu::$CONFIG['EXT']['comment']['feedbackTabFilters'] = array(
	array(
		'filter'	=> 'unseenFeedbackPerson',
		'value'		=> 0,
		'negate'	=> false
	)
);



/**
 * Add comments related tabs, context menu items
 */
if( allowed('comment', 'general:use') ) {

		// Add task tab for comments
	TodoyuTaskManager::addTaskTab('comment', 'TodoyuCommentTask::getLabel', 'TodoyuCommentTask::getContent', 30);

	if( TodoyuExtensions::isInstalled('portal') ) {
			// Add portal tab for feedbacks
		TodoyuPortalManager::addTab('feedback', 'TodoyuCommentRenderer::renderPortalFeedbackTabLabel', 'TodoyuCommentRenderer::renderPortalFeedbackTabContent', 30, array('comment/public'));
	}

		// Add task contextmenu to add comments to task
	TodoyuContextMenuManager::addFunction('Task', 'TodoyuCommentManager::getTaskContextMenuItems', 150);
}

?>