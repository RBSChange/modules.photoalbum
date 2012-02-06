<?php
/**
 * photoalbum_patch_0350
 * @package modules.photoalbum
 */
class photoalbum_patch_0350 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$this->executeLocalXmlScript('update.xml');
		
		$newPath = f_util_FileUtils::buildWebeditPath('modules/photoalbum/persistentdocument/album.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'photoalbum', 'album');
		$newProp = $newModel->getPropertyByName('topic');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('photoalbum', 'album', $newProp);
		$newProp = $newModel->getPropertyByName('website');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('photoalbum', 'album', $newProp);
		$this->execChangeCommand('compile-db-schema');
		
		$tm = f_persistentdocument_TransactionManager::getInstance();
		try 
		{
			$tm->beginTransaction();
			
			$as = photoalbum_AlbumService::getInstance();
			foreach ($as->createQuery()->find() as $album)
			{
				$topic = $as->getParentOf($album);
				$album->addTopic($topic);
				$album->save();
			}
			
			$ts = TreeService::getInstance();
			$fs = generic_FolderService::getInstance();
			$rootId = ModuleService::getInstance()->getRootFolderId('photoalbum');
			$root = generic_persistentdocument_rootfolder::getInstanceById($rootId);
			foreach ($root->getDocumentService()->getChildrenOf($root, 'modules_website/websitetopicsfolder') as $topicsFolder)
			{
				$folder = $fs->getNewDocumentInstance();
				$folder->setLabel($topicsFolder->getLabel());
				$folder->save($rootId);
				$folderId = $folder->getId();
				
				foreach ($topicsFolder->getTopicsArray() as $topic)
				{
					$this->migrateTopic($topic, $folderId, $ts, $fs);
				}
			}
			$root->removeAllTopics();
			$root->save();
			
			$tm->commit();
		} 
		catch (Exception $e)
		{
			$tm->rollback($e);
			throw $e;
		}
	}
	
	/**
	 * @param website_persistentodcument_topic $topic
	 * @param TreeService $ts
	 * @param generic_FolderService $fs
	 */
	private function migrateTopic($topic, $parentId, $ts, $fs)
	{
		$folder = $fs->getNewDocumentInstance();
		$folder->setLabel($topic->getLabel());
		$folder->save($parentId);
		$folderId = $folder->getId();
		
		foreach ($topic->getDocumentService()->getChildrenOf($topic, 'modules_photoalbum/album') as $album)
		{
			$photos = $album->getChildrenPhotos();
			foreach ($photos as $photo)
			{
				$ts->deleteNode($ts->getInstanceByDocument($photo));
			}
			
			$albumId = $album->getId();
			$ts->deleteNode($ts->getInstanceByDocument($album));
			$ts->newLastChild($folderId, $albumId);
			
			foreach ($photos as $photo)
			{
				$ts->newLastChild($albumId, $photo->getId());
			}
		}
		
		foreach ($topic->getDocumentService()->getChildrenOf($topic, 'modules_website/topic') as $subTopic)
		{
			$this->migrateTopic($subTopic, $folderId, $ts, $fs);
		}
	}
}