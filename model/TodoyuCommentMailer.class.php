<?php
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
 * Send comment mails
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentMailer {

	/**
	 * Send comment information email to the persons
	 *
	 * @param	Integer		$idComment
	 * @param	Array		$personIDs
	 */
	public static function sendEmails($idComment, array $personIDs) {
		$idComment	= intval($idComment);
		$personIDs	= TodoyuArray::intval($personIDs, true, true);

		$succeeded	= true;
		foreach($personIDs as $idPerson) {
			$result = self::sendMail($idComment, $idPerson);

			if( $result === false ) {
				$succeeded	= false;
			}
		}

		return $succeeded;
	}



	/**
	 * Send a comment email to a person
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idPerson
	 * @return	Boolean		Success
	 */
	public static function sendMail($idComment, $idPerson) {
		$idComment	= intval($idComment);
		$idPerson	= intval($idPerson);

		$mail		= new TodoyuCommentMail($idComment, $idPerson);

		return $mail->send();
	}

}
?>