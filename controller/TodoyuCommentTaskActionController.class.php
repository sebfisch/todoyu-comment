<?php

class TodoyuCommentTaskActionController extends TodoyuActionController {

	public function listAction(array $params) {
		$idTask	= intval($params['task']);
		$desc	= intval($params['desc']) === 1;

		return TodoyuCommentRenderer::renderCommentList($idTask, $desc);
	}


	public function togglecustomervisibilityAction(array $params) {
		$idComment	= intval($params['comment']);

		TodoyuCommentManager::toggleCustomerVisibility($idComment);
	}

	public function seenAction(array $params) {
		$idUser		= userid();
		$idComment	= intval($params['comment']);

		TodoyuCommentFeedbackManager::setAsSeen($idComment, $idUser);

		TodoyuHeader::sendTodoyuHeader('idComment', $idComment);
		TodoyuHeader::sendTodoyuHeader('idUser', $idUser);

		return 'done';
	}

}

?>