<?php
namespace ClikIT\Infinite_Uploads\Aws\GeoMaps;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Location Service Maps V2** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result getGlyphs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getGlyphsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSprites(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSpritesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStaticMap(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStaticMapAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStyleDescriptor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStyleDescriptorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTileAsync(array $args = [])
 */
class GeoMapsClient extends AwsClient {}
