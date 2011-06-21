<?php
// change:slideshow
// <tal:block change:slideshow="docs medias" />
// <tal:block change:slideshow="docs photos; mediaGetter 'getMedia'; thumbHeight 40" options="lightbox: true, width: 400, height: 320" />
// <tal:block change:slideshow="docs products; mediaGetter 'getVisual'; descriptionGetter 'getShortDescription'; thumbHeight 40; imageHeight 200" options="width: 300, height: 300" />
//
// Handled parameters:
// - docs f_persistentdocument_PersistentDocument[]: the docs to show
// - mediaGetter string: if the docs are not media, specify the getter to get the visual
// - titleGetter string: default value is 'getTitleAsHtml' for medias and 'getLabelAsHtml' for other documents
// - descriptionGetter string: no description by default
// - thumbHeight: the thumbnails max height
// - thumbWidth: the thumbnails max width
// - imageHeight: the image max height
// - imageWidth: the image max width
// - options: a list of Galleria options (cf: http://galleria.aino.se/docs/1.2/options/)

/**
 * @package photoalbum.lib.phptal
 */
class PHPTAL_Php_Attribute_CHANGE_slideshow extends ChangeTalAttribute 
{
	protected function evaluateAll()
	{
		return true;
	}
	
	/**
	 * @param array $params
	 * @return string
	 */
	public static function renderSlideshow($params)
	{
		$context = website_BlockController::getInstance()->getContext();
		$context->addScript('modules.photoalbum.lib.js.galleria-classic');
		
		$slideshowId = uniqid();
		$class = self::getFromParams('class', $params);
		$classes = explode(' ', $class);
		if (!in_array('slideshow', $classes))
		{
			$classes[] = 'slideshow';
		}
		$class = trim(implode(' ', $classes));
		$html = '<div class="' . $class . '" id="slideshow-' . $slideshowId . '"></div>'; 

		// Render items.
		$thumbFormat = self::getFormat($params, 'thumb');
		$imageFormat = self::getFormat($params, 'image');
		$mediaGetter = self::getFromParams('mediaGetter', $params);
		$titleGetter = self::getFromParams('titleGetter', $params);
		$descriptionGetter = self::getFromParams('descriptionGetter', $params);
		$docs = self::getFromParams('docs', $params);
		$data = array();
		if (!f_util_ArrayUtils::isEmpty($docs))
		{
			foreach ($docs as $doc)
			{
				$docData = self::renderJSONItem($doc, $thumbFormat, $imageFormat, $mediaGetter, $titleGetter, $descriptionGetter);
				if ($docData !== null)
				{
					$data[] = $docData;
				}
			}
		}
		if (!count($data))
		{
			return '';
		}
		$html .= "\n<script type=\"text/javascript\">\n";
		$html .= 'var data' . $slideshowId . ' = ' . JsonService::getInstance()->encode($data);
		
		// Slideshow options.
		$options = self::getFromParams('options', $params);
		$options = '{' . $options . ($options ? ', ' : '') . 'dataSource: data' . $slideshowId . '}';
		$html .= "\njQuery('#slideshow-$slideshowId').galleria($options);\n</script>";
		return $html;
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $doc
	 * @param array $thumbFormat
	 * @param array $imageFormat
	 * @param string $mediaGetter
	 * @param string $titleGetter
	 * @param string $descriptionGetter
	 * @return string
	 */
	private function renderJSONItem($doc, $thumbFormat, $imageFormat, $mediaGetter, $titleGetter, $descriptionGetter)
	{
		$data = array();
		if ($doc instanceof media_persistentdocument_media)
		{
			$media = $doc;
			$data['title'] = (f_util_ClassUtils::methodExists($doc, $titleGetter)) ? $doc->{$titleGetter}() : $doc->getTitleAsHtml();
		}
		else if (f_util_ClassUtils::methodExists($doc, $mediaGetter))
		{
			$media = $doc->{$mediaGetter}();
			$data['title'] = (f_util_ClassUtils::methodExists($doc, $titleGetter)) ? $doc->{$titleGetter}() : $doc->getLabelAsHtml();
			if ($doc->getPersistentModel()->hasUrl())
			{
				$data['link'] = LinkHelper::getDocumentUrl($doc);
			}
		}
		else 
		{
			return null;
		}
		
		$data['thumb'] = LinkHelper::getDocumentUrl($media, null, $thumbFormat);
		$data['image'] = LinkHelper::getDocumentUrl($media, null, $imageFormat);
		$data['big'] = LinkHelper::getDocumentUrl($media);
		if (f_util_ClassUtils::methodExists($doc, $descriptionGetter))
		{
			$data['description'] = $doc->{$descriptionGetter}();
		}
		return $data;
	}
	
	/**
	 * @param string $key
	 * @param array $params
	 * @return string
	 */
	private static function getFromParams($key, $params, $default = null)
	{
		return (array_key_exists($key, $params)) ? $params[$key] : $default;
	}
	
	/**
	 * @return array
	 */
	private function getFormat($params, $paramPrefix)
	{
		$format = array();
		$width = self::getFromParams($paramPrefix . 'Width', $params);
		if (is_numeric($width) && $width > 0)
		{
			$format['max-width'] = $width . 'px';
		}
		$height = self::getFromParams($paramPrefix . 'Height', $params);	
		if (is_numeric($height) && $height > 0)
		{
			$format['max-height'] = $height . 'px';
		}
		return $format;
	}
}