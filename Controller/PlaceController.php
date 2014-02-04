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
use EzSystems\DemoBundle\Helper\PlaceHelper;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class PlaceController extends Controller
{
    /**
     * Displays all the places contained in a place list
     *
     * @param int $locationId id of the place list
     *
     * @return Response
     */
    public function listPlaceListAction( $locationId )
    {
        /** @var PlaceHelper $placeHelper */
        $placeHelper = $this->get( 'ezdemo.place_helper' );

        $places = $placeHelper->getPlaceList( $locationId );

        return $this->render(
            'eZDemoBundle:parts/place:place_list.html.twig',
            array( 'places' => $places ),
            new Response()
        );
    }

    /**
     * Displays all the places sorted by proximity contained in a place list
     * The max distance of the places displayed can be modified in the default config
     *
     * @param int $locationId
     * @param float $latitude
     * @param float $longitude
     *
     * @return Response
     */
    public function listPlaceListSortedAction( $locationId, $latitude, $longitude )
    {
        /** @var PlaceHelper $placeHelper */
        $placeHelper = $this->get( 'ezdemo.place_helper' );

        $sortClauses = array(
            new SortClause\MapLocationDistance(
                "place",
                "location",
                $latitude,
                $longitude,
                Query::SORT_ASC,
                $this->getConfigResolver()->getParameter( 'languages' )[0]
            )
        );

        $places = $placeHelper->getPlaceListSorted( $locationId, $latitude, $longitude, $sortClauses );

        return $this->render(
            'eZDemoBundle:parts/place:place_list.html.twig',
            array( 'places' => $places ),
            new Response()
        );
    }
}
