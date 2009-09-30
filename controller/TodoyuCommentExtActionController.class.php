<?php

class TodoyuCommentExtActionController extends TodoyuActionController {
	
	public function listAction(array $params) {
		$idTask	= intval($params['task']);
		$desc	= intval($params['desc']) === 1;
		
		return TodoyuCommentRenderer::renderCommentList($idTask, $desc);
	}
	
	
	public function emailboxAction(array $params) {
		$idTask		= intval($params['task']);
		$idComment	= intval($params['comment']);
		
		return TodoyuCommentRenderer::renderEmailBox($idTask, $idComment);
	}
	
	public function togglecustomervisibilityAction(array $params) {
		$idComment	= intval($params['comment']);
		
		TodoyuCommentManager::toggleCustomerVisibility($idComment);
	}
	
	public function toggleseenstatusAction(array $params) {
		$idUser		= userid();
		$idComment	= intval($params['comment']);

		TodoyuCommentFeedbackManager::setAsSeen($idComment, $idUser);

		TodoyuHeader::sendTodoyuHeader('idComment', $idComment);
		TodoyuHeader::sendTodoyuHeader('idUser', $idUser);
		
		return 'done';
	}
		
}

?>