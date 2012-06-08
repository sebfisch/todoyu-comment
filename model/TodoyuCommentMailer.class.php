<?php
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
 * Send comment mails
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentMailer {

	/**
	 * Send comment email to given receivers. Save status to mail-log.
	 *
	 * @param	Integer		$idComment
	 * @param	String[]	$receiverTuples
	 * @return	Array
	 */
	public static function sendEmails($idComment, array $receiverTuples) {
		$idComment	= intval($idComment);
		$status		= array();

		foreach($receiverTuples as $receiverTuple) {
			$isSent = self::sendMail($idComment, $receiverTuple, true);
			if( $isSent ) {
				TodoyuCommentMailManager::saveMailSent($idComment, $receiverTuple);
			}

			$status[$receiverTuple]	= $isSent;
		}

		return $status;
	}



	/**
	 * @param		Integer				$idComment
	 * @param		String				$receiverTuple				'type:ID' or just 'ID' which sets type to default (=contactperson)
	 * @param		Boolean 			$setCurrentUserAsSender
	 * @return		Boolean				$success
	 */
	public static function sendMail($idComment, $receiverTuple, $setCurrentUserAsSender=false) {
		$idComment	= intval($idComment);

		$mail	= new TodoyuCommentMail($idComment, $receiverTuple);

		if( $setCurrentUserAsSender ) {
			$mail->setCurrentUserAsSender();
		}

		TodoyuHookManager::callHook('comment', 'comment.email', array($idComment, $receiverTuple));

		return $mail->send();
	}

}
?>