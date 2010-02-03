<?php
class photoalbum_BlockAlbumcontextuallistAction extends website_BlockAction
{
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String view name
	 */
	public function execute($request, $response)
	{
		// Get the parent topic
		$ancestor = $this->getContext()->getPage()->getAncestorIds();
        $topicId = f_util_ArrayUtils::lastElement($ancestor);

        $container = DocumentHelper::getDocumentInstance($topicId);
		$request->setAttribute('container', $container);

		// Get the list of element for the container
		$items = photoalbum_AlbumService::getInstance()->getPublishedByTopicId($topicId);

		// Get the preference of module
		$nbItemPerPage = 10;

		// Set the paginator
		$paginator = new paginator_Paginator('photoalbum', $request->getParameter(paginator_Paginator::REQUEST_PARAMETER_NAME, 1), $items, $nbItemPerPage);
		$request->setAttribute('paginator', $paginator);

		return block_BlockView::SUCCESS;
	}
}