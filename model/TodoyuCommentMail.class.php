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
 * Comment sent as mail
 *
 * @package		Todoyu
 * @subpackage	Comment
 */
class TodoyuCommentMail extends TodoyuMail {

	/**
	 * Email receiver object
	 *
	 * @var	TodoyuMailReceiver
	 */
	private $mailReceiver;

	/**
	 * Tuple: 'type:ID', e.g. 'contactperson:232'
	 * Or only ID, which defaults the type to 'contactperson'
	 *
	 * @var String
	 */
	private $receiverTuple;

	/**
	 * Comment to be send
	 *
	 * @var	TodoyuCommentComment
	 */
	private $comment;


	/**
	 * Initialize comment mail
	 *
	 * @param	Integer		$idComment
	 * @param	String		$receiverTuple
	 * @param	Array		$config
	 */
	public function __construct($idComment, $receiverTuple, array $config = array()) {
		parent::__construct($config);

		$this->comment			= TodoyuCommentCommentManager::getComment($idComment);
		$this->receiverTuple	= $receiverTuple;
		$this->mailReceiver		= TodoyuMailReceiverManager::getMailReceiverObject($receiverTuple);

		$this->init();
	}



	/**
	 * Init mail
	 */
	private function init() {
		$this->setMailSubject();
		$this->setCurrentUserAsSender();
		$this->addReceiver($this->getReceiverTuple());
		$this->setHeadline('comment.ext.mail.newcomment');

		$this->setHtmlContent($this->getContent(true));
		$this->setTextContent($this->getContent(false));
	}



	/**
	 * Get mail receiver tuple ('type:ID')
	 *
	 * @return	String
	 */
	public function getReceiverTuple() {
		return $this->receiverTuple;
	}



	/**
	 * Set mail subject
	 */
	private function setMailSubject() {
		$subject	= Todoyu::Label('comment.ext.mail.subject') . ': ' . $this->comment->getTask()->getTitle() . ' (#' . $this->comment->getTask()->getTaskNumber(true) . ')';

		$this->setSubject($subject);
	}



	/**
	 * Get email content
	 *
	 * @param	Boolean		$asHtml
	 * @return	String
	 */
	private function getContent($asHtml = false) {
		$tmpl	= $this->getTemplate($asHtml);
		$data	= $this->getData();

		$data['hideEmails']	= true;

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Get template
	 *
	 * @param	Boolean		$asHtml
	 * @return	String
	 */
	private function getTemplate($asHtml = false) {
		$basePath	= 'ext/comment/view';
		$type		= $asHtml ? 'html' : 'text';
		$template	= $basePath . '/comment-mail-' . $type . '.tmpl';

		return TodoyuFileManager::pathAbsolute($template);
	}



	/**
	 * Get data to render email
	 *
	 * @return	Array
	 */
	private function getData() {
		$task			= $this->comment->getTask();
		$project		= $this->comment->getProject();
		$personWrite	= $this->comment->getPersonCreate();
		$personSend		= TodoyuAuth::getPerson();

		$data	= array(
			'comment'			=> $this->comment->getTemplateData(),
			'project'			=> $project->getTemplateData(true),
			'task'				=> $task->getTemplateData(),
//			'personReceive'		=> $this->person->getTemplateData(),
			'personWrite'		=> $personWrite->getTemplateData(),
			'personSend'		=> $personSend->getTemplateData(),
			'feedback_persons'	=> $this->comment->getFeedbackPersonsData()
		);

		$idTask	= $task->getID();

			// Add deep-link URLs for task and comment
		$data['tasklink']	= self::buildUrlForTask($idTask);
		$data['commentlink']= self::buildUrlForComment($this->comment->getID());

		return $data;
	}



	/**
	 * Build task deep-link
	 *
	 * @param	Integer		$idTask
	 * @param	Boolean		$encode
	 * @param	Boolean		$absolute
	 * @return	String
	 */
	private static function buildUrlForTask($idTask, $encode = true, $absolute = true) {
		$idTask	= intval($idTask);
		$task	= TodoyuProjectTaskManager::getTask($idTask);

		return TodoyuString::buildUrl(
			array(
				'ext'		=> 'project',
				'project'	=> $task->getProjectID(),
				'task'		=> $idTask,
			),
			'task-' . $idTask,
			$absolute,
			$encode
		);
	}



	/**
	 * Build comment deep-link
	 *
	 * @param	Integer		$idComment
	 * @param	Boolean		$encode
	 * @param	Boolean		$absolute
	 * @return	String
	 */
	private static function buildUrlForComment($idComment, $encode = true, $absolute = true) {
		$idComment	= intval($idComment);
		$comment	= TodoyuCommentCommentManager::getComment($idComment);

		return TodoyuString::buildUrl(
			array(
				'ext'		=> 'project',
				'project'	=> $comment->getProjectID(),
				'task'		=> $comment->getTaskID(),
				'tab'		=> 'comment'
			),
			'task-comment-' . $idComment,
			$absolute,
			$encode
		);
	}

}

?>