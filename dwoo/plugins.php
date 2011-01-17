<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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



/**
 * @param	Dwoo_Compiler	$compiler
 * @param	String			$text
 * @return	String
 */
function Dwoo_Plugin_linkComments_compile(Dwoo_Compiler $compiler, $text) {
	return 'TodoyuCommentManager::linkCommentIDsInText(' . $text . ')';
}

?>