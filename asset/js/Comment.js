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
 * Comment related methods
 * @type {Object}
 */
Todoyu.Ext.comment.Comment = {

	/**
	 * @var	Ext back ref
	 */
	ext: Todoyu.Ext.comment,



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
		$('comment-' + idComment + '-action-makePublic').toggleClassName('isPublic');

		var hasPublicFeedbackWarning	= this.commentHasPublicFeedbackWarning(idComment);

		if( response.hasTodoyuHeader('publicFeedbackWarning') ) {
				// Add received warning
			if( !hasPublicFeedbackWarning ) {
				$('task-comment-' + idComment + '-text').insert(new Element('div', {
					className:	'publicFeedbackWarning'
				}).update(response.getTodoyuHeader('publicFeedbackWarning')));
			}
		} else {
				// Remove invalid warning
			if( hasPublicFeedbackWarning ) {
				this.getCommentPublicFeedbackWarning(idComment).remove();
			}
		}
	},



	/**
	 * Get feedback warning element of comment if it exists
	 *
	 * @method	getCommentPublicFeedbackWarning
	 * @param	{Number}	idComment
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
		return !!this.getCommentPublicFeedbackWarning(idComment);
	},



	/**
	 * Get current "seen" status of given comment (for current person) and toggle it
	 *
	 * @method	toggleSeen
	 * @param	{Number}	idComment
	 * @param	{Number}	idPerson
	 */
	toggleSeen: function(idComment, idPerson) {
		var isSeen	= $('comment-' + idComment + '-seenstatus').hasClassName('isseen');

		if( !isSeen ) {
			this.setSeen(idComment, idPerson);
		} else {
			this.setUnseen(idComment, idPerson);
		}
	},



	/**
	 * Set 'seen' status of given comment false
	 *
	 * @method	setSeenStatus
	 * @param	{Number}	idComment
	 * @param	{Number}	idPerson
	 */
	setUnseen: function(idComment, idPerson) {
		var url		= Todoyu.getUrl('comment', 'task');
		var options	= {
			parameters: {
				action:		'unseen',
				comment:	idComment
			},
			onComplete: this.onUnseenSet.bind(this, idComment, idPerson)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler for setUnseenStatus
	 *
	 * @method	onSeenStatusSet
	 * @param	{Number}			idComment
	 * @param	{Number}			idPerson
	 * @param	{Ajax.Response}		response
	 */
	onUnseenSet: function(idComment, idPerson, response) {
			// Change star from grey to yellow (=seen)
		$('comment-' + idComment + '-seenstatus').removeClassName('isseen');

			// Update seen/unseen mark at person name
		var person = $('task-comment-' + idComment + '-involvedPerson-' + idPerson);
		if( person ) {
			person.down('.feedback.icon').replaceClassName('approved', 'unapproved');
		}

		this.ext.updateFeedbackTab(response.getTodoyuHeader('feedback'));
	},



	/**
	 * Set 'seen' status of given comment true
	 *
	 * @method	setSeenStatus
	 * @param	{Number}	idComment
	 * @param	{Number}	idPerson
	 */
	setSeen: function(idComment, idPerson) {
		var url		= Todoyu.getUrl('comment', 'task');
		var options	= {
			parameters: {
				action:		'seen',
				comment:	idComment
			},
			onComplete: this.onSeenSet.bind(this, idComment, idPerson)
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
	onSeenSet: function(idComment, idPerson, response) {
			// Change star from yellow to grey (=seen)
		$('comment-' + idComment + '-seenstatus').addClassName('isseen');

			// Update seen/unseen mark at person name
		var person = $('task-comment-' + idComment + '-involvedPerson-' + idPerson);
		if( person ) {
			person.down('.feedback.icon').replaceClassName('unapproved', 'approved');
		}

		this.ext.updateFeedbackTab(response.getTodoyuHeader('feedback'));
	},



	/**
	 * Remove given comment
	 *
	 * @method	remove
	 * @param	{Number}	idComment
	 */
	remove: function(idComment) {
		if( !confirm('[LLL:comment.ext.delete.confirm]') ) {
			return false;
		}

		var url		= Todoyu.getUrl('comment', 'comment');
		var options	= {
			parameters: {
				action:		'delete',
				comment:	idComment
			},
			onComplete: this.onRemoved.bind(this, idComment)
		};

		Todoyu.send(url, options);
	},




	/**
	 * Evoked after completion of removal comment request
	 *
	 * @method	onRemoved
	 * @param	{Number}			idComment
	 * @param	{Ajax.Response}		response
	 */
	onRemoved: function(idComment, response){
		var tabLabel	= response.getTodoyuHeader('tabLabel');
		var idTask		= response.getTodoyuHeader('task');

		this.ext.setTabLabel(idTask, tabLabel);

			// Fade out the removed task
		Effect.Fade($('task-comment-' + idComment), {
			duration:	0.5,
			afterFinish: function(effect) {
					// Remove element
				effect.element.remove();
					// Less than 2 comments => hide sorting buttons
				this.ext.List.toggleSortingButtons(idTask);
			}.bind(this)
		});
	},



	/**
	 * Scroll to comment
	 *
	 * @method	scrollTo
	 * @param	{Number}	idComment
	 */
	scrollTo: function(idComment) {
		$('task-comment-' + idComment).scrollToElement();
	},



	/**
	 * Add a new comment with the current comment text as template
	 *
	 * @method	quote
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 */
	quote: function(idTask, idComment) {
		this.ext.add(idTask, idComment);
	},



	/**
	 * Quote comment and add creator as mail receiver
	 *
	 * @method	mailReply
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 */
	mailReply: function(idTask, idComment) {
		this.ext.add(idTask, idComment, idComment);
	}

};