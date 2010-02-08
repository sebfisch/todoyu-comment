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
 * Unseen comment feedback request for user
 */
$CONFIG['FILTERS']['TASK']['widgets']['unseenFeedbackUser'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_unseenFeedbackUser',
	'label'		=> 'LLL:comment.filter.unseenFeedbackUser',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'textinput',
	'wConf' => array(
		'autocomplete'	=> true,
		'FuncRef'		=> 'TodoyuUserFilterDataSource::autocompleteUsers',
		'FuncParams'	=> array(),
		'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel',
		'negation'	=> 'default'
	)
);

$CONFIG['FILTERS']['TASK']['filters']['unseenFeedbackCurrentUser'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_unseenFeedbackCurrentUser'
);

$CONFIG['FILTERS']['TASK']['widgets']['unseenFeedbackGroups'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_unseenFeedbackGroups',
	'label'		=> 'LLL:comment.filter.unseenFeedbackGroups',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'select',
	'wConf'		=> array(
		'multiple'	=> true,
		'size'		=> 5,
		'FuncRef'	=> 'TodoyuTaskFilterDataSource::getUsergroupOptions'
	)
);


/**
 * Comment fulltext search
 */
$CONFIG['FILTERS']['TASK']['widgets']['commentFulltext'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_fulltext',
	'label'		=> 'LLL:comment.filter.commentFulltext',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'textinput',
	'wConf' => array(
		'autocomplete'	=> false,
		'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel',
		'negation'	=> 'default'
	)
);


$CONFIG['FILTERS']['TASK']['widgets']['commentWrittenUser'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_commentWrittenUser',
	'label'		=> 'LLL:comment.filter.commentWrittenUser',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'textinput',
	'wConf' => array(
		'autocomplete'	=> true,
		'FuncRef'		=> 'TodoyuUserFilterDataSource::autocompleteUsers',
		'FuncParams'	=> array(),
		'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel',
		'negation'	=> 'default'
	)
);

$CONFIG['FILTERS']['TASK']['widgets']['commentWrittenGroups'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_commentWrittenGroups',
	'label'		=> 'LLL:comment.filter.commentWrittenGroups',
	'optgroup'	=> 'LLL:comment.search.label',
	'widget'	=> 'select',
	'wConf'		=> array(
		'multiple'	=> true,
		'size'		=> 5,
		'FuncRef'	=> 'TodoyuTaskFilterDataSource::getUsergroupOptions'
	)
);


?>