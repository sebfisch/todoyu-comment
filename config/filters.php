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

$CONFIG['FILTERS']['TASK']['config']['filterWidgets']['unseenFeedbackCurrentUser'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_unseenFeedbackCurrentUser',
	'label'		=> 'LLL:comment.filter.unseenFeedbackCurrentUser.label',
	'optgroup'	=> 'LLL:task.search.label',
	'widget'	=> 'checkbox',
	'wConf'   => array(
  		'checked' => true
	)
);

$CONFIG['FILTERS']['TASK']['config']['filterWidgets']['unseenFeedbackUser'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_unseenFeedbackUser',
	'label'		=> 'LLL:comment.filter.unseenFeedbackUser.label',
	'optgroup'	=> 'LLL:task.search.label',
	'widget'	=> 'textinput',
	'wConf' => array(
		'autocomplete'	=> true,
		'FuncRef'		=> 'TodoyuUserFilterDataSource::autocompleteUsers',
		'FuncParams'	=> array(),
		'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel',
		'negation'	=> array(
    		'labelTrue'		=> 'LLL:search.negation.default.true',
    		'labelFalse'	=> 'LLL:search.negation.default.false',
	    )
	)
);

$CONFIG['FILTERS']['TASK']['config']['filterWidgets']['feedbackFulltext'] = array(
	'funcRef'	=> 'TodoyuCommentTaskFilter::Filter_fulltext',
	'label'		=> 'LLL:comment.filter.fulltext.label',
	'optgroup'	=> 'LLL:task.search.label',
	'widget'	=> 'textinput',
	'wConf' => array(
		'autocomplete'	=> false,
		'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel',
		'negation'	=> array(
    		'labelTrue'		=> 'LLL:search.negation.default.true',
    		'labelFalse'	=> 'LLL:search.negation.default.false',
	    )
	)
);

?>