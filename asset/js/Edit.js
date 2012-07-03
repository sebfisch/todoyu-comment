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
 * Comment editing
 *
 * @class		Edit
 * @namespace	Todoyu.Ext.comment
 */
Todoyu.Ext.comment.Edit = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext: Todoyu.Ext.comment,



	/**
	 * Evoke comment editor (of given comment of given task)
	 * Note:	there is the method 'edit' and the sub object 'Edit' (case-sensitive) with its own methods
	 *
	 * @method	edit
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 */
	editComment: function(idTask, idComment) {
		var url		= Todoyu.getUrl('comment', 'comment');
		var options = {
			parameters: {
				action: 	'edit',
				task:		idTask,
				comment:	idComment
			},
			onComplete:	this.onCommentEdit.bind(this, idTask, idComment)
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
	onCommentEdit: function(idTask, idComment, response) {
//		$('task-' + idTask + '-commentform-' + idComment).removeClassName('taskOptionBlock');
	},




	/**
	 * Hook when comment form is displayed
	 *
	 * @method	onFormDisplay
	 * @param	{String}		idForm
	 * @param	{String}		name
	 * @param	{Number|String}	recordID
	 */
	onFormDisplay: function(idForm, name, recordID) {
		var parts		= recordID.split('-');
		var idComment	= parts[1];
		var idTask		= parts[0];

		this.showEmailReceiversOnCheckboxActive(idTask, idComment);

		Todoyu.Hook.exec('comment.comment.edit', idComment, idTask);
	},



	/**
	 * Show email receivers box when checkbox is activated in form
	 * This happens on reload when form was invalid
	 *
	 * @method	showEmailReceiversOnCheckboxActive
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 */
	showEmailReceiversOnCheckboxActive: function(idTask, idComment) {
		if( this.isMailActive(idTask, idComment) ) {
			this.toggleEmailReceivers(idTask, idComment, true);
		}
	},



	/**
	 * Check whether send mail option is enabled
	 *
	 * @method	isMailActive
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 * @return	{Boolean}
	 */
	isMailActive: function(idTask, idComment) {
		var checkbox = $('comment-' + idTask + '-' + idComment + '-field-sendasemail');

		return checkbox && checkbox.checked;
	},



	/**
	 * Handler when changing "send as email" option checkbox
	 *
	 * @method	onMailCheckboxToggle
	 * @param	{Element}	checkbox
	 */
	onMailCheckboxToggle: function(checkbox) {
		var parts		= checkbox.id.split('-');
		var idTask		= parts[1];
		var idComment	= parts[2];

		this.toggleEmailReceivers(idTask, idComment, checkbox.checked);
	},



	/**
	 * Toggle the email receivers field
	 *
	 * @method	toggleEmailReceivers
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 * @param	{Boolean}	show
	 */
	toggleEmailReceivers: function(idTask, idComment, show) {
		var form	= $('comment-' + idTask + '-' + idComment + '-form');
		var method	= show ? 'show' : 'hide';

		if( form ) {
			form.select('.mailToggle').invoke(method);
		}
	},



	/**
	 * Save comment
	 *
	 * @method	save
	 * @param	{String}	form
	 * @return	{Boolean}
	 */
	save: function(form) {
		Todoyu.Ui.closeRTE(form);

		var idTask	= $(form).up('.task').readAttribute('id').split('-').last();

		$(form).request({
			parameters: {
				action:	'save',
				area:	Todoyu.getArea()
			},
			onComplete: this.onSaved.bind(this, idTask)
		});

		return false;
	},



	/**
	 * Evoked after completion of saving comment
	 *
	 * @method	onSaved
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}		response
	 */
	onSaved: function(idTask, response) {
		var idComment				= response.getTodoyuHeader('comment');
		var notificationIdentifier	= 'comment.edit.saved';

		if( response.hasTodoyuError() ) {
			$('comment-' + idTask + '-' + idComment + '-form').replace(response.responseText);
			Todoyu.notifyError('[LLL:comment.ext.js.commentSavingFailed]', notificationIdentifier);
		} else {
			Todoyu.Ext.comment.List.refresh(idTask);
			Todoyu.Ext.comment.setTabLabel(idTask, response.getTodoyuHeader('tabLabel'));
			Todoyu.notifySuccess('[LLL:comment.ext.js.commentSaved]', notificationIdentifier);

			if( response.hasTodoyuHeader('feedback') ) {
				this.notifyFeedbackPersons(response.getTodoyuHeader('feedback'));
			}
			if( response.hasTodoyuHeader('emailStatus') ) {
				this.notifyEmailSendStatus(response.getTodoyuHeader('emailStatus'));
			}
			if( Todoyu.isInArea('portal') ) {
				this.ext.updateFeedbackTab(response.getTodoyuHeader('openFeedbackCount'));
			}

			Todoyu.Hook.exec('comment.comment.saved', idComment, idTask);
		}
	},



	/**
	 * Show notification about the persons from which feedback is requested
	 *
	 * @method	notifyFeedbackPersons
	 * @param	{Array}		feedbackPersons
	 */
	notifyFeedbackPersons: function(feedbackPersons) {
		var names = this.extractNames(feedbackPersons).join(', ');

		Todoyu.notifyInfo('[LLL:comment.ext.js.requestedFeedbackFrom]' + ' ' + names);
	},



	/**
	 * Notify about email status
	 *
	 * @method	notifyEmailSendStatus
	 * @param	{Array}		emailSendStatus
	 */
	notifyEmailSendStatus: function(emailSendStatus) {
		var names;
		var allOk = emailSendStatus.all(function(person){
			return person.status;
		});

		if( allOk ) {
			names = emailSendStatus.collect(function(person){
				return person.name;
			}).join(', ');

			Todoyu.notifyInfo('[LLL:comment.ext.js.sentEmailsTo]' + ' ' + names);
		} else {
			var ok = emailSendStatus.findAll(function(person){
				return this.status;
			});
			var fail = emailSendStatus.findAll(function(person){
				return !this.status;
			});

			if( ok.size() ) {
				names = this.extractNames(ok).join(', ');
				Todoyu.notifyInfo('[LLL:comment.ext.js.emailSent]: ' + names);
			} else {
				fail.each(function(person) {
					Todoyu.notifyError('[LLL:comment.ext.js.emailSent.fail]: ' + person.name);
				});
			}
		}
	},



	/**
	 * Extract names out of person objects
	 *
	 * @method	extractNames
	 * @param	{Object}	personObjects
	 */
	extractNames: function(personObjects) {
		return personObjects.collect(function(person){
			return person.name;
		});
	},



	/**
	 * Cancel editing of comment (close comment edit box)
	 *
	 * @method	cancel
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 */
	cancel: function(idTask, idComment) {
		var area = 'task-' + idTask + '-commentform-' + idComment;
		this.clearTempUploads(idComment, idTask);
		Todoyu.Ui.closeRTE(area);
		$(area).remove();

		this.ext.List.refresh(idTask);
	},



	/**
	 * Upload asset file
	 *
	 * @method	uploadFileInline
	 * @param	{Element}	field
	 */
	uploadFileInline: function(field) {
		if( $F(field) !== '' ) {
			var url	= Todoyu.getUrl('comment', 'comment', {
				action:		'uploadassetfile'
			});

			Todoyu.Form.submitFileUploadForm(field.form, url);
		}
	},


	/**
	 *
	 * @param	{Integer}		idComment
	 * @param	{Integer}		idTask
	 * @param	{String}		filename
	 * @param	{String}		filekey
	 */
	uploadFinished: function(idComment, idTask, filename, filekey) {
		this.refreshFileSelectorOptions(idComment, idTask);
		this.addSelectorItem(idComment, idTask, filename, filekey);

		Todoyu.notifySuccess('[LLL:core.file.upload.succeeded]', 'fileupload');
	},



	/**
	 *
	 * @param	{Integer}		idTask
	 * @param	{String}		filename
	 * @param	{Integer}		maxFileSize
	 */
	uploadFailed: function(idTask, filename, maxFileSize) {
		var info	= {
			filename:		filename,
			maxFileSize:	maxFileSize,
			id_task:		idTask
		};
		var msg		= '';

		if( error === 1 || error === 2 ) {
			msg	= '[LLL:core.file.upload.failed.maxFileSizeExceeded]';
		} else {
			msg	= '[LLL:core.file.upload.error.uploadfailed]';
		}

		Todoyu.notifyError(msg.interpolate(info), 'fileupload');
	},



	/**
	 *
	 * @param	{Integer}		idComment
	 */
	refreshFileSelectorOptions: function(idComment, idTask) {
		var idElement = 'comment-' + idTask + '-' + idComment + '-field-assets-search';
		var target		= $(idElement);

		var url		= Todoyu.getUrl('comment', 'comment');
		var options	= {
			parameters: {
				action:		'refreshfileselector',
				task:		idTask,
				comment:	idComment
			}
		};

		Todoyu.Ui.update(target, url, options);
	},


	/**
	 * @param	{Integer}		idComment
	 * @param	{Integer}		idTask
	 * @param	{String}		filename
	 * @param	{String}		filekey
	 */
	addSelectorItem: function(idComment, idTask, filename, filekey) {
		Todoyu.R['recordselectasset-comment-' + idTask +'-' + idComment + '-field-assets'].addSelectedItem(filekey, filename, filename);
	},



	/**
	 *
	 * @param	{Integer}		idComment
	 * @param	{Integer}		idTask
	 */
	clearTempUploads: function(idComment, idTask) {
		var url		= Todoyu.getUrl('comment', 'comment');
		var options	= {
			parameters: {
				action:		'cleartempuploads',
				task:		idTask,
				comment:	idComment
			}
		};

		Todoyu.send(url, options);
	}
};
