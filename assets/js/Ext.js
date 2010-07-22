/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * Ext: comment
 */
Todoyu.Ext.comment = {

	PanelWidget: {},


	/**
	 * Toggle customer visibility of given comment
	 *
	 * @param	{Number}	idComment
	 */
	togglePublic: function(idComment) {
		var url		= Todoyu.getUrl('comment', 'task');
		var options	= {
			'parameters': {
				'action':	'togglepublic',
				'comment':	idComment
			},
			'onComplete': this.onToggledPublic.bind(this, idComment)
		};

		Todoyu.send(url , options);
	},



	/**
	 * Handler for togglePublic
	 *
	 * @param	{Number}			idComment
	 * @param	{Ajax.Response}	response
	 */
	onToggledPublic: function(idComment, response) {
		$('task-comment-' + idComment).toggleClassName('isPublic');
		$('public-trigger-' + idComment).toggleClassName('comment-public');
	},



	/**
	 * Toggle 'seen' status of given comment
	 *
	 * @param	{Number}	idComment
	 */
	setSeenStatus: function(idComment, idPerson)	{
		var url		= Todoyu.getUrl('comment', 'task');
		var options	= {
			'parameters': {
				'action':	'seen',
				'comment':	idComment
			},
			'onComplete': this.onSeenStatusSet.bind(this, idComment, idPerson)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler for setSeenStatus
	 *
	 * @param	{Number}			idComment
	 * @param	{Number}			idPerson
	 * @param	{Ajax.Response}	response
	 */
	onSeenStatusSet: function(idComment, idPerson, response) {
			// Remove unseen icon
		$('comment-' + idComment + '-seenstatus').remove();
			// Remove class which marks the name unseen
		$('comment-personfeedback-' + idComment + '-' + idPerson).removeClassName('commentperson-unapproved');

			// Count down the feedback counter
		if( Todoyu.getArea() === 'portal' && Todoyu.exists('portal-tab-feedback') ) {
			var numFeedbacks	= response.getTodoyuHeader('feedback');
			var labelElement	= $('portal-tab-feedback').down('span.labeltext');

			labelElement.update(labelElement.innerHTML.replace(/\(\d\)/, '(' + numFeedbacks + ')'));
		}
	},



	/**
	 * Remove given comment
	 *
	 * @param	{Number}	idComment
	 */
	remove: function(idComment) {
		if( ! confirm('[LLL:comment.delete.confirm]') ) {
			return false;
		}

		var url		= Todoyu.getUrl('comment', 'comment');
		var options	= {
			'parameters': {
				'action':	'delete',
				'comment':	idComment
			},
			'onComplete': Todoyu.Ext.comment.Edit.onRemoved.bind(this)
		};

		Todoyu.send(url, options);

		Effect.Fade($('task-comment-' + idComment), {
			'duration':	0.5,
			'from':		1,
			'to':		0
		});
	},



	/**
	 * Add new comment to given task
	 *
	 * @param	{Number}	idTask
	 */
	addTaskComment: function(idTask) {
		Todoyu.Ext.project.Task.showDetails(idTask, 'comment', this.onTaskCommentTabLoaded.bind(this));
	},

	onTaskCommentTabLoaded: function(idTask, tab, response) {
		if( ! Todoyu.exists('task-' + idTask + '-commentform-0') ) {
			this.edit(idTask, 0);
		}
	},



	/**
	 * Set Label (on adding or removing comment)
	 *
	 * @param	{Number} idTask
	 * @param	{String}	label
	 */
	setTabLabel: function(idTask, label){
		$('task-' + idTask + '-tab-comment-label').select('.labeltext').first().update(label);
	},


	/**
	 * Check whether sorting of comments of given task is desc (true) or asc (false)
	 *
	 * @param	{Number}	idTask
	 * @return	{Boolean}
	 */
	checkSortingIsDesc: function( idTask ) {
		var elementID	= 'task-' + idTask + '-comments';
		var isDesc = false;

		if(elementID) {
			isDesc	= $(elementID).hasClassName('desc');
		}

		return isDesc;
	},



	/**
	 * Toggle display of comment (feedbacks and mailing) log
	 *
	 * @param	{Number}	idTask
	 */
	toggleLog: function(idComment) {
		var logDiv	= $('task-comment-log-' + idComment + '-details');

		if( ! logDiv.visible() ) {
			if( logDiv.empty() ) {

				var url		= Todoyu.getUrl('comment', 'comment');
				var options	= {
					'parameters': {
						'action':	'log',
						'comment':	idComment
					},
					'onComplete': Todoyu.Ext.comment.onLogToggled.bind(this)
				};

				Todoyu.Ui.update(logDiv, url, options);
			}
			logDiv.show();
		} else {
			logDiv.hide();
		}

		this.updateToggleLogIcon(idComment);
	},



	onLogToggled: function(idComment, response) {
//		Todoyu.log('OnComplete erreicht');
	},



	updateToggleLogIcon: function(idComment) {
		Todoyu.Ui.updateToggleIcon('task-comment-log-', idComment);
	},



	/**
	 * Toggle sorting of comments of given task
	 *
	 * @param	{Number}	idTask
	 */
	toggleSorting: function(idTask) {
		var sortingIsDesc	= this.checkSortingIsDesc(idTask);
		var desc 			= sortingIsDesc ? 0 : 1;

		Todoyu.Ext.comment.List.refresh(idTask, desc);
	},
	
	
	
	/**
	 * Add a new comment, open empty edit form
	 * 
	 * @param	{Number}		idTask
	 */
	add: function(idTask) {
		this.removeForms(idTask);
		
		var url		= Todoyu.getUrl('comment', 'comment');
		var options = {
			'parameters': {
				'action': 	'add',
				'task':		idTask
			},
			'insertion':	'after',
			'onComplete': this.onAdd.bind(this, idTask)
		};
		var target	= 'task-' + idTask + '-comment-commands';
		
		Todoyu.Ui.update(target, url, options);		
	},
	
	
	
	/**
	 * Handler when empty edit form to add comment loaded
	 * 
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}	response
	 */
	onAdd: function(idTask, response) {
		
	},



	/**
	 * Evoke comment editor (of given comment of given task)
	 * Note:	there is the method 'edit' and the sub object 'Edit' (case-sensitive) with its own methods
	 *
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 */
	edit: function(idTask, idComment) {
		var url		= Todoyu.getUrl('comment', 'comment');
		var options = {
			'parameters': {
				'action': 	'edit',
				'task':		idTask,
				'comment':	idComment
			},
			'onComplete':	this.onEdit.bind(this, idTask, idComment)
		};
		var target	= 'task-comment-' + idComment + '-text';
		
		Todoyu.Ui.update(target, url, options);
	},
	
	
	
	/**
	 * Handler when comment edit form loaded
	 * 
	 * @param	{Number}			idTask
	 * @param	{Number}			idComment
	 * @param	{Ajax.Response}	response
	 */
	onEdit: function(idTask, idComment, response) {
		$('task-' + idTask + '-commentform-' + idComment).removeClassName('taskOptionBlock');
	},
	
	
	
	/**
	 * Remove all open edit forms for comment
	 * 
	 * @param	{Number}		idTask
	 */
	removeForms: function(idTask) {
		$('task-' + idTask + '-tabcontent-comment').select('.commentform').invoke('remove');
	}

};