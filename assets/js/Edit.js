/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext: Todoyu.Ext.comment,



	/**
	 * 'Email changed' event handler
	 *
	 * @param	{Element}	checkbox
	 */
	onClickSendAsEmail: function(checkbox) {
		var parts		= checkbox.id.split('-');
		var idTask		= parts[1];
		var idComment	= parts[2];

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
	 * @param	{String}	form
	 * @return	{Boolean}
	 */
	save: function(form) {
		Todoyu.Ui.closeRTE(form);

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
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}		response
	 */
	onSaved: function(idTask, response) {
		var idComment	= response.getTodoyuHeader('idComment');

		if( response.hasTodoyuError() ) {
			$('comment-' + idTask + '-' + idComment + '-form').replace(response.responseText);
			Todoyu.notifyError('[LLL:comment.js.commentSavingFailed]');
		} else {
			Todoyu.Ext.comment.List.refresh(idTask);
			Todoyu.Ext.comment.setTabLabel(idTask, response.getTodoyuHeader('tabLabel'));
			Todoyu.notifySuccess('[LLL:comment.js.commentSaved]');

			if( response.getTodoyuHeader('sentEmail') ) {
				Todoyu.notifySuccess('[LLL:comment.js.emailSent]');
			}
		}
	},



	/**
	 * Cancel editing of comment (close comment edit box)
	 *
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
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}		response
	 */
	onRemoved: function(response){
		var tabLabel	= response.getTodoyuHeader('tabLabel');
		var idTask		= response.getTodoyuHeader('idTask');

		Todoyu.Ext.comment.setTabLabel(idTask, tabLabel);
	}

};
