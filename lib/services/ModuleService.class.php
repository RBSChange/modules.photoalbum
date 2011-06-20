<?php
/**
 * @package modules.photoalbum.lib.services
 */
class photoalbum_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var photoalbum_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return photoalbum_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	
	/**
	 * @param f_peristentdocument_PersistentDocument $container
	 * @param array $attributes
	 * @param string $script
	 * @return array
	 */
	public function getStructureInitializationAttributes($container, $attributes, $script)
	{
		// Check container.
		if (!$container instanceof website_persistentdocument_topic)
		{
			throw new BaseException('Invalid topic', 'modules.photoalbum.bo.general.Invalid-topic');
		}
		
		$query = website_PageService::getInstance()->createQuery()->add(Restrictions::orExp(
			Restrictions::hasTag('functional_photoalbum_album-list'),
			Restrictions::hasTag('functional_photoalbum_album-detail')
		));
		$query->add(Restrictions::childOf($container->getId()))->setProjection(Projections::rowCount('count'));
		if (f_util_ArrayUtils::firstElement($query->findColumn('count')) > 0)
		{
			throw new BaseException('This topic already contains some of this pages', 'modules.photoalbum.bo.general.Topic-already-contains-some-of-this-pages');
		}
		
		// Set atrtibutes.
		$attributes['byDocumentId'] = $container->getId();
		$attributes['type'] = $container->getPersistentModel()->getName();
		return $attributes;
	}
}