<?php
class photoalbum_persistentdocument_album extends photoalbum_persistentdocument_albumbase
{
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
}