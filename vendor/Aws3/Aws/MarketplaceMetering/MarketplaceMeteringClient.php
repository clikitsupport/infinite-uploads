<?php
namespace ClikIT\Infinite_Uploads\Aws\MarketplaceMetering;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWSMarketplace Metering** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchMeterUsage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchMeterUsageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result meterUsage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise meterUsageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerUsage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerUsageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result resolveCustomer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise resolveCustomerAsync(array $args = [])
 */
class MarketplaceMeteringClient extends AwsClient {}
