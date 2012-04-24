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
 * Main comment object
 *
 * @class		Comment
 * @namespace	Todoyu.Ext
 */
Todoyu.Ext.comment = {

	/**
	 * @property	PanelWidget
	 * @type		Object
	 */
	PanelWidget: {},

	/**
	 * Initialize comment extension
	 * Add a hook to observe form display to fix email receivers field display
	 *
	 * @method	init
	 */
	init: function() {
		Todoyu.Hook.add('form.display', this.onFormDisplay.bind(this));
	},



	/**
	 * Hook, when a form is displayed
	 *
	 * @method	onFormDisplay
	 * @param	{String}	idForm
	 * @param	{String}	name
	 * @param	{String}	recordID		idTask-idComment
	 */
	onFormDisplay: function(idForm, name, recordID) {
		if( name === 'comment' ) {
			this.Edit.onFormDisplay(idForm, name, recordID)
		}
	},



	/**
	 * Toggle customer visibility of given comment
	 *
	 * @method	togglePublic
	 * @param	{Number}	idComment
	 */
	togglePublic: function(idComment) {
		var url		= Todoyu.getUrl('comment', 'task');
		var options	= {
			parameters: {
				action:		'togglepublic',
				comment:	idComment
			},
			onComplete: this.onToggledPublic.bind(this, idComment)
		};

		Todoyu.send(url , options);
	},



	/**
	 * Handler for togglePublic
	 *
	 * @method	onToggledPublic
	 * @param	{Number}			idComment
	 * @param	{Ajax.Response}		response
	 */
	onToggledPublic: function(idComment, response) {
		$('task-comment-' + idComment).toggleClassName('isPublic');
		$('public-trigger-' + idComment).toggleClassName('comment-public');

		var warning;
		if( response.hasTodoyuHeader('publicFeedbackWarning') ) {
			if( !this.commentHasPublicFeedbackWarning(idComment) ) {
					// Add received warning
				warning		= new Element('div', { className:	'publicFeedbackWarning'}).update(response.getTodoyuHeader('publicFeedbackWarning'));
				$('task-comment-' + idComment + '-text').insert(warning);
			}
		} else if( this.commentHasPublicFeedbackWarning(idComment) ) {
				// Remove invalid warning
			this.getCommentPublicFeedbackWarning(idComment).remove();
		}
	},



	/**
	 * @method	getCommentPublicFeedbackWarning
	 * @param	{Number}	idComment
	 * @param	{Element}
	 */
	getCommentPublicFeedbackWarning: function(idComment) {
		return $('task-comment-' + idComment + '-text').down('.publicFeedbackWarning');
	},


	/**
	 * Check whether there is a warning about non-public task/comments being not visible
	 *
	 * @method	hasPublicFeedbackWarning
	 * @param	{Number}	idComment
	 * @return	{Boolean}
	 */
	commentHasPublicFeedbackWarning: function(idComment) {
		return Todoyu.exists(this.getCommentPublicFeedbackWarning(idComment));
	},


	/**
	 * Toggle 'seen' status of given comment
	 *
	 * @method	setSeenStatus
	 * @param	{Number}	idComment
	 * @param	{Number}	idPerson
	 */
	setSeenStatus: function(idComment, idPerson) {
		var url		= Todoyu.getUrl('comment', 'task');
		var options	= {
			parameters: {
				action:	'seen',
				comment:	idComment
			},
			onComplete: this.onSeenStatusSet.bind(this, idComment, idPerson)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler for setSeenStatus
	 *
	 * @method	onSeenStatusSet
	 * @param	{Number}			idComment
	 * @param	{Number}			idPerson
	 * @param	{Ajax.Response}	response
	 */
	onSeenStatusSet: function(idComment, idPerson, response) {
			// Remove unseen icon
		$('comment-' + idComment + '-seenstatus').remove();
			// Remove class which marks the name unseen
		$('comment-personfeedback-' + idComment + '-' + idPerson).removeClassName('commentperson-unapproved');

		this.updateFeedbackTab(response.getTodoyuHeader('feedback'));
	},



	/**
	 * @method	updateFeedbackTab
	 * @param	{Number}		numFeedbacks
	 */
	updateFeedbackTab: function(numFeedbacks) {
			// Count-down the feedback counter
		if( Todoyu.isInArea('portal') && Todoyu.exists('portal-tab-feedback') ) {
			var labelElement	= $('portal-tab-feedback').down('span.labeltext');

			labelElement.update(labelElement.innerHTML.replace(/\(\d\)/, '(' + numFeedbacks + ')'));
		}
	},



	/**
	 * Remove given comment
	 *
	 * @method	remove
	 * @param	{Number}	idComment
	 */
	remove: function(idComment) {
		if( ! confirm('[LLL:comment.ext.delete.confirm]') ) {
			return false;
		}

		var url		= Todoyu.getUrl('comment', 'comment');
		var options	= {
			parameters: {
				action:		'delete',
				comment:	idComment
			},
			onComplete: Todoyu.Ext.comment.Edit.onRemoved.bind(this.Edit)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Add new comment to given task: expand task details and open comments tab with new comment form
	 *
	 * @method	addTaskComment
	 * @param	{Number}	idTask
	 */
	addTaskComment: function(idTask) {
		Todoyu.Ext.project.Task.showDetails(idTask, 'comment', this.onTaskCommentTabLoaded.bind(this));
	},



	/**
	 * Handler when task comment tab is loaded
	 *
	 * @method	onTaskCommentTabLoaded
	 * @param	{Number}	idTask
	 * @param	{String}	tab
	 */
	onTaskCommentTabLoaded: function(idTask, tab) {
		if( ! Todoyu.exists('task-' + idTask + '-commentform-0') ) {
			this.add(idTask);
		} else {
			if( ! Todoyu.Ext.project.Task.isDetailsVisible(idTask) ) {
				$('task-' + idTask + '-details').toggle();
			}
		}
	},



	/**
	 * Set Label (on adding or removing comment)
	 *
	 * @method	setTabLabel
	 * @param	{Number} idTask
	 * @param	{String}	label
	 */
	setTabLabel: function(idTask, label){
		$('task-' + idTask + '-tab-comment-label').select('.labeltext').first().update(label);
	},



	/**
	 * Check whether sorting of comments of given task is desc (true) or asc (false)
	 *
	 * @method	checkSortingIsDesc
	 * @param	{Number}	idTask
	 * @return	{Boolean}
	 */
	checkSortingIsDesc: function( idTask ) {
		var elementID	= 'task-' + idTask + '-comments';
		var isDesc = false;

		if( elementID ) {
			isDesc	= $(elementID).hasClassName('desc');
		}

		return isDesc;
	},



	/**
	 * Toggle sorting of comments of given task
	 *
	 * @method	toggleSorting
	 * @param	{Number}	idTask
	 */
	toggleSorting: function(idTask) {
		var list 	= $('task-' + idTask + '-comments');

		list.select('li.comment').reverse().each(function(commentElement){
			list.insert(commentElement);
		});

		$('task-' + idTask + '-tabcontent-comment').select('button.order').invoke('toggleClassName', 'desc');
	},



	/**
	 * Add a new comment, open empty edit form
	 *
	 * @method	add
	 * @param	{Number}	idTask
	 */
	add: function(idTask) {
			// Clean up UI
		this.removeForms(idTask);

		var addButton	= $('task-' + idTask + '-comment-commands-bottom').down('.addComment');
		if( addButton ) {
			addButton.hide();
		}

			// Load new comment form
		var url		= Todoyu.getUrl('comment', 'comment');
		var options = {
			parameters: {
				action: 'add',
				task:	idTask
			},
			insertion:	'after',
			onComplete:	this.onAdded.bind(this, idTask)
		};
		var target	= 'task-' + idTask + '-comment-commands-top';

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Handler when empty edit form to add comment loaded
	 *
	 * @method	onAdded
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}		response
	 */
	onAdded: function(idTask, response) {
		$('task-' + idTask + '-comment-commands-top').scrollToElement();
	},



	/**
	 * Evoke comment editor (of given comment of given task)
	 * Note:	there is the method 'edit' and the sub object 'Edit' (case-sensitive) with its own methods
	 *
	 * @method	edit
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 */
	edit: function(idTask, idComment) {
		var url		= Todoyu.getUrl('comment', 'comment');
		var options = {
			parameters: {
				action: 	'edit',
				task:		idTask,
				comment:	idComment
			},
			onComplete:	this.onEdit.bind(this, idTask, idComment)
		};
		var target	= 'task-comment-' + idComment + '-text';

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Handler when comment edit form loaded
	 *
	 * @method	onEdit
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
	 * @method	removeForms
	 * @param	{Number}		idTask
	 */
	removeForms: function(idTask) {
		$('task-' + idTask + '-tabcontent-comment').select('.commentform').invoke('remove');
	}

};