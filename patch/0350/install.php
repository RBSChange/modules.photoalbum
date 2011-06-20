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
	}
}