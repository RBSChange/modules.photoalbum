<?php
class photoalbum_BlockAlbumAction extends website_BlockAction
{
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String view name
	 */
	public function execute($request, $response)
	{
		if ($this->isInBackofficeEdition())
		{
			return website_BlockView::NONE;
		}
		
		$configuration = $this->getConfiguration();
		$album = $this->getDocumentParameter();
		if ($album === null)
		{
			$album = $configuration->getDefaultcmpref();
		}
		
		$isOnDetailPage = TagService::getInstance()->hasTag($this->getContext()->getPersistentPage(), 'functional_photoalbum_album-detail');
		if (!($album instanceof photoalbum_persistentdocument_album) || !$album->isPublished())
		{
			if ($isOnDetailPage && !$this->isInBackofficePreview())
			{
				HttpController::getInstance()->redirect("website", "Error404");
			}
			return website_BlockView::NONE;
		}
		$request->setAttribute('item', $album);

		$useDiaporama = $configuration->getUsediaporama();
		$request->setAttribute('usediaporama', $useDiaporama);
		$request->setAttribute('diaporamaparams', array('photoalbumParam' => array('diaporama' => '1')));

		if ($useDiaporama)
		{
			if ($request->hasParameter('diaporama'))
			{
				$diaporama = intval($request->getParameter('diaporama', 0)) == 1;
			}
			else
			{
				$diaporama = ($configuration->getDefaultmode() == 'diaporama');
			}

			if ($diaporama)
			{
				$this->getContext()->addScript('modules.photoalbum.lib.js.jquery-cycle-all');
				return 'Diaporama';
			}
		}

		$pageSize = $configuration->getPhotoinselector();
		if ($pageSize > 0)
		{
			$album->setPageSize($pageSize);
		}

		$currentphotoId = intval($request->getParameter('currentphoto', 0));
		if ($currentphotoId > 0)
		{
			$currentPhoto = photoalbum_persistentdocument_photo::getInstanceById($currentphotoId);
			if ($currentPhoto->isPublished())
			{
				$album->setCurrentPhotoId($currentphotoId);
			}
		}
		else
		{
			$album->setCurrentPageIndex(intval($request->getParameter('currentpageindex', 0)));
		}
		return website_BlockView::SUCCESS;
	}
}