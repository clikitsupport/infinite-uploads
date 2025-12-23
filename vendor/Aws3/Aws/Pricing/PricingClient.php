<?php
namespace ClikIT\Infinite_Uploads\Aws\Pricing;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Price List Service** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeServices(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeServicesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAttributeValues(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAttributeValuesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPriceListFileUrl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPriceListFileUrlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getProducts(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getProductsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPriceLists(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPriceListsAsync(array $args = [])
 */
class PricingClient extends AwsClient {}
