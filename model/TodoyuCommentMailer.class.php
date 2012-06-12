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
		$idComment		= intval($idComment);
		$sendStatus		= array();
		$mailReceivers	= TodoyuMailReceiverManager::getMailReceivers($receiverTuples);

		foreach($mailReceivers as $receiverTuple => $mailReceiver) {
			$isSent = self::sendMail($idComment, $mailReceiver, true);
			if( $isSent ) {
				TodoyuCommentMailManager::saveMailSent($idComment, $mailReceiver);
			}

			$sendStatus[] = array(
				'sendStatus'=> $isSent,
				'receiver'	=> $mailReceiver
			);
		}

		return $sendStatus;
	}



	/**
	 * Send comment as mail
	 *
	 * @param		Integer							$idComment
	 * @param		TodoyuMailReceiverInterface		$mailReceiver
	 * @param		Boolean 						$setCurrentUserAsSender
	 * @return		Boolean							$success
	 */
	public static function sendMail($idComment, TodoyuMailReceiverInterface $mailReceiver, $setCurrentUserAsSender=false) {
		$idComment	= intval($idComment);
		$mail		= new TodoyuCommentMail($idComment, $mailReceiver);

		if( $setCurrentUserAsSender ) {
			$mail->setCurrentUserAsSender();
		}

		TodoyuHookManager::callHook('comment', 'comment.email.send', array($idComment, $mail, $mailReceiver));

		return $mail->send();
	}

}

?>