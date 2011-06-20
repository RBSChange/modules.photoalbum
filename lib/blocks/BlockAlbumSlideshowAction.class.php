<?php
/**
 * photoalbum_BlockAlbumSlideshowAction
 * @package modules.photoalbum.lib.blocks
 */
class photoalbum_BlockAlbumSlideshowAction extends website_BlockAction
{
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function execute($request, $response)
	{
		if ($this->isInBackofficeEdition())
		{
			return website_BlockView::NONE;
		}
	
		$configuration = $this->getConfiguration();	
		$album = $this->getDocumentParameter();
		if ($album === null)
		{
			$album = $configuration->getDefaultcmpref();
		}
		$request->setAttribute('album', $album);
		
		$options = array('lightbox: true');
		$options[] = 'height: ' . $configuration->getSlideshowHeight();
		if ($configuration->getSlideshowWidth())
		{
			$options[] = 'width: ' . $configuration->getSlideshowWidth();
		}
		$options[] = 'thumbnails: ' . ($configuration->getShowThumbnails() ? 'true' : 'false');
		$options[] = 'autoplay: ' . ($configuration->getAutoplay() ? 'true' : 'false');
		$options[] = 'lightbox: ' . ($configuration->getLightbox() ? 'true' : 'false');
		$options[] = 'transition: \'' . $configuration->getTransition() . "'";
		$request->setAttribute('options', implode(', ', $options));
		
		if (!$configuration->getShowThumbnails())
		{
			$request->setAttribute('slideshowClass', 'no-thumbnail');
		}
		
		return website_BlockView::SUCCESS;
	}
}