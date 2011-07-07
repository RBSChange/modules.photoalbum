<?php
class photoalbum_persistentdocument_album extends photoalbum_persistentdocument_albumbase implements indexer_IndexableDocument
{
	/**
	 * Get the indexable document
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
	
	/**
	 * @var photoalbum_persistentdocument_photo[]
	 */
	private $publishedPhotos = null;

	/**
	 * @return photoalbum_persistentdocument_photo[]
	 */
	public function getPublishedPhotos()
	{
		$this->checkLoaded();
		
		if ($this->publishedPhotos === null)
		{
			$this->publishedPhotos = $this->getChildrenPublishedPhotos();
		}
		return $this->publishedPhotos;
	}
	
	/**
	 * @var media_persistentdocument_media
	 */
	private $thumbnail = null;

	/**
	 * @return media_persistentdocument_media
	 */
	public function getThumbnail()
	{
		$this->checkLoaded();
		if ($this->thumbnail === null)
		{
			$photos = $this->getPublishedPhotos();
			if (count($photos) > 0)
			{
				$photo = $photos[0];
				$this->thumbnail = $photo->getPopulatedThumbnail();
			}
			else
			{
				$this->thumbnail = false;
			}
		}
		return $this->thumbnail === false ? null : $this->thumbnail;
	}
	
	// Deprecated.
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	private $pageSize = 10;
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function setPageSize($pageSize)
	{
		if ($pageSize !== null)
		{
			$this->pageSize = intval($pageSize);	
		}
	}

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getPageSize()
	{
		return $this->pageSize;
	}
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
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
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	private $currentPageIndex = 0;

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function setCurrentPageIndex($pageIndex)
	{
		if ($pageIndex < 0)
		{
			$pageIndex = 0;
		}

		$this->currentPageIndex = $pageIndex;
		$this->setCurrentPhotoIndex($this->currentPageIndex * $this->pageSize);
	}

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getCurrentPageIndex()
	{
		return $this->currentPageIndex;
	}
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	private $currentphotoIndex = 0;
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	private function setCurrentPhotoIndex($photoIndex)
	{
		$photos = $this->getPublishedPhotos();
		if ($photoIndex >= count($photos))
		{
			return;
		}
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
	 * @deprecated (will be removed in 4.0)
	 */
	public function getCurrentPhoto()
	{
		$photos = $this->getPublishedPhotos();
		if (array_key_exists($this->currentphotoIndex, $photos))
		{
			return $photos[$this->currentphotoIndex];
		}
		return null;
	}
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	private $previousPhoto;
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getPreviousPhoto()
	{
		return $this->previousPhoto;
	}
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	private $nextPhoto;
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getPreviousPhotoPageIndex()
	{
		return $this->previousPhotoPageIndex;
	}

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	private $previousPhotoPageIndex;
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getNextPhotoPageIndex()
	{
		return $this->nextPhotoPageIndex;
	}
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	private $nextPhotoPageIndex;
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getNextPhoto()
	{
		return $this->nextPhoto;
	}
	
	/**
	 * @deprecated (will be removed in 4.0)
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
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getPagePreviousSelector()
	{
		return $this->currentPageIndex - 1;
	}

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function hasPagePreviousSelector()
	{
		return $this->getPagePreviousSelector() >= 0;
	}

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getPageNextSelector()
	{
		return $this->currentPageIndex + 1;
	}

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function hasPageNextSelector()
	{
		return ($this->getPageNextSelector() * $this->pageSize) < count($this->getPublishedPhotos());
	}
}