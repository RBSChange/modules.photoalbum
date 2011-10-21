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
			self::$instance = new self();
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
		return $this->createQuery()->add(Restrictions::published())->add(Restrictions::eq('topic.id', $topicId))->find();
	}
	
	/**
	 * @param photoalbum_persistentdocument_album $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId)
	{
		if ($document->isPropertyModified('topic'))
		{
			$this->refreshWebsites($document);
		}
	}

	/**
	 * @param photoalbum_persistentdocument_album $document
	 */
	public function refreshWebsites($document)
	{
		$websiteIds = array();
		foreach ($document->getTopicArray() as $topic)
		{
			$websiteIds[] = $topic->getDocumentService()->getWebsiteId($topic);
		}
		$websites = website_WebsiteService::getInstance()->createQuery()->add(Restrictions::in('id', $websiteIds))->find();
		$document->setWebsiteArray($websites);
	}
	
	/**
	 * @param photoalbum_persistentdocument_album $document
	 * @return integer | null
	 */
	public function getWebsiteId($document)
	{
		$website = $document->getWebsite(0);
		return ($website !== null) ? $website->getId() : null;
	}
	
	/**
	 * @param photoalbum_persistentdocument_album $document
	 * @return integer[] | null
	 */
	public function getWebsiteIds($document)
	{
		$websites = $document->getWebsiteArray();
		return DocumentHelper::getIdArrayFromDocumentArray($websites);
	}

	/**
	 * @param photoalbum_persistentdocument_album $document
	 * @param website_persistentdocument_website $website
	 */
	public function getPrimaryTopicForWebsite($document, $website)
	{
		$topics = $document->getPublishedTopicArray();
		$topicIds = DocumentHelper::getIdArrayFromDocumentArray($topics);
				
		$query = website_TopicService::getInstance()->createQuery()->add(Restrictions::descendentOf($website->getId()));
		$query->add(Restrictions::published())->add(Restrictions::in('id', $topicIds))->setProjection(Projections::property('id'));
		$ids = $query->findColumn('id');
		
		foreach ($topics as $topic)
		{
			if (in_array($topic->getId(), $ids))
			{
				return $topic;
			}
		}
		return null;
	}
	
	/**
	 * @param photoalbum_persistentdocument_album $document
	 * @return website_persistentdocument_page | null
	 */
	public function getDisplayPage($document)
	{
		$request = change_Controller::getInstance()->getContext()->getRequest();
		if ($request->hasModuleParameter('photoalbum', 'topicId'))
		{
			$topicId = $request->getModuleParameter('photoalbum', 'topicId');
		}
		else
		{
			$topic = $this->getPrimaryTopicForWebsite($document, website_WebsiteService::getInstance()->getCurrentWebsite());
			$topicId = $topic ? $topic->getId() : null;
		}
		
		if ($topicId > 0)
		{
			return website_PageService::getInstance()->createQuery()
				->add(Restrictions::published())
				->add(Restrictions::childOf($topicId))
				->add(Restrictions::hasTag('functional_photoalbum_album-detail'))
				->findUnique();
		}
		return null;
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
			$media = $document->getThumbnail();
			if ($media !== null)
			{
		    	$nodeAttributes['thumbnailsrc'] = MediaHelper::getPublicFormatedUrl($media, "modules.uixul.backoffice/thumbnaillistitem");
			}
		}
	}
}