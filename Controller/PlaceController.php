<?php
/**
 * File containing the PlaceController class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace EzSystems\DemoBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use EzSystems\DemoBundle\Helper\CriteriaHelper;
use EzSystems\DemoBundle\Helper\PlaceHelper;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class PlaceController extends Controller
{
    //TODO listPlaceNotSorted action

    //TODO comment
    public function listPlaceListSortedAction( $locationId, $latitude, $longitude )
    {
        // Getting location and content from ezpublish dedicated services
        $repository = $this->getRepository();
        $location = $repository->getLocationService()->loadLocation( $locationId );

        /** @var PlaceHelper $placeHelper */
        $placeHelper = $this->get( 'ezdemo.place_helper' );

        /** @var LocaleConverterInterface $localeConverter */
        $localeConverter = $this->get( 'ezpublish.locale.converter' );

        $query = new Query();
        $query->filter = $placeHelper->generatePlaceListCriterion( $location, $latitude, $longitude );

        $query->sortClauses = array(
            new SortClause\MapLocationDistance(
                "place",
                "location",
                $latitude,
                $longitude,
                Query::SORT_ASC,
                $localeConverter->convertToEz( $this->getRequest()->getLocale() )
            )
        );

        $searchResults = $this->getRepository()->getSearchService()->findContent( $query );

        $places = array();

        foreach ( $searchResults->searchHits as $hit )
        {
            $places[] = $hit->valueObject;
        }

        return $this->render(
            'eZDemoBundle:parts/place:place_list.html.twig',
            array( 'places' => $places ),
            new Response()
        );

    }
}
