<?php
/**
 * Created by PhpStorm.
 * User: Steve Cohen
 * Date: 29/12/17
 * Time: 16:25
 */

namespace SteveCohen\EzPublishHelpersBundle\Helper;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use eZ\Publish\Core\SignalSlot\Repository;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

class GenericFindHelper
{
    /** @var \eZ\Publish\Core\SignalSlot\Repository */
    protected $repository;

    protected $configResolver;

    protected $contentService;

    protected $contentTypeService;

    protected $fieldTypeService;

    protected $searchService;

    protected $locationService;

    public function  __construct(Repository $repository, ConfigResolver $configResolver)
    {
        $this->repository = $repository;
        $this->configResolver = $configResolver;
        $this->contentService = $repository->getContentService();
        $this->contentTypeService = $repository->getContentTypeService();
        $this->fieldTypeService = $repository->getFieldTypeService();
        $this->searchService = $repository->getSearchService();
        $this->locationService = $repository->getLocationService();
    }

    /**
     * Récupère tous les enfants dans l'arborescence
     * @param  $parentLocation
     * @param array $contentTypeIdentifier
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findChildrenTree(Content\Location $parentLocation, $contentTypeIdentifier = null, $limit = -1, $offset = -1)
    {
        $query = new LocationQuery();


        $criteria = array(
            new Criterion\Visibility( Criterion\Visibility::VISIBLE ),
            new Criterion\Subtree( $parentLocation->pathString ),
            new Criterion\LanguageCode( $this->configResolver->getParameter( 'languages' )[0] )
        );
        if ( $contentTypeIdentifier )
        {
            $criteria[] = new Criterion\ContentTypeIdentifier( $contentTypeIdentifier );
        }
        if ( $limit > 0 )
        {
            $query->limit = $limit;
        }
        if ( $offset > 0 )
        {
            $query->offset = $offset;
        }
        $query->filter = new Criterion\LogicalAnd( $criteria );
        $query->sortClauses = array( new Content\Query\SortClause\Location\Priority( Content\Query::SORT_ASC ) );

        return $this->prepareResults( $this->searchService->findLocations( $query ) );
    }

    /**
     * Liste les enfants triés par priorités définies dans le parent
     *
     * @param Content\Location $parentLocation
     * @param array $contentTypeIdentifier
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function findChildrenList(Content\Location $parentLocation, $contentTypeIdentifier = null, $limit = -1, $offset = -1)
    {
        $query = new LocationQuery();

        $criteria = array(
            new Criterion\Visibility( Criterion\Visibility::VISIBLE ),
            new Criterion\ParentLocationId( $parentLocation->id ),
            new Criterion\LanguageCode( $this->configResolver->getParameter( 'languages' )[0] )
        );
        if ( $contentTypeIdentifier )
        {
            $criteria[] = new Criterion\ContentTypeIdentifier( $contentTypeIdentifier );
        }
        if ( $limit > 0 )
        {
            $query->limit = $limit;
        }
        if ( $offset > 0 )
        {
            $query->offset = $offset;
        }

        $query->filter = new Criterion\LogicalAnd( $criteria );

        $query->sortClauses = array( new Content\Query\SortClause\Location\Priority( Content\Query::SORT_ASC ) );

        return $this->prepareResults( $this->searchService->findLocations( $query ) );
    }

    /**
     *
     * Liste les enfants triés par priorités définies dans le parent en fonction du groupe de classe
     *
     * @param $parentLocation
     * @param $classGroudId
     * @param null $excludeContentType
     *
     * @return array
     */
    public function findChildrenListByParentAndContentTypeGroupId(Content\Location $parentLocation, $classGroudId, $excludeContentType = null)
    {
        $query = new LocationQuery();

        $criteria[] = new Criterion\LogicalAnd(
            array(
                new Criterion\Visibility( Criterion\Visibility::VISIBLE ),
                new Criterion\ParentLocationId( $parentLocation->id ),
                new Criterion\ContentTypeGroupId( $classGroudId ),
            )
        );
        if ( $excludeContentType )
        {
            $criteria[] = new Criterion\LogicalNot(
                new Criterion\ContentTypeIdentifier( $excludeContentType )
            );
        }

        $query->filter = new Criterion\LogicalAnd( $criteria );

        $query->sortClauses = array( new Content\Query\SortClause\Location\Priority( Content\Query::SORT_ASC ) );

        return $this->prepareResults( $this->searchService->findLocations( $query ) );
    }

