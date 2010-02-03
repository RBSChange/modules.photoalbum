<?php
class photoalbum_persistentdocument_album extends photoalbum_persistentdocument_albumbase implements indexer_IndexableDocument{

	/**
	 * @var photoalbum_persistentdocument_photo
	 */
	private $previousPhoto;
	/**
	 * @var photoalbum_persistentdocument_photo
	 */
	private $nextPhoto;

	/**
	 * @var Integer
	 */
	private $previousPhotoPageIndex;
	/**
	 * @var Integer
	 */
	private $nextPhotoPageIndex;
	/**
	 * Get the indexable document
	 *
	 * @return indexer_IndexedDocument
	 */
	public function getIndexedDocument()
	{
		$indexedDoc = new indexer_IndexedDocument();
		$indexedDoc->setId($this->getId());
		$indexedDoc->setDocumentModel('modules_photoalbum/album');
		$indexedDoc->setLabel($this->getLabel());
		$indexedDoc->setLang(RequestContext::getInstance()->getLang());
		$indexedDoc->setText($this->getSummary());
		return $indexedDoc;
	}

	// Templating function
	private $currentThumbnail = null;

	private $publishedPhotos = null;

	private $currentphotoIndex = 0;

	private $currentPageIndex = 0;

	private $pageSize = 10;

	public function getPublishedPhotos()
	{
		$this->checkLoaded();
		
		if ($this->publishedPhotos === null)
		{
			$this->publishedPhotos = $this->getChildrenPublishedPhotos();
		}
		return $this->publishedPhotos;
	}

	public function getThumbnail()
	{
		$this->checkLoaded();
		if ($this->currentThumbnail === null)
		{
			$photos = $this->getPublishedPhotos();
			if (count($photos) > 0)
			{
				$photo = $photos[0];
				$this->currentThumbnail = $photo->getPopulatedThumbnail();
			}
			else
			{
				$this->currentThumbnail = false;
			}
		}
		return $this->currentThumbnail === false ? NULL : $this->currentThumbnail;
	}
	
	public function setPageSize($pageSize)
	{
		if ($pageSize !== null)
		{
			$this->pageSize = intval($pageSize);	
		}
	}

	public function getPageSize()
	{
		return $this->pageSize;
	}

	public function setCurrentPhotoId($currentphotoId)
	{
		$currentphotoIndex = 0;
		foreach ($this->getPublishedPhotos() as $photo)
		{
			if ($photo->getId() == $currentphotoId)
			{
				$this->setCurrentPhotoIndex($currentphotoIndex);
				return;
			}
			$currentphotoIndex++;
		}

		$this->setCurrentPhotoIndex(0);
	}

	public function setCurrentPageIndex($pageIndex)
	{
		if ($pageIndex < 0)
		{
			$pageIndex = 0;
		}

		$this->currentPageIndex = $pageIndex;
		$this->setCurrentPhotoIndex($this->currentPageIndex * $this->pageSize);
	}

	public function getCurrentPageIndex()
	{
		return $this->currentPageIndex;
	}

	/**
	 * @return photoalbum_persistentdocument_photo
	 */
	public function getCurrentPhoto()
	{
		$photos = $this->getPublishedPhotos();
		return $photos[$this->currentphotoIndex];
	}

	/**
	 * @return photoalbum_persistentdocument_photo
	 */
	public function getPreviousPhoto()
	{
		return $this->previousPhoto;
	}

	public function getPreviousPhotoPageIndex()
	{
		return $this->previousPhotoPageIndex;
	}

	public function getNextPhotoPageIndex()
	{
		return $this->nextPhotoPageIndex;
	}

	/**
	 * @return photoalbum_persistentdocument_photo
	 */
	public function getNextPhoto()
	{
		return $this->nextPhoto;
	}

	/**
	 * @return photoalbum_persistentdocument_photo[]
	 */
	public function getPhotosSelector()
	{
		$selector = array();
		$photos = $this->getPublishedPhotos();

		$startIndex = $this->currentPageIndex * $this->pageSize;
		$endIndex = $startIndex + $this->pageSize;
		if ($endIndex > count($photos))
		{
			$endIndex = count($photos);
		}
		while ($startIndex < $endIndex)
		{
			$selector[] = $photos[$startIndex];
			$startIndex++;
		}
		return $selector;
	}

	public function getPagePreviousSelector()
	{
		return $this->currentPageIndex - 1;
	}

	public function hasPagePreviousSelector()
	{
		return $this->getPagePreviousSelector() >= 0;
	}

	public function getPageNextSelector()
	{
		return $this->currentPageIndex + 1;
	}

	public function hasPageNextSelector()
	{
		return ($this->getPageNextSelector() * $this->pageSize) < count($this->getPublishedPhotos());
	}

	// private methods

	/**
	 * @param Integer $photoIndex
	 */
	private function setCurrentPhotoIndex($photoIndex)
	{
		$photos = $this->getPublishedPhotos();
		$photo = $photos[$photoIndex];
		$photo->setCurrent();
		$this->currentphotoIndex = $photoIndex;
		$this->currentPageIndex = floor($photoIndex / $this->pageSize);

		if ($photoIndex > 0)
		{
			$this->previousPhoto = $photos[$photoIndex-1];
			$this->previousPhotoPageIndex = floor(($photoIndex-1) / $this->pageSize);
		}
		if ($photoIndex < count($photos)-1)
		{
			$this->nextPhoto = $photos[$photoIndex+1];
			$this->nextPhotoPageIndex = floor(($photoIndex+1) / $this->pageSize);
			Framework::debug("NEXT PAGE INDEX ".$this->nextPhotoPageIndex." ".($photoIndex+1)." ".$this->pageSize);
		}
	}
	
	/**
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */	
	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
	{
		if ($treeType == 'wlist')
		{
			$photo = $this->getCurrentPhoto();
			if ($photo !== null)
			{
		    	$media = $photo->getThumbnailMedia();
		    	$nodeAttributes['thumbnailsrc'] = MediaHelper::getPublicFormatedUrl($media, "modules.uixul.backoffice/thumbnaillistitem");
			}
		}
	}
}