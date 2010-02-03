<?php
class photoalbum_persistentdocument_photo extends photoalbum_persistentdocument_photobase implements indexer_IndexableDocument{
	/**
	 * Get the indexable document
	 *
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
	
	// Templating function
	
	private $isCurrent = false;
	
	public function setCurrent($isCurrent = true)
	{
	    $this->isCurrent = ($isCurrent == true);
	}

	public function isCurrent()
	{
	    return $this->isCurrent;
	}
	
	/**
	 * @return media_persistentdocument_media
	 */
	public final function getThumbnailMedia()
	{
	    return ($this->getThumbnail() != null) ? $this->getThumbnail() : $this->getMedia();
	}
	
	public function getPopulatedThumbnail()
	{
        $media = $this->getThumbnailMedia();
        $media->setTitle($this->getLabel());
        return $media;  
	}
	
	public function getPopulatedMedia()
	{
        $media = $this->getMedia();
        $media->setTitle($this->getLabel());
        return $media;  
	}

	public function getSelectorUrl()
	{
	    $media = $this->getThumbnailMedia();
	    return MediaHelper::getPublicFormatedUrl($media, "modules.photoalbum.frontoffice/photoselector");
	}
	
	public function getPreviewUrl()
	{
	    $media = $this->getMedia();
	    return MediaHelper::getPublicFormatedUrl($media, "modules.photoalbum.frontoffice/photopreview");
	}
	
	public function getDiaporamaUrl()
	{
	    $media = $this->getMedia();
	    return MediaHelper::getPublicFormatedUrl($media, "modules.photoalbum.frontoffice/diaporama");
	}
	
	public function getAlbum()
	{
		return TreeService::getInstance()->getParentDocument($this);
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
	    	$media = $this->getThumbnailMedia();
	    	$nodeAttributes['thumbnailsrc'] = MediaHelper::getPublicFormatedUrl($media, "modules.uixul.backoffice/thumbnaillistitem");
		}
	}
}