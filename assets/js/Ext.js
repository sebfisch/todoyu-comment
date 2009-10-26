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
	 *	Toggle customer visibility of given comment
	 *
	 *	@param	Integer	idComment
	 */
	togglePublicStatus: function(idComment) {
		var url		= Todoyu.getUrl('comment', 'ext');
		var options	= {
			'parameters': {
				'cmd': 'togglecustomervisibility',
				'comment': idComment
			}
		};

		Todoyu.send(url , options);

		$('task-comment-' + idComment).toggleClassName('isPublic');
		$('public-trigger-' + idComment).toggleClassName('comment-public');
	},



	/**
	 *	Toggle 'seen' status of given comment
	 *
	 *	@param	Integer	idComment
	 */
	toggleSeenStatus: function(idComment)	{
		var url		= Todoyu.getUrl('comment', 'ext');
		var options	= {
			'parameters': {
				'cmd': 'toggleseenstatus',
				'comment': idComment
			},
			'onComplete': this.onToggleSeenStatus.bind(this)
		};

		Todoyu.send(url, options);
	},



	/**
	 *	Event handler: onToggleSeenStatus (evoked after toggeling of 'seen' status)
	 *
	 *	@param	Object	response
	 */
	onToggleSeenStatus: function(response)	{
		var idUser		= response.getHeader('Todoyu-idUser');
		var idComment	= response.getHeader('Todoyu-idComment');

		$('comment-' + idComment + '-seenstatus').fade();
		$('feedbackuser-' + idUser + '-' + idComment).toggleClassName('commentuser-unapproved');
	},


	/**
	 *	Remove given comment
	 *
	 *	@param	Integer	idComment
	 */
	remove: function(idComment) {
		if( ! confirm('[LLL:comment.js.deleteComment]') ) {
			return false;
		}

		var url		= Todoyu.getUrl('comment', 'comment');
		var options	= {
			'parameters': {
				'cmd': 'delete',
				'comment': idComment
			}
		};

		Todoyu.send(url, options);

		Effect.Fade($('task-comment-' + idComment), {
			'duration': 0.5,
			'from': 1,
			'to': 0
		});
	},



	/**
	 *	Add new comment to given task
	 *
	 *	@param	Integer	idTask
	 */
	addTaskComment: function(idTask) {
		Todoyu.Ext.project.Task.showDetails(idTask, 'comment');
	},



	/**
	 *	List comments of task methods
	 */
	List: {
		/**
		 *	Refresh list of comments of given task, optionally toggle sorting order
		 *
		 *	@param	Integer	idTask
		 *	@param	Integer	desc	(0 or 1)
		 */
		refresh: function(idTask, desc) {
			var url				= Todoyu.getUrl('comment', 'ext');
			var target			= 'task-' + idTask + '-tabcontent-comment';

			var options	= {
				'parameters': {
					'cmd':	'list',
					'task': idTask,
					'desc':	desc
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

	},



	/**
	 *	Check whether sorting of comments of given task is desc (true) or asc (false)
	 *
	 *	@param	Integer	idTask
	 *	@return	Boolean
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
	 *	Toggle sorting of comments of given task
	 *
	 *	@param	Integer	idTask
	 */
	toggleSorting: function(idTask) {
		var sortingIsDesc	= this.checkSortingIsDesc(idTask);
		var desc = sortingIsDesc ? 0 : 1

		Todoyu.Ext.comment.List.refresh(idTask, desc);
	},



	/**
	 *	Evoke comment editor (of given comment of given task)
	 *	Note:	there is the method 'edit' and the sub object 'Edit' (case-sensitive) with its own methods
	 *
	 *	@param	Integer	idTask
	 *	@param	Integer	idComment
	 */
	edit: function(idTask, idComment) {
		var url		= Todoyu.getUrl('comment', 'comment');
		var options = {
			'parameters': {
				'cmd': 		'edit',
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
	},


	/**
	 *	Comment editing methods
	 *	Note:	there is the method 'edit' and the sub object 'Edit' (case-sensitive) with its own methods
	 */
	Edit: {

		/**
		 *	'Email changed' event handler
		 *
		 *	@param	String	field
		 *	@param	Integer	idTask
		 *	@param	Integer	idComment
		 */
		onChangeEmail: function(idTask, idComment) {
			var checkbox= $('comment-' + idTask + '-' + idComment + '-field-sendasemail');
			var emailEl	= $('formElement-comment-' + idTask + '-' + idComment + '-field-emailreceivers'); 
			
			if( checkbox.checked ) {
				emailEl.show();
			} else {
				emailEl.hide();
			}
		},



		/**
		 *	Save comment
		 *
		 *	@param	String	form
		 *	@return	Boolean
		 */
		save: function(form) {
			tinyMCE.triggerSave();
			var idTask	= $(form).up('.task').readAttribute('id').split('-').last();

			$(form).request({
				'parameters': {
					'cmd': 'save'
				},
				'onComplete': this.onSaved.bind(this, idTask)
			});

			return false;
		},
		
		onSaved: function(idTask, response) {
			var idComment=response.getTodoyuHeader('idComment');
			
			if( response.hasTodoyuError() ) {
				$('comment-' + idTask + '-' + idComment + '-form').replace(response.responseText);
			} else {
				Todoyu.Ext.comment.List.refresh(idTask);
			}
		},
		
		/**
		 *	Cancel comment editing (close comment edit box)
		 *
		 *	@param	Integer	idTask
		 *	@param	Integer	idComment
		 */
		cancel: function(idTask, idComment) {
			$('task-' + idTask + '-commentform-' + idComment).remove();
		}
	}

};