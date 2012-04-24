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
 * @module	Comment
 */

/**
 *	List comments of task methods
 */
Todoyu.Ext.comment.List = {

	/**
	 * Refresh list of comments of given task, optionally toggle sorting order
	 *
	 * @method	refresh
	 * @param	{Number}	idTask
	 * @param	{Boolean}	desc
	 */
	refresh: function(idTask, desc) {
		var url		= Todoyu.getUrl('comment', 'task');
		var options	= {
			parameters: {
				action:	'list',
				task:	idTask,
				desc:	desc !== false ? 1 : 0
			},
			onComplete: this.onRefreshed.bind(this, idTask)
		};
		var target	= 'task-' + idTask + '-tabcontent-comment';

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Check refreshed list of comments is empty hide the first of the two "add comment" buttons
	 *
	 * @method	onRefreshed
	 * @param	{Number}		idTask
	 * @param	{Ajax.Response}	response
	 */
	onRefreshed: function(idTask, response) {
		var firstButton = $('task-' + idTask).select('button.addComment')[0];

		if( this.hasComments(idTask) ) {
				// There are comments: unhide first button (above comments)
			firstButton.show();
		} else {
				// There are no comments: hide first button
			firstButton.hide();
		}
	},



	/**
	 * Get amount of displayed comments of given task
	 *
	 * @method	getAmountComments
	 * @param	{Number}			idTask
	 * @return	{Number}
	 */
	getAmountComments: function(idTask) {
		var commentsElement = $('task-' + idTask + '-comments');
		if( commentsElement ) {
			return commentsElement.select('li.comment').size();
		} else {
			return 0;
		}
	},



	/**
	 * Check whether the given task has any shown comments
	 *
	 * @method	hasComments
	 * @param	{Number}	idTask
	 * @return	{Boolean}
	 */
	hasComments: function(idTask) {
		return this.getAmountComments(idTask) > 0;
	},



	/**
	 * Toggle comments list visibility
	 *
	 * @method	toggle
	 * @param	{Number}	idTask
	 */
	toggle: function(idTask) {
		Todoyu.Ui.toggle('task-' + idTask + '-comments');
	}

};