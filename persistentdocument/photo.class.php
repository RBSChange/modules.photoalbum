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
}