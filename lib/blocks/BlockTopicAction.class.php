<?php
class photoalbum_BlockTopicAction extends website_BlockAction
{
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String view name
	 */
    public function execute($request, $response)
    {   
        $container = $this->getRequiredDocumentParameter();
		$request->setAttribute('container', $container);

		// Get the list of element for the container
		$items = array();
		$items = $container->getDocumentService()->getChildrenOf($container, 'modules_photoalbum/album');
		
		// Get the preference of module
		$nbItemPerPage = 10;

		// Set the paginator
		$paginator = new paginator_Paginator('photoalbum', $request->getParameter(paginator_Paginator::PAGEINDEX_PARAMETER_NAME, 1), $items, $nbItemPerPage);
		$request->setAttribute('paginator', $paginator);

		return block_BlockView::SUCCESS;
    }
}