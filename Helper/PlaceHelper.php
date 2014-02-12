<?php
/**
 * File containing the PlaceHelper class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace EzSystems\DemoBundle\Helper;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;

/**
 * Helper for menus
 */
class PlaceHelper
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * Min distance in kilometers to display items in the place list
     *
     * @var int
     */
    private $placeListMinDist;

    /**
     * Max distance in kilometers to display items in the place list
     *
     * @var int
     */
    private $placeListMaxDist;

    public function __construct( Repository $repository, $placeListMinDist, $placeListMaxDist )
    {
        $this->repository = $repository;
        $this->placeListMinDist = $placeListMinDist;
        $this->placeListMaxDist = $placeListMaxDist;
    }

    /**
     * Returns all places contained in a place_list
     *
     * @param int|string $locationId id of a place_list
     * @param string|string[] $contentTypes to be retrieved
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    public function getPlaceList( $locationId, $contentTypes )
    {
        $location = $this->repository->getLocationService()->loadLocation( $locationId );

        $query = new Query();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentTypeIdentifier( $contentTypes ),
                new Criterion\Subtree( $location->pathString ),
            )
        );

        $searchResults = $this->repository->getSearchService()->findContent( $query );

        return $this->buildContentListFromSearchResult( $searchResults );
    }

    /**
     * Returns all places contained in a place_list that are located between the range defined in
     * the default configuration. A sort clause array can be provided in order to sort the results.
     *
     * @param int|string $locationId
     * @param float $latitude
     * @param float $longitude
     * @param string|string[] $contentTypes to be retrieved
     * @param int $maxDist Maximum distance for the search in km
     * @param array $sortClauses
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    public function getPlaceListSorted( $locationId, $latitude, $longitude, $contentTypes, $maxDist = null, $sortClauses = array() )
    {
        $location = $this->repository->getLocationService()->loadLocation( $locationId );

        if ( $maxDist == null )
        {
            $maxDist = $this->placeListMaxDist;
        }

        $query = new Query();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentTypeIdentifier( $contentTypes ),
                new Criterion\Subtree( $location->pathString ),
                new Criterion\MapLocationDistance(
                    "location",
                    Criterion\Operator::BETWEEN,
                    array(
                        $this->placeListMinDist,
                        $maxDist
                    ),
                    $latitude,
                    $longitude
                )
            )
        );

        $query->sortClauses = $sortClauses;

        $searchResults = $this->repository->getSearchService()->findContent( $query );

        return $this->buildContentListFromSearchResult( $searchResults );
    }

    //FIXME this method is a copy of the one from the MenuHelper we need to find a way to externalize them
    /**
     * Builds a Content list from $searchResult.
     * Returned array consists of a hash of Content objects, indexed by their ID.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    private function buildContentListFromSearchResult( SearchResult $searchResult )
    {
        $contentList = array();
        foreach ( $searchResult->searchHits as $searchHit )
        {
            $contentList[$searchHit->valueObject->contentInfo->id] = $searchHit->valueObject;
        }

        return $contentList;
    }
}
