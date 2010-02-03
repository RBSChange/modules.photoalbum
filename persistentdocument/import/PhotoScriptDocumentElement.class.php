<?php

class photoalbum_PhotoScriptDocumentElement extends import_ScriptDocumentElement
{

    /**
     * @return photoalbum_persistentdocument_album
     */
    protected function initPersistentDocument ()
    {
        return photoalbum_PhotoService::getInstance()->getNewDocumentInstance();
    }

    /**
     * @return array
     */
    protected function getDocumentProperties ()
    {
        $properties = parent::getDocumentProperties();
        if (isset($properties['mediarefid']))
        {
            $media = $this->script->getElementById($properties['mediarefid']);
            if ($media !== null)
            {
                $properties['media'] = $media->getPersistentDocument();
            }
            unset($properties['mediarefid']);
        }
        if (isset($properties['thumbnailrefid']))
        {
            $media = $this->script->getElementById($properties['thumbnailrefid']);
            if ($media !== null)
            {
                $properties['thumbnail'] = $media->getPersistentDocument();
            }
            unset($properties['thumbnailrefid']);
        }
        
        if (isset($properties['mediahdrefid']))
        {
            $media = $this->script->getElementById($properties['mediahdrefid']);
            if ($media !== null)
            {
                $properties['mediahd'] = $media->getPersistentDocument();
            }
            unset($properties['mediahdrefid']);
        }
        
        return $properties;
    }

    public function endProcess ()
    {
        $document = $this->getPersistentDocument();
        if ($document->getPublicationstatus() == 'DRAFT')
        {
            $document->getDocumentService()->activate($document->getId());
        }
    }

}