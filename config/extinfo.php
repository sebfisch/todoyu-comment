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
 * Various comment extension info data
 */

Todoyu::$CONFIG['EXT']['comment']['info'] = array(
	'title'			=> 'Task Comment Management',
	'description' 	=> 'Task Comment Management',
	'author' 		=> array(
		'name'		=> 'todoyu Core Developer Team',
		'email'		=> 'team@todoyu.com',
		'company'	=> 'snowflake productions GmbH, Zurich'
	),
	'state' 		=> 'beta',
	'version'		=> '0.2.0',
	'constraints'	=> array(
		'depends'	=> array(
			'project' 	=> '0.2.0',
		),
		'conflicts' => array(
		)
	)
);

?>