<?php

class TodoyuCommentCommentActionController extends TodoyuActionController {

	public function updateAction(array $params) {
		$time	= intval($params['time']);
		$tab	= $params['tab'];

		PanelWidgetCalendar::saveDate($time);
		TodoyuCalendarPreferences::saveActiveTab($tab);

		return TodoyuCalendarRenderer::renderCalendar($time, $tab);
	}

	public function editAction(array $params) {
		$idTask		= intval($params['task']);
		$idComment	= intval($params['comment']);

		return TodoyuCommentRenderer::renderEdit($idTask, $idComment);
	}

	public function deleteAction(array $params) {
		$idComment	= intval($params['comment']);

		TodoyuCommentManager::deleteComment($idComment);
	}


	public function saveAction(array $params) {
		$xmlPath	= 'ext/comment/config/form/comment.xml';
		$data		= $params['comment'];
		$idComment	= intval($data['id']);

		$form		= TodoyuFormManager::getForm($xmlPath, $idComment);

		$form->setFormData($data);

		if( $form->isValid() ) {
			$data	= $form->getStorageData();

			TodoyuCommentManager::saveComment($data);
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();
			TodoyuHeader::sendTodoyuHeader('idComment', $idComment);

			return $form->render();
		}
	}

}

?>