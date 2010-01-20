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

//require_once( PATH_EXT_COMMENT . '/config/search.php' );

?>