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

	// Add auto feedback and auto email to comment form
TodoyuFormHook::registerBuildForm('ext/comment/config/form/comment.xml',	'TodoyuCommentManager::hookAddAutoFeedbackFields');
TodoyuFormHook::registerBuildForm('ext/comment/config/form/comment.xml',	'TodoyuCommentManager::hookAddAutoMailFields');
TodoyuFormHook::registerLoadData('ext/comment/config/form/comment.xml',	'TodoyuCommentManager::hookLoadDataAutoFeedback');
TodoyuFormHook::registerLoadData('ext/comment/config/form/comment.xml',	'TodoyuCommentManager::hookLoadDataAutoMail');

//TodoyuHookManager::registerHook('project', 'renderTasks', 'TodoyuCommentCommentManager::onTasksRender');


	// Callbacks for exteding filter widgets of other extensions
TodoyuHookManager::registerHook('core', 'loadconfig.project.filters', 'TodoyuCommentManager::hookLoadProjectFilterConfig');

?>