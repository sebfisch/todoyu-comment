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

	// Substitute comment identifiers in text by hyperlinks
TodoyuHookManager::registerHook('core', 'substituteLinkableElements', 'TodoyuCommentCommentManager::linkCommentIDsInText');

	// Extend comments form
TodoyuFormHook::registerBuildForm('ext/comment/config/form/comment.xml',	'TodoyuCommentRenderer::extendEditFormWithAutoRequestedFeedbackFromOwner', 20);
TodoyuFormHook::registerBuildForm('ext/comment/config/form/comment.xml',	'TodoyuCommentRenderer::extendEditFormWithAutoMailedCommentToOwner', 30);

//TodoyuHookManager::registerHook('project', 'renderTasks', 'TodoyuCommentCommentManager::onTasksRender');


	// System name and email to be used in sender attribute inside emails
Todoyu::$CONFIG['EXT']['comment']['infomail']['fromname'] 		= Todoyu::$CONFIG['SYSTEM']['name'];
Todoyu::$CONFIG['EXT']['comment']['infomail']['email'] 			= Todoyu::$CONFIG['SYSTEM']['email'];

	// Allowable tags inside comments text when saving
Todoyu::$CONFIG['EXT']['comment']['allowedtags']	= '<p><b><strong><em><span><i><u><strike><ol><ul><li><br><pre><a>';



/* ------------------------------------------------
	Configuration for 'feedback' tab in portal
   ------------------------------------------------ */
Todoyu::$CONFIG['EXT']['comment']['feedbackTabFilters'] = array(
	array(
		'filter'	=> 'unseenFeedbackPerson',
		'value'		=> 0,
		'negate'	=> false
	)
);



/* --------------------------------------------
	Add comment content tab and context menu
   -------------------------------------------- */
if( Todoyu::allowed('comment', 'general:use') ) {
		// Add task tab for comments
	TodoyuProjectTaskManager::addTaskTab('comment', 'TodoyuCommentTask::getLabel', 'TodoyuCommentTask::getContent', 30);
		// Add "Add New > Comment" to task context menu
	TodoyuContextMenuManager::addFunction('Task', 'TodoyuCommentCommentManager::getTaskContextMenuItems', 150);
}

?>