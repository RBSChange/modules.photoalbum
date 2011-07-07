<?php
class photoalbum_persistentdocument_photo extends photoalbum_persistentdocument_photobase implements indexer_IndexableDocument
{
	/**
	 * @return indexer_IndexedDocument
	 */
	public function getIndexedDocument()
	{
		$indexedDoc = new indexer_IndexedDocument();
		$indexedDoc->setId($this->getId());
		$indexedDoc->setDocumentModel('modules_photoalbum/photo');
		$indexedDoc->setLabel($this->getLabel());
		$indexedDoc->setLang(RequestContext::getInstance()->getLang());
		$indexedDoc->setText($this->getSummary() . "\n" . $this->getCopyright());
		return $indexedDoc;
	}
	
	/**
	 * @return media_persistentdocument_media
	 */
	public final function getThumbnailMedia()
	{
		return ($this->getThumbnail() != null) ? $this->getThumbnail() : $this->getMedia();
	}

	/**
	 * @return media_persistentdocument_media
	 */
	public function getPopulatedThumbnail()
	{
		$media = $this->getThumbnailMedia();
		$media->setTitle($this->getLabel());
		return $media;
	}

	/**
	 * @return media_persistentdocument_media
	 */
	public function getPopulatedMedia()
	{
		$media = $this->getMedia();
		$media->setTitle($this->getLabel());
		return $media;
	}

	/**
	 * @return  photoalbum_persistentdocument_album
	 */
	public function getAlbum()
	{
		return TreeService::getInstance()->getParentDocument($this);
	}
	
	// Deprecated.
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	private $isCurrent = false;

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function setCurrent($isCurrent = true)
	{
		$this->isCurrent = ($isCurrent == true);
	}

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function isCurrent()
	{
		return $this->isCurrent;
	}
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getSelectorUrl()
	{
		$media = $this->getThumbnailMedia();
		return MediaHelper::getPublicFormatedUrl($media, "modules.photoalbum.frontoffice/photoselector");
	}

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getPreviewUrl()
	{
		$media = $this->getMedia();
		return MediaHelper::getPublicFormatedUrl($media, "modules.photoalbum.frontoffice/photopreview");
	}

	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getDiaporamaUrl()
	{
		$media = $this->getMedia();
		return MediaHelper::getPublicFormatedUrl($media, "modules.photoalbum.frontoffice/diaporama");
	}
}