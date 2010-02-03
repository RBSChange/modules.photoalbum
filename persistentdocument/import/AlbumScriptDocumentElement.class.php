<?php
class photoalbum_AlbumScriptDocumentElement extends import_ScriptDocumentElement
{
    
    /**
     * @return photoalbum_persistentdocument_album
     */
    protected function initPersistentDocument()
    {
        return photoalbum_AlbumService::getInstance()->getNewDocumentInstance();
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