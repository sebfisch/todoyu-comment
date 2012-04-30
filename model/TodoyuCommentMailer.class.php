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
	 * @param	Array		$personIDs
	 * @return	Array		ID indexed list with send status
	 */
	public static function sendEmails($idComment, array $personIDs) {
		$idComment		= intval($idComment);
		$personIDs		= TodoyuArray::intval($personIDs, true, true);
		$sendStatus		= array();
		$sentPersonIDs	= array();

		foreach($personIDs as $idPerson) {
			$personStatus = self::sendMail($idComment, $idPerson, true);

			$sendStatus[$idPerson] = $personStatus;

			if( $personStatus ) {
				$sentPersonIDs[] = $idPerson;
			}
		}

		TodoyuCommentMailManager::saveMailsSent($idComment, $sentPersonIDs);

		return $sendStatus;
	}



	/**
	 * Send a comment email to a person
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idPerson
	 * @param	Boolean		$setCurrentUserAsSender
	 * @return	Boolean		Success
	 */
	public static function sendMail($idComment, $idPerson, $setCurrentUserAsSender=false) {
		$idComment	= intval($idComment);
		$idPerson	= intval($idPerson);

		$mail		= new TodoyuCommentMail($idComment, $idPerson);

		if( $setCurrentUserAsSender ) {
			$mail->setCurrentUserAsSender();
		}

		TodoyuHookManager::callHook('comment', 'comment.email', array($idComment, $idPerson));

		return $mail->send();
	}

}
?>