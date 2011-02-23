<?php
class photoalbum_PhotoService extends f_persistentdocument_DocumentService
{
	/**
	 * @var photoalbum_PhotoService
	 */
	private static $instance;

	/**
	 * @return photoalbum_PhotoService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return photoalbum_persistentdocument_photo
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_photoalbum/photo');
	}

	/**
	 * Create a query based on 'modules_photoalbum/photo' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_photoalbum/photo');
	}
	
	/**
	 * @param photoalbum_persistentdocument_photo $document
	 */
	function generateUrl($document)
	{
		return LinkHelper::getDocumentUrl($document->getAlbum(), null, array("photoalbumParam[currentphoto]" => $document->getId()));
	}

	/**
	 * @param photoalbum_persistentdocument_photo $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
	    $mediaService = media_MediaService::getInstance();
	    
	    $tmpMedia = $document->getMedia();
        if ($tmpMedia instanceof media_persistentdocument_tmpfile ) 
        {
          $document->setMedia(null);  
          $document->setMedia($mediaService->importFromTempFile($tmpMedia));
        }
        $this->updateMediaInfos($document, $document->getMedia());
        
        $tmpMedia = $document->getThumbnail();
	    if ($tmpMedia instanceof media_persistentdocument_tmpfile ) 
        {
          $document->setThumbnail(null);
          $document->setThumbnail($mediaService->importFromTempFile($tmpMedia));
        } 
        
        $this->updateMediaInfos($document, $document->getThumbnail());

        $tmpMedia = $document->getMediahd();
		if ($tmpMedia instanceof media_persistentdocument_tmpfile ) 
        {
            $document->setMediahd(null);
            $document->setMediahd($mediaService->importFromTempFile($tmpMedia));
        }
        $this->updateMediaInfos($document, $document->getMediahd());
	}

	/**
	 * Mise à jour des informations des medias attaché à la 
	 *
	 * @param photoalbum_persistentdocument_photo $document
	 * @param media_persistentdocument_media $media
	 */
	private function updateMediaInfos($document, $media)
	{
	    if ($media instanceof media_persistentdocument_media && $media->isContextLangAvailable())
        {
            $media->setLabel($document->getLabel());
            $media->setTitle($document->getLabel());
            $media->setDescription($document->getSummary());
            if ($media->isModified())
            {
                 $media->save();   
            }
        }	    
	}
	
	/**
	 * Methode à surcharger pour effectuer des post traitement apres le changement de status du document
	 * utiliser $document->getPublicationstatus() pour retrouver le nouveau status du document.
	 * @param photoalbum_persistentdocument_photo $document
	 * @param String $oldPublicationStatus
	 * @param array<"cause" => String, "modifiedPropertyNames" => array, "oldPropertyValues" => array> $params
	 * @return void
	 */
	protected function publicationStatusChanged($document, $oldPublicationStatus, $params)
	{
	    $album = $this->getParentOf($document);
	    if ($album instanceof photoalbum_persistentdocument_album)
	    {
	        $album->getDocumentService()->publishIfPossible($album->getId());
	    }
	}

	/**
	 * @param photoalbum_persistentdocument_photo $document
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */	
	public function addTreeAttributes($document, $moduleName, $treeType, &$nodeAttributes)
	{
		if ($treeType == 'wlist')
		{
	    	$media = $document->getThumbnailMedia();
	    	$nodeAttributes['thumbnailsrc'] = MediaHelper::getPublicFormatedUrl($media, "modules.uixul.backoffice/thumbnaillistitem");
		}
	}
}