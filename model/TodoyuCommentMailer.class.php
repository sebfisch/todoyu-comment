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
		$idComment	= intval($idComment);
		$personIDs	= TodoyuArray::intval($personIDs, true, true);

		$succeeded	= true;
		foreach($personIDs as $idPerson) {
			$result = self::sendMail($idComment, $idPerson, false, true);

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
	 * @param	Boolean		$setSenderFromPersonMail
	 * @param	Boolean		$hideEmails					Show comment authors email addresses in message?
	 * @return	Boolean		Success
	 */
	public static function sendMail($idComment, $idPerson, $setSenderFromPersonMail = false, $hideEmails = true) {
		$idComment	= intval($idComment);
		$idPerson	= intval($idPerson);

		$comment	= TodoyuCommentCommentManager::getComment($idComment);
		$person		= TodoyuContactPersonManager::getPerson($idPerson);

			// Set mail config
		$mail			= new PHPMailerLite(true);
		$mail->Mailer	= 'mail';
		$mail->CharSet	= 'utf-8';
//
//			// Change mail program
//		if( PHP_OS !== 'Linux' ) {
//				// Windows Server: use 'mail' instead of 'sendmail'
//			$mail->Mailer	= 'mail';
//		}

			// Set "from" (sender) name and email address
		$fromName		= Todoyu::person()->getFullName() . ' (todoyu)';
		$fromAddress	= $setSenderFromPersonMail ? Todoyu::person()->getEmail() : Todoyu::$CONFIG['SYSTEM']['email'];
		$mail->SetFrom($fromAddress, $fromName);

			// Set "replyTo", "subject"
		$mail->AddReplyTo(Todoyu::person()->getEmail(), Todoyu::person()->getFullName());
		$mail->Subject	= Label('comment.mail.subject') . ': ' . $comment->getTask()->getTitle() . ' (#' . $comment->getTask()->getTaskNumber(true) . ')';

			// Add message body as HTML and plain text
		$htmlBody		= self::getMailContentHtml($idComment, $idPerson, $hideEmails);
		$textBody		= self::getMailContentText($idComment, $idPerson, $hideEmails);

		$mail->MsgHTML($htmlBody, PATH_EXT_COMMENT);
		$mail->AltBody	= $textBody;

			// Add "to" (recipient) address
		$mail->AddAddress($person->getEmail(), $person->getFullName());


//	@todo	verify
//		if( DIR_SEP !== '\\' ) {
				// Non-Windows (e.g Linux)
//			$mail->AddAddress($person->getEmail(), $person->getFullName());
//		} else {
//				// Windows
//			$mail->AddAddress($person->getEmail(), '');
//		}

		try {
			$sendStatus	= $mail->Send();
		} catch(phpmailerException $e) {
			Todoyu::log($e->getMessage(), TodoyuLogger::LEVEL_ERROR);
		} catch(Exception $e) {
			Todoyu::log($e->getMessage(), TodoyuLogger::LEVEL_ERROR);
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

		$comment		= TodoyuCommentCommentManager::getComment($idComment);

		$task			= $comment->getTask();
		$project		= $comment->getProject();

		$personReceive	= TodoyuContactPersonManager::getPerson($idPerson);
		$personWrite	= $comment->getCreatePerson();
		$personSend		= TodoyuAuth::getPerson();

		$data	= array(
			'comment'			=> $comment->getTemplateData(),
			'project' 			=> $project->getTemplateData(true),
			'task'				=> $task->getTemplateData(),
			'personReceive'		=> $personReceive->getTemplateData(),
			'personWrite'		=> $personWrite->getTemplateData(),
			'personSend'		=> $personSend->getTemplateData(),
			'feedback_persons'	=> $comment->getFeedbackPersons()
		);

		$data['tasklink'] = TodoyuString::buildUrl(array(
			'ext'		=> 'project',
			'project'	=> $project->getID(),
			'task'		=> $task->getID()
		), 'task-' . $task->getID(), true);

		$data['commentlink'] = TodoyuString::buildUrl(array(
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
	 * @param	Boolean		$hideEmails
	 * @return	String
	 */
	private static function getMailContentHtml($idComment, $idPerson, $hideEmails = true) {
		$idComment	= intval($idComment);
		$idPerson	= intval($idPerson);

		$tmpl		= 'ext/comment/view/comment-mail-html.tmpl';

		$data				= self::getMailData($idComment, $idPerson);
		$data['hideEmails']	= $hideEmails;

		return render($tmpl, $data);
	}



	/**
	 * Render content for text mail
	 *
	 * @param	Integer		$idComment		Comment to send
	 * @param	Integer		$idPerson		Person to send the email to
	 * @param	Boolean		$hideEmails		Hide comment authors' email addresses in message?
	 * @return	String
	 */
	private static function getMailContentText($idComment, $idPerson, $hideEmails = true) {
		$idComment	= intval($idComment);
		$idPerson		= intval($idPerson);

		$tmpl		= 'ext/comment/view/comment-mail-text.tmpl';
		$data				= self::getMailData($idComment, $idPerson);
		$data['hideEmails']	= $hideEmails;

		return render($tmpl, $data);
	}

}
?>