<?php
namespace ClikIT\Infinite_Uploads\Aws\GeoPlaces;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Location Service Places V2** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result autocomplete(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise autocompleteAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result geocode(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise geocodeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPlace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPlaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result reverseGeocode(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise reverseGeocodeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result searchNearby(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise searchNearbyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result searchText(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise searchTextAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result suggest(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise suggestAsync(array $args = [])
 */
class GeoPlacesClient extends AwsClient {}
