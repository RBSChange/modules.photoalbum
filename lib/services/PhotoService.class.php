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
			self::$instance = new self();
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
	 * @param website_UrlRewritingService $urlRewritingService
	 * @param photoalbum_persistentdocument_photo $document
	 * @param website_persistentdocument_website $website
	 * @param string $lang
	 * @param array $parameters
	 * @return f_web_Link | null
	 */
	public function getWebLink($urlRewritingService, $document, $website, $lang, $parameters)
	{
		if (!isset($parameters['photoalbumParam']))
		{
			$parameters['photoalbumParam'] = array('currentphoto' => $document->getId());
		}
		else
		{
			$parameters['photoalbumParam']['currentphoto']  = $document->getId();
		}
		return $urlRewritingService->getDocumentLinkForWebsite($document->getAlbum(), $website, $lang, $parameters);
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

		$tmpMedia = $document->getThumbnail();
		if ($tmpMedia instanceof media_persistentdocument_tmpfile )
		{
			$document->setThumbnail(null);
			$document->setThumbnail($mediaService->importFromTempFile($tmpMedia));
		}
		
		$tmpMedia = $document->getMediahd();
		if ($tmpMedia instanceof media_persistentdocument_tmpfile )
		{
			$document->setMediahd(null);
			$document->setMediahd($mediaService->importFromTempFile($tmpMedia));
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

	/**
	 * @param photoalbum_persistentdocument_photo $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$data = parent::getResume($document, $forModuleName, $allowedSections);

		$media = $document->getMedia();
		$rc = RequestContext::getInstance();
		$lang = ($media->isContextLangAvailable()) ? $rc->getLang() : $media->getLang();
		try
		{
			$rc->beginI18nWork($lang);

			$info = $media->getCommonInfo();
			$data['content'] = array(
				'mimetype' => $media->getMimetype(),
				'size' => $info['size'],
				'previewimgurl' => array('id' => $media->getId(), 'lang' => $lang)
			);

			if ($media->getMediatype() == MediaHelper::TYPE_IMAGE)
			{
				$pixelsLabel = LocaleService::getInstance()->transBO('m.media.bo.doceditor.pixels');
				$data['content']['width'] = $info['width'].' '.$pixelsLabel;
				$data['content']['height'] = $info['height'].' '.$pixelsLabel;
				$data['content']['previewimgurl']['image'] = LinkHelper::getUIActionLink('media', 'BoDisplay')
				->setQueryParameter('cmpref', $media->getId())
				->setQueryParameter('max-height', 128)
				->setQueryParameter('max-width', 128)
				->setQueryParameter('lang', $lang)
				->setQueryParameter('time', date_Calendar::now()->getTimestamp())->getUrl();
			}
			else
			{
				$data['content']['previewimgurl']['image'] = '';
			}

			$rc->endI18nWork();
		}
		catch (Exception $e)
		{
			$rc->endI18nWork($e);
		}

		return $data;
	}

	// Deprecated.

	/**
	 * @deprecated with no replacement
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
}