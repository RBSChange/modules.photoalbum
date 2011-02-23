<?php
class photoalbum_AlbumService extends f_persistentdocument_DocumentService
{
	/**
	 * @var photoalbum_AlbumService
	 */
	private static $instance;

	/**
	 * @return photoalbum_AlbumService
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
	 * @return photoalbum_persistentdocument_album
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_photoalbum/album');
	}

	/**
	 * Create a query based on 'modules_photoalbum/album' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_photoalbum/album');
	}
	
	/**
	 * @param integer $topicId
	 * @return photoalbum_persistentdocument_album[]
	 */
	public function getPublishedByTopicId($topicId)
	{
		return $this->createQuery()->add(Restrictions::published())->add(Restrictions::childOf($topicId))->find();
	}

	/**
	 * @param photoalbum_persistentdocument_album $document
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */	
	public function addTreeAttributes($document, $moduleName, $treeType, &$nodeAttributes)
	{
		if ($treeType == 'wlist')
		{
			$photo = $document->getCurrentPhoto();
			if ($photo !== null)
			{
		    	$media = $photo->getThumbnailMedia();
		    	$nodeAttributes['thumbnailsrc'] = MediaHelper::getPublicFormatedUrl($media, "modules.uixul.backoffice/thumbnaillistitem");
			}
		}
	}
}