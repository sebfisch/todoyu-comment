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


Todoyu::$CONFIG['FILTERS']['TASK']['widgets']['unseenFeedback'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_unseenFeedback',
	'label'		=> 'LLL:comment.filter.unseenFeedback',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'checkbox',
	'wConf'		=> array(
		'checked'	=> true
	)
);

/**
 * Unseen comment feedback request for person
 */
Todoyu::$CONFIG['FILTERS']['TASK']['widgets']['unseenFeedbackPerson'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_unseenFeedbackPerson',
	'label'		=> 'LLL:comment.filter.unseenFeedbackPerson',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'text',
	'wConf' => array(
		'autocomplete'	=> true,
		'FuncRef'		=> 'TodoyuContactPersonFilterDataSource::autocompletePersons',
		'FuncParams'	=> array(),
		'LabelFuncRef'	=> 'TodoyuContactPersonFilterDataSource::getLabel',
		'negation'	=> 'default'
	)
);

Todoyu::$CONFIG['FILTERS']['TASK']['filters']['unseenFeedbackCurrentPerson'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_unseenFeedbackCurrentPerson'
);

Todoyu::$CONFIG['FILTERS']['TASK']['widgets']['unseenFeedbackRoles'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_unseenFeedbackRoles',
	'label'		=> 'LLL:comment.filter.unseenFeedbackRoles',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'select',
	'wConf'		=> array(
		'multiple'	=> true,
		'size'		=> 5,
//		'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getRoleOptions'
		'FuncRef'	=> 'TodoyuRoleDatasource::getRoleOptions'
	)
);



/**
 * Comment full-text search
 */
Todoyu::$CONFIG['FILTERS']['TASK']['widgets']['commentFulltext'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_fulltext',
	'label'		=> 'LLL:comment.filter.commentFulltext',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'text',
	'wConf' => array(
		'autocomplete'	=> false,
		'LabelFuncRef'	=> 'TodoyuContactPersonFilterDataSource::getLabel',
		'negation'	=> 'default'
	)
);


Todoyu::$CONFIG['FILTERS']['TASK']['widgets']['commentWrittenPerson'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_commentWrittenPerson',
	'label'		=> 'LLL:comment.filter.commentWrittenPerson',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'text',
	'wConf' => array(
		'autocomplete'	=> true,
		'FuncRef'		=> 'TodoyuContactPersonFilterDataSource::autocompletePersons',
		'FuncParams'	=> array(),
		'LabelFuncRef'	=> 'TodoyuContactPersonFilterDataSource::getLabel',
		'negation'		=> 'default'
	)
);

Todoyu::$CONFIG['FILTERS']['TASK']['widgets']['commentWrittenRoles'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_commentWrittenRoles',
	'label'		=> 'LLL:comment.filter.commentWrittenRoles',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'select',
	'wConf'		=> array(
		'multiple'	=> true,
		'size'		=> 5,
		'FuncRef'	=> 'TodoyuRoleDatasource::getRoleOptions'
	)
);

?>