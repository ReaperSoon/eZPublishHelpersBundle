<?php
/**
 * Created by PhpStorm.
 * User: Steve Cohen
 * Date: 29/12/17
 * Time: 16:25
 */

namespace SteveCohen\EzPublishHelpersBundle\Helper;

use eZ\Publish\Core\SignalSlot\Repository;

class ConvertHelper
{

    /** @var \eZ\Publish\Core\SignalSlot\Repository */
    protected $repository;

    /** @var  \SteveCohen\EzPublishHelpersBundle\Helper\ContentHelper */
    protected $contentHelper;

    protected $contentService;

    protected $locationService;

    protected $contentTypeService;

    protected $fieldTypeService;

    protected $searchService;

    public function  __construct(Repository $repository, $contentHelper)
    {
        $this->repository = $repository;
        $this->contentService = $repository->getContentService();
        $this->locationService = $repository->getLocationService();
        $this->contentTypeService = $repository->getContentTypeService();
        $this->fieldTypeService = $repository->getFieldTypeService();
        $this->searchService = $repository->getSearchService();
        $this->contentHelper = $contentHelper;
    }

    /**
     * @param $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function locationToContent( $location )
    {
        $content = $this->contentService->loadContent( $location->contentId );
        return $content;
    }

    /**
     * @param $locationArray
     *
     * @return array
     */
    public function locationArrayToContentArray( $locationArray )
    {
        $contentArray = array();
        foreach ( $locationArray as $location )
        {
            $contentArray[] = $this->locationToContent( $location );
        }
        return $contentArray;
    }

    /**
     * @param $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function contentToMainLocation( $content )
    {
        $location = $this->locationService->loadLocation( $content->versionInfo->contentInfo->mainLocationId );
        return $location;
    }

    /**
     * @param $contentArray
     *
     * @return array
     */
    public function contentArrayToContentArrayByIdentifierKeys( $contentArray )
    {
        $arrayByIdentifierKeys = array();
        foreach ( $contentArray as $content )
        {
            $arrayByIdentifierKeys[$this->contentHelper->extractClassIdentifier( $content ) ] = $content;
        }
        return $arrayByIdentifierKeys;
    }

}
