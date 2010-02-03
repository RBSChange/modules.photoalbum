<?php
class photoalbum_ActionBase extends f_action_BaseAction
{
	
	/**
	 * Returns the photoalbum_AlbumService to handle documents of type "modules_photoalbum/album".
	 *
	 * @return photoalbum_AlbumService
	 */
	public function getAlbumService()
	{
		return photoalbum_AlbumService::getInstance();
	}
	
	/**
	 * Returns the photoalbum_PhotoService to handle documents of type "modules_photoalbum/photo".
	 *
	 * @return photoalbum_PhotoService
	 */
	public function getPhotoService()
	{
		return photoalbum_PhotoService::getInstance();
	}
	
}