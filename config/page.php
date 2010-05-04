<?php

if( TodoyuExtensions::isInstalled('portal') && allowed('comment', 'general:use') ) {
		// Add portal tab for feedback
	TodoyuPortalManager::addTab('feedback', 'TodoyuCommentRenderer::renderPortalFeedbackTabLabel', 'TodoyuCommentRenderer::renderPortalFeedbackTabContent', 30);
}


?>