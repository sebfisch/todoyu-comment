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
 * Ext: comment
 *
 */
Todoyu.Ext.comment = {

	PanelWidget: {},


	/**
	 * Toggle customer visibility of given comment
	 *
	 * @param	Integer	idComment
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
	 * @param	Integer			idComment
	 * @param	Ajax.Response	response
	 */
	onToggledPublic: function(idComment, response) {
		$('task-comment-' + idComment).toggleClassName('isPublic');
		$('public-trigger-' + idComment).toggleClassName('comment-public');
	},



	/**
	 * Toggle 'seen' status of given comment
	 *
	 * @param	Integer	idComment
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
	 * @param	Integer			idComment
	 * @param	Integer			idPerson
	 * @param	Ajax.Response	response
	 */
	onSeenStatusSet: function(idComment, idPerson, response) {
			// Remove unseen icon
		$('comment-' + idComment + '-seenstatus').remove();
			// Remove class which marks the name unseen
		$('comment-personfeedback-' + idComment + '-' + idPerson).removeClassName('commentperson-unapproved');
	},



	/**
	 * Remove given comment
	 *
	 * @param	Integer	idComment
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
	 * @param	Integer	idTask
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
	 * @param	Integer idTask
	 * @param	String	label
	 */
	setTabLabel: function(idTask, label){
		$('task-' + idTask + '-tab-comment-label').select('.labeltext').first().update(label);
	},


	/**
	 * Check whether sorting of comments of given task is desc (true) or asc (false)
	 *
	 * @param	Integer	idTask
	 * @return	Boolean
	 */
	checkSortingIsDesc: function( idTask ) {
		var elementID	= 'task-' + idTask + '-comments';
		var isDesc = false;

		if (elementID) {
			isDesc	= $(elementID).hasClassName('desc');
		}

		return isDesc;
	},



	/**
	 * Toggle display of comment (feedbacks and mailing) log
	 *
	 * @param	Integer	idTask
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
	 * @param	Integer	idTask
	 */
	toggleSorting: function(idTask) {
		var sortingIsDesc	= this.checkSortingIsDesc(idTask);
		var desc = sortingIsDesc ? 0 : 1

		Todoyu.Ext.comment.List.refresh(idTask, desc);
	},



	/**
	 * Evoke comment editor (of given comment of given task)
	 * Note:	there is the method 'edit' and the sub object 'Edit' (case-sensitive) with its own methods
	 *
	 * @param	Integer	idTask
	 * @param	Integer	idComment
	 */
	edit: function(idTask, idComment) {
		var url		= Todoyu.getUrl('comment', 'comment');
		var options = {
			'parameters': {
				'action': 	'edit',
				'task':		idTask,
				'comment':	idComment
			}
		};
		var target;

		if( idComment === 0 ) {
				// Remove other forms
			$$('#task-' + idTask + '-tabcontent-comment .commentform').invoke('remove');

			target = 'task-' + idTask + '-comment-commands';
			options.insertion = 'after';
		} else {
			target = 'task-comment-' + idComment + '-text';
			options.onComplete = function(response) {
				$('task-' + idTask + '-commentform-' + idComment).removeClassName('taskOptionBlock');
			};
		}

		Todoyu.Ui.update(target, url, options);
	}

};