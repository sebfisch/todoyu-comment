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

/**
 * Page configuration for commnet extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */

$CONFIG['EXT']['comment']['assets'] = array(
	'js' => array(
		array(
			'file'		=> 'ext/comment/assets/js/Ext.js',
			'position'	=> 100
		),
		array(
			'file'		=> 'ext/comment/assets/js/List.js',
			'position'	=> 101
		),
		array(
			'file'		=> 'ext/comment/assets/js/Edit.js',
			'position'	=> 102
		)
	),
	'css' => array(
		array(
			'file'		=> 'ext/comment/assets/css/global.css',
			'position'	=> 100
		),
		array(
			'file'		=> 'ext/comment/assets/css/ext.css',
			'position'	=> 100
		)
	)
);

?>