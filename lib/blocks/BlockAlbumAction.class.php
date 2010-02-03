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
			return block_BlockView::DUMMY;
		}
		$configuration = $this->getConfiguration();
		$album = $this->getDocumentParameter(K::COMPONENT_ID_ACCESSOR, "photoalbum_persistentdocument_album");
		if ($album === null)
		{
			$defaultalbumid = $configuration->getDefaultcmpref();
			if ($defaultalbumid === null)
			{
				return block_BlockView::DUMMY;
			}
			$album = DocumentHelper::getDocumentInstance($defaultalbumid);
		}

		$request->setAttribute('item', $album);

		if (!$album->isPublished())
		{
			return $this->genericView(block_BlockView::UNAVAILABLE);
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
					return block_BlockView::NONE;
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
			elseif (DocumentHelper::equals($currentPhoto->getAlbum(), $album))
			{
				$user = users_UserService::getInstance()->getCurrentBackEndUser();
				if ($user !== null)
				{
					$permissionService = f_permission_PermissionService::getInstance();
					if ($permissionService->hasPermission($user, "modules_photoalbum.Insert.photo", $album->getId()))
					{
						$request->setAttribute("unpublishedPhoto", $currentPhoto);
					}
				}
			}
			else
			{
				throw new Exception("Bad argument");
			}
		}
		else
		{
			$currentPageIndex = intval($request->getParameter('currentpageindex', 0));
			$album->setCurrentPageIndex($currentPageIndex);
		}
		return block_BlockView::SUCCESS;
	}
}