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
 * Unseen comment feedback request for person
 */
Todoyu::$CONFIG['FILTERS']['TASK']['widgets']['unseenFeedbackPerson'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_unseenFeedbackPerson',
	'label'		=> 'LLL:comment.filter.unseenFeedbackPerson',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'textinput',
	'wConf' => array(
		'autocomplete'	=> true,
		'FuncRef'		=> 'TodoyuPersonFilterDataSource::autocompletePersons',
		'FuncParams'	=> array(),
		'LabelFuncRef'	=> 'TodoyuPersonFilterDataSource::getLabel',
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
		'FuncRef'	=> 'TodoyuTaskFilterDataSource::getRoleOptions'
	)
);


/**
 * Comment fulltext search
 */
Todoyu::$CONFIG['FILTERS']['TASK']['widgets']['commentFulltext'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_fulltext',
	'label'		=> 'LLL:comment.filter.commentFulltext',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'textinput',
	'wConf' => array(
		'autocomplete'	=> false,
		'LabelFuncRef'	=> 'TodoyuPersonFilterDataSource::getLabel',
		'negation'	=> 'default'
	)
);


Todoyu::$CONFIG['FILTERS']['TASK']['widgets']['commentWrittenPerson'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_commentWrittenPerson',
	'label'		=> 'LLL:comment.filter.commentWrittenPerson',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'textinput',
	'wConf' => array(
		'autocomplete'	=> true,
		'FuncRef'		=> 'TodoyuPersonFilterDataSource::autocompletePersons',
		'FuncParams'	=> array(),
		'LabelFuncRef'	=> 'TodoyuPersonFilterDataSource::getLabel',
		'negation'	=> 'default'
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
		'FuncRef'	=> 'TodoyuTaskFilterDataSource::getRoleOptions'
	)
);


?>