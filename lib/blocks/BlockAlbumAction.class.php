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
		if ($this->isInBackoffice())
		{
			return website_BlockView::DUMMY;
		}
		$configuration = $this->getConfiguration();
		$album = $this->getDocumentParameter(K::COMPONENT_ID_ACCESSOR, "photoalbum_persistentdocument_album");
		if ($album === null)
		{
			$album = $configuration->getDefaultcmpref();
			if ($album === null)
			{
				return website_BlockView::DUMMY;
			}
		}

		$request->setAttribute('item', $album);

		if (!$album->isPublished())
		{
			return $this->genericView('Unavailable');
		}

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
				$defaultMode = $configuration->getConfigurationParameter('defaultmode', 'standard');
				$diaporama = ($defaultMode == 'diaporama');
			}

			if ($diaporama)
			{
				if ($this->isInBackoffice())
				{
					return website_BlockView::NONE;
				}
				$this->getContext()->addScript('modules.photoalbum.lib.js.jquery-cycle-all');
				return 'Diaporama';
			}
		}

		$pageSize = intval($configuration->getConfigurationParameter('photoinselector', 5));
		if ($pageSize > 0)
		{
			$album->setPageSize($pageSize);
		}

		$currentphotoId = intval($request->getParameter('currentphoto', 0));
		if ($currentphotoId != 0)
		{
			$currentPhoto = DocumentHelper::getDocumentInstance($currentphotoId, "modules_photoalbum/photo");
			if ($currentPhoto->isPublished())
			{
				$album->setCurrentPhotoId($currentphotoId);
			}
		}
		else
		{
			$currentPageIndex = intval($request->getParameter('currentpageindex', 0));
			$album->setCurrentPageIndex($currentPageIndex);
		}
		return website_BlockView::SUCCESS;
	}
}