    /**
     *
     * Find all reverse relation of a content filtered by type
     *
     * @param $content
     * @param array $contentTypeIdentifier
     *
     * @return array
     */
    public function findReverseRelationsByContentType( $content, $contentTypeIdentifier = array() )
    {
        $reverseRelations = $this->contentService->loadReverseRelations( $content->contentInfo );
        $relations = array();

        if ( count( $reverseRelations ) )
        {
            foreach ( $reverseRelations as $relation )
            {
                $contentInfo = $this->contentService->loadContentInfo( $relation->sourceContentInfo->id );
                $contentType = $this->contentTypeService->loadContentType( $contentInfo->contentTypeId );
                $identifier = $contentType->identifier;
                if ( empty( $contentType ) || in_array( $identifier, $contentTypeIdentifier ) )
                {
                    $relation = $this->locationService->loadLocation( $relation->sourceContentInfo->mainLocationId  );
                    if ( $relation->invisible == false )
                    {
                        $relations[] = $relation;
                    }
                }
            }
        }
        return $relations;
    }

    /**
     *
     * Find all relation of a content filtered by type
     *
     * @param $content
     * @param array $contentTypeIdentifier
     *
     * @return array
     */
    public function findRelationsByContentType( $content, $contentTypeIdentifier = array() )
    {
        $relatedObjects = $this->contentService->loadRelations( $content->versionInfo );
        $relations = array();
        if ( count( $relatedObjects ) )
        {
            foreach ( $relatedObjects as $relation )
            {
                $contentInfo = $this->contentService->loadContentInfo( $relation->destinationContentInfo->id );
                $contentType = $this->contentTypeService->loadContentType( $contentInfo->contentTypeId );
                $identifier = $contentType->identifier;
                if ( empty( $contentType ) || in_array( $identifier, $contentTypeIdentifier ) )
                {
                    $relation = $this->locationService->loadLocation( $relation->destinationContentInfo->mainLocationId  );
                    if ( $relation->invisible == false )
                    {
                        $relations[] = $relation;
                    }
                }
            }
        }
        return $relations;
    }

    /**
     *
     * Get an array of all relations of a content by field name
     *
     * @param Content\Content $content
     * @param $fieldName
     *
     * @return array
     */
    public function findRelationObjectsFromField( Content\Content $content, $fieldName )
    {
        $destinationContentIds = $content->getField( $fieldName )->value->destinationContentIds;
        $relatedObjects = array();
        foreach ( $destinationContentIds as $id )
        {
            $relatedObjects[] = $this->contentService->loadContent( $id );
        }
        return $relatedObjects;
    }

    /**
     *
     * Find the first ancestor of a type. If not found, it return null
     *
     * @param $currentLocation
     * @param $parentContentType
     *
     * @return Content\Location|null
     */
    public function findFirstParentOfType( $currentLocation, $parentContentType )
    {
        $parentLocation = $this->locationService->loadLocation( $currentLocation->id );
        $contentIdentifier = $this->contentTypeService->loadContentType( $parentLocation->contentInfo->contentTypeId )->identifier;

        while ( $contentIdentifier !== $parentContentType && $parentLocation->id != 2 )
        {
            $parentLocation = $this->locationService->loadLocation( $parentLocation->parentLocationId );
            $contentIdentifier = $this->contentTypeService->loadContentType( $parentLocation->contentInfo->contentTypeId )->identifier;
        }

        if ( $parentLocation->id != 2 )
        {
            return $parentLocation;
        }
        return null;
    }

    /**
     *
     * Return the first parent of a location
     *
     * @param $currentLocation
     *
     * @return Content\Location
     */
    public function findFirstParent( $currentLocation )
    {
        $parentLocation = $this->locationService->loadLocation( $currentLocation->parentLocationId );

        return $parentLocation;
    }

    /**
     * Insère les résultats dans un tableau
     *
     * @param $results
     * @return array
     */
    private function prepareResults($results)
    {
        $res = array();
        foreach ( $results->searchHits as $hit )
        {
            /**
             *
             * @var $hit \eZ\Publish\API\Repository\Values\Content\Search\SearchHit
             */
            $res[] = $hit->valueObject;
        }

        return $res;
    }
}
