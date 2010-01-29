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
	 * Send comment information email to the users
	 *
	 * @param	Integer		$idComment
	 * @param	Array		$userIDs
	 */
	public static function sendEmails($idComment, array $userIDs) {
		$userIDs	= TodoyuArray::intval($userIDs, true, true);

		foreach($userIDs as $idUser) {
			$result = self::sendMail($idComment, $idUser);

			if ( $result !== false ) {
				TodoyuCommentMailManager::saveMailSent($idComment, $idUser);
			}
		}
	}



	/**
	 * Send a comment email to an user
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idUser
	 * @return	Boolean		Success
	 */
	public static function sendMail($idComment, $idUser) {
		$idComment	= intval($idComment);
		$idUser		= intval($idUser);
		$comment	= TodoyuCommentManager::getComment($idComment);
		$user		= TodoyuUserManager::getUser($idUser);

			// Set mail config
		$mail			= new PHPMailerLite(true);
		$mail->CharSet	= 'utf-8';
		$mail->From		= $GLOBALS['CONFIG']['EXT']['comment']['infomail']['email'];
		$mail->FromName	= $GLOBALS['CONFIG']['EXT']['comment']['infomail']['fromname'];
		$mail->Subject	= Label('comment.mail.subject') . ': ' . $comment->getTask()->getTitle() . ' (#' . $comment->getTask()->getTaskNumber(true) . ')';

		$htmlBody		= self::getMailContentHtml($idComment, $idUser);
		$textBody		= self::getMailContentText($idComment, $idUser);

		$mail->MsgHTML($htmlBody, PATH_EXT_COMMENT);
		$mail->AltBody	= $textBody;


		$mail->AddAddress($user->getEmail(), $user->getFullName());

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
	 * @param	Integer		$idUser
	 * @return	Array
	 */
	private static function getMailData($idComment, $idUser) {
		$idComment	= intval($idComment);
		$idUser		= intval($idUser);
		$comment	= TodoyuCommentManager::getComment($idComment);
		$task		= $comment->getTask();
		$project	= $comment->getProject();
		$userReceive= TodoyuUserManager::getUser($idUser);
		$userWrite	= $comment->getCreateUser();
		$userSend	= TodoyuAuth::getUser();

		$data	= array(
			'comment'		=> $comment->getTemplateData(),
			'project' 		=> $project->getTemplateData(),
			'task'			=> $task->getTemplateData(0),
			'userReceive'	=> $userReceive->getTemplateData(),
			'userWrite'		=> $userWrite->getTemplateData(),
			'userSend'		=> $userSend->getTemplateData(),
			'feedback_users'=> $comment->getFeedbackUsers()
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
	 * @param	Integer		$idUser			User to send the email to
	 * @return	String
	 */
	private static function getMailContentHtml($idComment, $idUser) {
		$idComment	= intval($idComment);
		$idUser		= intval($idUser);

		$tmpl		= 'ext/comment/view/comment-mail-html.tmpl';
		$data		= self::getMailData($idComment, $idUser);

		return render($tmpl, $data);
	}



	/**
	 * Render content for text mail
	 *
	 * @param	Integer		$idComment		Comment to send
	 * @param	Integer		$idUser			User to send the email to
	 * @return	String
	 */
	private static function getMailContentText($idComment, $idUser) {
		$idComment	= intval($idComment);
		$idUser		= intval($idUser);

		$tmpl		= 'ext/comment/view/comment-mail-text.tmpl';
		$data		= self::getMailData($idComment, $idUser);

		return render($tmpl, $data);
	}

}

?>