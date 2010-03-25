<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSC License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

/**
 * Extension main file for comment extension
 *
 * @package		Todoyu
 * @subpackage	Comment
 */

	// Declare ext ID, path
define('EXTID_COMMENT', 105);
define('PATH_EXT_COMMENT', PATH_EXT . '/comment');

	// Register module locales
TodoyuLanguage::register('comment', PATH_EXT_COMMENT . '/locale/ext.xml');

	// Request configurations
require_once( PATH_EXT_COMMENT . '/config/extension.php' );
require_once( PATH_EXT_COMMENT. '/config/hooks.php' );
require_once( PATH_EXT_COMMENT . '/dwoo/plugins.php');

?>