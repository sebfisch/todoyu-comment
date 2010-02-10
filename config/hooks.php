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
 * Hooks config for Resources extension
 *
 * @package		Todoyu
 * @subpackage	Comment
 */

	// Extend comments form
TodoyuFormHook::registerBuildForm('ext/comment/config/form/comment.xml',	'TodoyuCommentRenderer::extendEditFormWithAutoRequestedFeedbackFromOwner', 20);
TodoyuFormHook::registerBuildForm('ext/comment/config/form/comment.xml',	'TodoyuCommentRenderer::extendEditFormWithAutoMailedCommentToOwner', 30);

TodoyuHookManager::registerHook('project', 'renderTasks', 'TodoyuCommentManager::onTasksRender');

?>