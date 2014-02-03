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
use eZ\Publish\API\Repository\Values\Content\Location;
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
     * Default limit for content list in menus.
     *
     * @var int
     */
    private $defaultMenuLimit;

    public function __construct( Repository $repository, $defaultMenuLimit )
    {
        $this->repository = $repository;
        $this->defaultMenuLimit = $defaultMenuLimit;
    }

    //TODO
    public function generatePlaceListCriterion( Location $location,$latitude, $longitude )
    {
        return new Criterion\LogicalAnd(
            array(
                new Criterion\ContentTypeIdentifier( "place" ),
                new Criterion\Subtree( $location->pathString ),
                new Criterion\MapLocationDistance(
                    "location",
                    Criterion\Operator::BETWEEN,
                    array(
                        0,//$this->configResolver->getParameter( 'ezdemo.places.place_list.min' ), //FIXME
                        3//$this->configResolver->getParameter( 'ezdemo.places.place_list.max' )
                    ),
                    $latitude,
                    $longitude
                )
            )
        );
    }

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
