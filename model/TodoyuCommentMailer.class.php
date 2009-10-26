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


require_once( PATH_LIB . '/php/phpmailer/class.phpmailer.php' );

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
			$result = self::sendCommentEmail($idComment, $idUser);
		}
	}



	/**
	 * Send a comment email to an user
	 *
	 * @param	Integer		$idComment
	 * @param	Integer		$idUser
	 * @return	Boolean		Success
	 */
	private static function sendCommentEmail($idComment, $idUser) {
		$idComment	= intval($idComment);
		$idUser		= intval($idUser);
		$comment	= TodoyuCommentManager::getComment($idComment);
		$user		= TodoyuUserManager::getUser($idUser);

		$bodyText	= self::getMailContent($idComment, $idUser);


		$mail			= new PHPMailer();
		$mail->CharSet	= 'utf-8';
		$mail->From		= $GLOBALS['CONFIG']['EXT']['comment']['infomail']['email'];
		$mail->FromName	= $GLOBALS['CONFIG']['EXT']['comment']['infomail']['fromname'];
		$mail->Subject	= Label('comment.infomail.subject') . ': ' . $comment->getTask()->getTitle();
		$mail->AltBody	= "To view the message, please use an HTML compatible email viewer!";

		$mail->MsgHTML($bodyText, PATH_EXT_COMMENT);

		$mail->AddAddress($user->getEmail(), $user->getFullName());

		return $mail->Send();
	}



	/**
	 * Render content for infomail
	 *
	 * @param	Integer		$idComment		Comment to send
	 * @param	Integer		$idUser			User to send the email to
	 * @return	String
	 */
	private static function getMailContent($idComment, $idUser) {
		$idComment	= intval($idComment);
		$idUser		= intval($idUser);
		$comment	= TodoyuCommentManager::getComment($idComment);
		$task		= $comment->getTask();
		$project	= $comment->getProject();
		$user		= TodoyuUserManager::getUser($idUser);


		$tmpl	= 'ext/comment/view/comment-mail.tmpl';
		$data	= array(
			'comment'	=> $comment->getTemplateData(),
			'project' 	=> $project->getTemplateData(),
			'task'		=> $task->getTemplateData(0),
			'user'		=> $user->getTemplateData(),
			'fbusers'	=> $comment->getFeedbackUsers()
		);

		$data['tasklink']	= SERVER_URL . '/?ext=project&project=' . $project->getID() . '&amp;task=' . $task->getID();
		$data['commentlink']= SERVER_URL . '/?ext=project&project=' . $project->getID() . '&amp;task=' . $task->getID() . '&amp;tab=comment#task-comment-' . $comment->getID();

		return render($tmpl, $data);
	}

}

?>