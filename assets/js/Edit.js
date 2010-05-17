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
 * Comment editing methods
 * Note:	there is the method 'edit' and the sub object 'Edit' (case-sensitive) with its own methods
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
	 * @param	{String}	field
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
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
	 * @param	{Number}	idTask
	 * @param	{Object}	response
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
	 * @param	{Number}	idTask
	 * @param	{Number}	idComment
	 */
	cancel: function(idTask, idComment) {
		$('task-' + idTask + '-commentform-' + idComment).remove();
		Todoyu.Ext.comment.List.refresh(idTask, true);
	},



	/**
	 * Evoked after completion of removing comment
	 *
	 * @param	{Number}	idTask
	 * @param	{Object}	response
	 */
	onRemoved: function(response){
		var tabLabel	=	response.getTodoyuHeader('tabLabel');
		var idTask		=	response.getTodoyuHeader('idTask');

		Todoyu.Ext.comment.setTabLabel(idTask, tabLabel);
	}
};
