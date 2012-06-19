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
 * @package		Todyou
 * @subpackage	Comment
 */
class TodoyuCommentAssetManager {

	/**
	 *
	 */
	const TABLE = 'ext_comment_mm_comment_asset';



	/**
	 * @static
	 * @param	TodoyuFormElement		$field
	 * @return	Array
	 */
	public static function getCommentAssetOptions(TodoyuFormElement $field){
		$formData	= $field->getForm()->getFormData();

		$idComment	= intval($formData['id']);
		$idTask		= intval($formData['id_task']);
		$idProject	= TodoyuProjectTaskManager::getProjectID($idTask);
		$options	= array();

		$groups = array(
			'project' => array(
				'id'			=> $idProject,
				'optGroupLabel'	=> Todoyu::Label('comment.ext.assets.optgroup.project'),
				'type'			=> ASSET_PARENTTYPE_PROJECT
			),
			'task' => array(
				'id'			=> $idTask,
				'optGroupLabel'	=> Todoyu::Label('comment.ext.assets.optgroup.task'),
				'type'			=> ASSET_PARENTTYPE_TASK
			),
			'comment' => array(
				'id'			=> $idComment,
				'optGroupLabel'	=> Todoyu::Label('comment.ext.assets.optgroup.comment'),
				'type'			=> ASSET_PARENTTYPE_COMMENT
			)
		);

		foreach( $groups as $group) {
			$assets = TodoyuAssetsAssetManager::getElementAssets($group['id'], $group['type']);

			foreach($assets as $key => $asset) {
				$assets[$key]['group'] = $group['optGroupLabel'];
			}

			$options = array_merge($options, TodoyuArray::reform($assets, array('id' => 'value', 'file_name' => 'label', 'group' => 'group')));
		}

		$tempFiles		= new TodoyuCommentTempUploader($idComment, $idTask);
		$fileInfos		= $tempFiles->getFilesInfos();

		foreach($fileInfos as $file) {
			$options[] = array(
				'value'	=> $file['key'],
				'label'	=> self::getTempFileLabel($file),
				'group'	=> $groups['comment']['optGroupLabel']
			);
		}

		return $options;
	}



	/**
	 * @static
	 * @param	Array		$file
	 * @return	String
	 */
	public static function getTempFileLabel(array $file) {
		return $file['name'] . ' (' . TodoyuTime::format($file['time'], 'timesec') . ', ' . TodoyuString::formatSize($file['size']) . ')';
	}



	/**
	 * @static
	 * @param	TodoyuFormElement		$field
	 * @return	Array
	 */
	public static function getCommentAssetRecords(TodoyuFormElement $field){
		$formData	= $field->getForm()->getFormData();

		$idComment	= intval($formData['id']);
		$idTask		= intval($formData['id_task']);

		$tempUploader = new TodoyuCommentTempUploader($idComment, $idTask);

		$assetIDs = $field->getValue();
		$records	= array();

		foreach($assetIDs as $idAsset) {
			if( is_numeric($idAsset) ) {
				$info = array(
					'id'	=> $idAsset,
					'label'	=> TodoyuAssetsAssetManager::getAsset($idAsset)->getFilename()
				);
			} else {
				$file = $tempUploader->getFileInfo($idAsset);
				$info = array(
					'id'	=> $idAsset,
					'label'	=> self::getTempFileLabel($file)
				);
			}

			$records[] = $info;
		}

		return $records;
	}



	/**
	 * @static
	 * @param	Integer		$idCommentOld
	 * @param	Integer		$idCommentNew
	 * @param	Integer		$idTask
	 * @param	Array		$assets
	 */
	public static function saveAssets($idCommentOld, $idCommentNew, $idTask, array $assets) {
		self::removeAllAssets($idCommentNew);

		foreach($assets as $idAsset) {
			if(!is_numeric($idAsset)) {
				$idAsset	= self::saveNewAsset($idCommentOld, $idCommentNew, $idTask, $idAsset);
			}

			$data = array(
				'date_create'		=> NOW,
				'date_update'		=> NOW,
				'id_person_create'	=> Todoyu::personid(),
				'id_asset'			=> $idAsset,
				'id_comment'		=> $idCommentNew
			);

			Todoyu::db()->doInsert(self::TABLE, $data);
		}
	}



	/**
	 * @static
	 * @param	Integer		$idCommentOld
	 * @param	Integer		$idCommentNew
	 * @param	Integer		$idTask
	 * @param	Mixed		$idAsset
	 * @return	Integer
	 */
	protected static function saveNewAsset($idCommentOld, $idCommentNew, $idTask, $idAsset) {
		$uploader	= new TodoyuCommentTempUploader($idCommentOld, $idTask);
		$fileInfo	= $uploader->getFileInfo($idAsset);

		return TodoyuAssetsAssetManager::addAsset(ASSET_PARENTTYPE_COMMENT, $idCommentNew, $fileInfo['path'], $fileInfo['name'], $fileInfo['type']);
	}



	/**
	 * @static
	 * @param	Integer		$idCommentNew
	 */
	protected static function removeAllAssets($idCommentNew) {
		Todoyu::db()->doDelete(self::TABLE, 'id_comment = ' . intval($idCommentNew));
	}



	/**
	 * @static
	 * @param	Array		$assetIDs
	 */
	public static function loadAssetTemplateData($assetIDs) {
		$assets = array();

		foreach($assetIDs as $idAsset) {
			if( TodoyuAssetsRights::isSeeAllowed($idAsset['id'])) {
				$assets[] = TodoyuAssetsAssetManager::getAsset($idAsset['id']);
			}
		}

		return $assets;
	}
}

?>