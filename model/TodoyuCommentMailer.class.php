<?php
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

	// Include mail library
require_once( PATH_LIB . '/php/phpmailer/class.phpmailer-lite.php' );

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
		$personIDs	= TodoyuArray::intval($personIDs, true, true);

		foreach($personIDs as $idPerson) {
			$result = self::sendMail($idComment, $idPerson);

			if ( $result !== false ) {
				TodoyuCommentMailManager::saveMailSent($idComment, $idPerson);
			}
		}
	}



	/**
	 * Send a comment email to an person
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idPerson
	 * @return	Boolean		Success
	 */
	public static function sendMail($idComment, $idPerson) {
		$idComment	= intval($idComment);
		$idPerson	= intval($idPerson);

		$comment	= TodoyuCommentManager::getComment($idComment);
		$person		= TodoyuPersonManager::getPerson($idPerson);

			// Set mail config
		$mail			= new PHPMailerLite(true);
		$mail->CharSet	= 'utf-8';
		$mail->From		= $GLOBALS['CONFIG']['EXT']['comment']['infomail']['email'];
		$mail->FromName	= $GLOBALS['CONFIG']['EXT']['comment']['infomail']['fromname'];
		$mail->Subject	= Label('comment.mail.subject') . ': ' . $comment->getTask()->getTitle() . ' (#' . $comment->getTask()->getTaskNumber(true) . ')';

		$htmlBody		= self::getMailContentHtml($idComment, $idPerson);
		$textBody		= self::getMailContentText($idComment, $idPerson);

		$mail->MsgHTML($htmlBody, PATH_EXT_COMMENT);
		$mail->AltBody	= $textBody;

		$mail->AddAddress($person->getEmail(), $person->getFullName());

		try {
			$sendStatus	= $mail->Send();
		} catch(phpmailerException $e) {
			Todoyu::log($e->getMessage(), LOG_LEVEL_ERROR);
			echo $e->getMessage()."\n";
		}

		return $sendStatus;
	}



	/**
	 * Get data array to render email
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idPerson
	 * @return	Array
	 */
	private static function getMailData($idComment, $idPerson) {
		$idComment		= intval($idComment);
		$idPerson		= intval($idPerson);

		$comment		= TodoyuCommentManager::getComment($idComment);

		$task			= $comment->getTask();
		$project		= $comment->getProject();

		$personReceive	= TodoyuPersonManager::getPerson($idPerson);
		$personWrite	= $comment->getCreatePerson();
		$personSend		= TodoyuAuth::getPerson();

		$data	= array(
			'comment'			=> $comment->getTemplateData(),
			'project' 			=> $project->getTemplateData(),
			'task'				=> $task->getTemplateData(0),
			'personReceive'		=> $personReceive->getTemplateData(),
			'personWrite'		=> $personWrite->getTemplateData(),
			'personSend'		=> $personSend->getTemplateData(),
			'feedback_persons'	=> $comment->getFeedbackPersons()
		);

		$data['tasklink'] = TodoyuDiv::buildUrl(array(
			'ext'		=> 'project',
			'project'	=> $project->getID(),
			'task'		=> $task->getID()
		), 'task-' . $task->getID(), true);

		$data['commentlink'] = TodoyuDiv::buildUrl(array(
			'ext'		=> 'project',
			'project'	=> $project->getID(),
			'task'		=> $task->getID(),
			'tab'		=> 'comment'
		), 'task-comment-' . $comment->getID(), true);

		return $data;
	}



	/**
	 * Render content for HTML mail
	 *
	 * @param	Integer		$idComment		Comment to send
	 * @param	Integer		$idPerson		Person to send the email to
	 * @return	String
	 */
	private static function getMailContentHtml($idComment, $idPerson) {
		$idComment	= intval($idComment);
		$idPerson	= intval($idPerson);

		$tmpl		= 'ext/comment/view/comment-mail-html.tmpl';
		$data		= self::getMailData($idComment, $idPerson);

		return render($tmpl, $data);
	}



	/**
	 * Render content for text mail
	 *
	 * @param	Integer		$idComment		Comment to send
	 * @param	Integer		$idPerson		Person to send the email to
	 * @return	String
	 */
	private static function getMailContentText($idComment, $idPerson) {
		$idComment	= intval($idComment);
		$idPerson		= intval($idPerson);

		$tmpl		= 'ext/comment/view/comment-mail-text.tmpl';
		$data		= self::getMailData($idComment, $idPerson);

		return render($tmpl, $data);
	}

}

?>