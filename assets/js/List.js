/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSC License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

/**
 *	List comments of task methods
 */
Todoyu.Ext.comment.List = {

	/**
	 * Refresh list of comments of given task, optionally toggle sorting order
	 *
	 * @param	Integer	idTask
	 * @param	Integer	desc	(0 or 1)
	 */
	refresh: function(idTask, desc) {
		var url				= Todoyu.getUrl('comment', 'task');
		var target			= 'task-' + idTask + '-tabcontent-comment';

		var options	= {
			'parameters': {
				'action':	'list',
				'task':		idTask,
				'desc':		desc
			}
		};

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Toggle comments list visibility
	 */
	toggle: function(idTask) {
		Todoyu.Ui.toggle('task-' + idTask + '-comments');
	}

};
