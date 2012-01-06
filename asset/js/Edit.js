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
	 * Hook when comment form is displayed
	 *
	 * @param	{Number}	idForm
	 * @param	{String}	name
	 * @param	{Number}	idComment
	 */
	onFormDisplay: function(idForm, name, idComment) {
		this.showEmailReceiversOnCheckboxActive(idForm);
	},

	

	/**
	 * Show email receivers box when checkbox is activated in form
	 * This happens on reload when form was invalid
	 *
	 * @param	{String}	idForm
	 */
	showEmailReceiversOnCheckboxActive: function(idForm) {
		var idParts	= idForm.split('-');
		if( $(idForm).down('.fieldnameSendasemail :checkbox').checked ) {
			this.toggleEmailReceivers(idParts[1], idParts[2], true);
		}
	},



	/**
	 * Handler when changing "send as email" option checkbox
	 *
	 * @method	onClickSendAsEmail
	 * @param	{Element}	checkbox
	 */
	onClickSendAsEmail: function(checkbox) {
		var parts		= checkbox.id.split('-');
		var idTask		= parts[1];
		var idComment	= parts[2];

		this.toggleEmailReceivers(idTask, idComment, checkbox.checked);
	},



	/**
	 * Toggle the email receivers field
	 *
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 * @param	{Boolean}	show
	 */
	toggleEmailReceivers: function(idTask, idComment, show) {
		var inputDiv= $('formElement-comment-' + idTask + '-' + idComment + '-field-emailreceivers');
		var method	= show ? 'show' : 'hide';

		inputDiv[method]();
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
				action:	'save'
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
		var idComment				= response.getTodoyuHeader('idComment');
		var notificationIdentifier	= 'comment.edit.saved';

		if( response.hasTodoyuError() ) {
			$('comment-' + idTask + '-' + idComment + '-form').replace(response.responseText);
			Todoyu.notifyError('[LLL:comment.ext.js.commentSavingFailed]', notificationIdentifier);
		} else {
			Todoyu.Ext.comment.List.refresh(idTask);
			Todoyu.Ext.comment.setTabLabel(idTask, response.getTodoyuHeader('tabLabel'));
			Todoyu.notifySuccess('[LLL:comment.ext.js.commentSaved]', notificationIdentifier);

			if( response.getTodoyuHeader('sentEmail') ) {
				Todoyu.notifySuccess('[LLL:comment.ext.js.emailSent]');
			}

			Todoyu.Ext.comment.updateFeedbackTab(response.getTodoyuHeader('feedback'));
		}
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
		Todoyu.Ui.closeRTE(area);
		$(area).remove();
		Todoyu.Ext.comment.List.refresh(idTask, true);
	},



	/**
	 * Evoked after completion of removing comment
	 *
	 * @method	onRemoved
	 * @param	{Ajax.Response}		response
	 */
	onRemoved: function(response){
		var tabLabel	= response.getTodoyuHeader('tabLabel');
		var idTask		= response.getTodoyuHeader('idTask');
		var idComment	= response.getTodoyuHeader('idComment');

		Todoyu.Ext.comment.setTabLabel(idTask, tabLabel);

			// Fade out the removed task
		Effect.Fade($('task-comment-' + idComment), {
			'duration':		0.5,
			'from':			1,
			'to':			0,
			'afterFinish':	function(effect) {
					// Less than 2 comments left? remove button to toggle sorting
				var tabContentElement		= effect.element.up('div.tabContent');
				var commentsContainerElement= tabContentElement.down('.task-comments');

				effect.element.remove();
				var amountComments		= commentsContainerElement.select('li').length;

				if( amountComments < 2 ) {
					var button	= tabContentElement.select('button.reverseOrder')[0];
					if( button ) {
						button.hide();
					}
				}
			}
		});
	}

};
