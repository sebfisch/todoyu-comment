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
 * General configuration for comment extension
 *
 * @package		Todoyu
 * @subpackage	Admin
 */

	// System name and email to be used in sender attribute inside emails
$CONFIG['EXT']['comment']['infomail']['fromname'] 		= $CONFIG['SYSTEM']['name'];
$CONFIG['EXT']['comment']['infomail']['email'] 			= $CONFIG['SYSTEM']['email'];



	// Allowable tags inside comments text when saving
$CONFIG['EXT']['contact']['allowabletags']	= '<p><b><i><u><strike><ol><ul><li><br>';



/**
 * Configuration for 'feedbacks' tab in portal
 */
$CONFIG['EXT']['comment']['feedbackTabFilters'] = array(
	array(
		'filter'	=> 'unseenFeedbackCurrentPerson',
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
	TodoyuContextMenuManager::registerFunction('Task', 'TodoyuCommentManager::getTaskContextMenuItems', 150);
}

?>