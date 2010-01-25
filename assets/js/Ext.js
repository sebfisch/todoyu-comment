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
	setSeenStatus: function(idComment, idUser)	{
		var url		= Todoyu.getUrl('comment', 'task');
		var options	= {
			'parameters': {
				'action':	'seen',
				'comment':	idComment
			},
			'onComplete': this.onSeenStatusSet.bind(this, idComment, idUser)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler for setSeenStatus
	 *
	 * @param	Integer			idComment
	 * @param	Integer			idUser
	 * @param	Ajax.Response	response
	 */
	onSeenStatusSet: function(idComment, idUser, response) {
			// Remove unseen icon
		$('comment-' + idComment + '-seenstatus').remove();
			// Remove class which marks the name unseen
		$('task-comment-' + idComment + '-feedbackuser-' + idUser).removeClassName('commentuser-unapproved');
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
		Todoyu.Ext.project.Task.showDetails(idTask, 'comment');
	},



	/**
	 * Set Label (on adding or removing comment)
	 *
	 * @param	Integer idTask
	 * @param	String	label
	 */
	setTabLabel: function(idTask, label){
		$('task-' + idTask + '-tabhead-comment-label').select('.labeltext').first().update(label);
	},



	/**
	 *	List comments of task methods
	 */
	List: {
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

//				Todoyu.send(url, options);
				Todoyu.Ui.update(logDiv, url, options);
			}
			logDiv.show();
		} else {
			logDiv.hide();
		}

		this.updateToggleLogIcon(idComment);
//		this.saveLogExpanded(idProject, detailDiv.visible());
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
	},



	/**
	 * Comment editing methods
	 * Note:	there is the method 'edit' and the sub object 'Edit' (case-sensitive) with its own methods
	 */
	Edit: {

		/**
		 * 'Email changed' event handler
		 *
		 * @param	String	field
		 * @param	Integer	idTask
		 * @param	Integer	idComment
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
		 * Save comment
		 *
		 * @param	String	form
		 * @return	Boolean
		 */
		save: function(form) {
			tinyMCE.triggerSave();
			var idTask	= $(form).up('.task').readAttribute('id').split('-').last();

			$(form).request({
				'parameters': {
					'action':	'save'
				},
				'onComplete': this.onSaved.bind(this, idTask)
			});

			return false;
		},



		/**
		 * Evoked after completion of saving comment
		 *
		 * @param	Integer	idTask
		 * @param	Object	response
		 */
		onSaved: function(idTask, response) {
			var idComment	=	response.getTodoyuHeader('idComment');
			var tabLabel	=	response.getTodoyuHeader('tabLabel');

			if( response.hasTodoyuError() ) {
				$('comment-' + idTask + '-' + idComment + '-form').replace(response.responseText);
				Todoyu.notifyError('[LLL:comment.js.commentSavingFailed]');
			} else {
				Todoyu.Ext.comment.List.refresh(idTask);
				Todoyu.Ext.comment.setTabLabel(idTask, tabLabel);
				Todoyu.notifySuccess('[LLL:comment.js.commentSaved]');

				if( response.getTodoyuHeader('sentEmail') ) {
					Todoyu.notifySuccess('[LLL:comment.js.emailSent]');
				}
			}
		},



		/**
		 * Cancel editing of comment (close comment edit box)
		 *
		 * @param	Integer	idTask
		 * @param	Integer	idComment
		 */
		cancel: function(idTask, idComment) {
			$('task-' + idTask + '-commentform-' + idComment).remove();
			Todoyu.Ext.comment.List.refresh(idTask, true);
		},



		/**
		 * Evoked after completion of removing comment
		 *
		 * @param	Integer	idTask
		 * @param	Object	response
		 */
		onRemoved: function(response){
			var tabLabel	=	response.getTodoyuHeader('tabLabel');
			var idTask		=	response.getTodoyuHeader('idTask');

			Todoyu.Ext.comment.setTabLabel(idTask, tabLabel);
		}
	}

};