<?php
/**
 * Created by PhpStorm.
 * User: Steve Cohen
 * Date: 29/12/17
 * Time: 16:25
 */

namespace SteveCohen\EzPublishHelpersBundle\Helper;

use eZ\Publish\Core\SignalSlot\Repository;

class ContentHelper
{

    /** @var \eZ\Publish\Core\SignalSlot\Repository */
    protected $repository;

    protected $contentService;

    protected $locationService;

    protected $contentTypeService;

    protected $fieldTypeService;

    protected $searchService;

    public function  __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->contentService = $repository->getContentService();
        $this->locationService = $repository->getLocationService();
        $this->contentTypeService = $repository->getContentTypeService();
        $this->fieldTypeService = $repository->getFieldTypeService();
        $this->searchService = $repository->getSearchService();
    }

    /**
     *
     * Get the content class identifier string
     *
     * @param $content
     *
     * @return string
     */
    public function extractClassIdentifier( $content )
    {
        $contentType = $this->contentTypeService->loadContentType( $content->contentInfo->contentTypeId );
        return $contentType->identifier;
    }


}
