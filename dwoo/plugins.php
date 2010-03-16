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
 * Comment specific Dwoo plugins
 *
 * @package		Todoyu
 * @subpackage	Template
 */



/**
 * Limit maximum length of words in text (break with space character if longer)
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param 		Dwoo_Compiler 	$compiler
 * @param 		Integer			$idEventIndex
 * @return		String
 */
function Dwoo_Plugin_limitHTMLwordsLen(Dwoo $dwoo, $string, $maxLen = 45) {
	return TodoyuHtmlFilter::entitySafeLimitWordsLen($string, $maxLen);
}

?>