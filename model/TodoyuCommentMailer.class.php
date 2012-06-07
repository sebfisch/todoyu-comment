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
	 * Send comment email to the persons
	 * Store successful savings
	 *
	 * @param	Integer		$idComment
	 * @param	String[]	$emailReceivers
	 * @return	Array		ID indexed list with send status
	 */
	public static function sendEmails($idComment, array $emailReceivers) {
		$idComment		= intval($idComment);
		$sendStatus		= array();
		$sentPersonIDs	= array();

		foreach($emailReceivers as $mailReceiverID) {
			self::sendMail($idComment, $mailReceiverID, true);

				// Email receiver is a regular person ID
//			$personStatus = self::sendMailByReceiverPersonID($idComment, $emailReceiver, true);
//			$sendStatus[$emailReceiver] = $personStatus;
//
//			if( $personStatus ) {
//				$sentPersonIDs[] = $emailReceiver;
//			}


		}

//		TodoyuCommentMailManager::saveMailsSent($idComment, $sentPersonIDs);

		return $sendStatus;
	}



	/**
	 * @param		Integer				$idComment
	 * @param		String				$mailReceiverID				ID optional with registered type key prefix
	 * @param		Boolean 			$setCurrentUserAsSender
	 * @return		Boolean				$success
	 */
	public static function sendMail($idComment, $mailReceiverID, $setCurrentUserAsSender=false) {
		$idComment	= intval($idComment);

		$mail	= new TodoyuCommentMail($idComment, $mailReceiverID);

		if( $setCurrentUserAsSender ) {
			$mail->setCurrentUserAsSender();
		}

		TodoyuHookManager::callHook('comment', 'comment.email', array($idComment, $mailReceiver));

		return $mail->send();
	}

}
?>