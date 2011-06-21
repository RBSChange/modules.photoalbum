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
		$container = $this->getContext()->getParent();
		$request->setAttribute('container', $container);
		
		$items = photoalbum_AlbumService::getInstance()->getPublishedByTopicId($container->getId());
		$itemsPerPage = 10;
		$page = $request->getParameter('page');
		if (!is_numeric($page) || $page < 1 || $page > ceil(count($items) / $itemsPerPage))
		{
			$page = 1;
		}
		$paginator = new paginator_Paginator('photoalbum', $page, $items, $itemsPerPage);
		$request->setAttribute('paginator', $paginator);

		return website_BlockView::SUCCESS;
	}
}