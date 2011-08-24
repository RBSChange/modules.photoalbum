<?php
class photoalbum_persistentdocument_photo extends photoalbum_persistentdocument_photobase
{
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