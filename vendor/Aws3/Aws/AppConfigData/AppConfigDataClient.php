<?php
namespace ClikIT\Infinite_Uploads\Aws\AppConfigData;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS AppConfig Data** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLatestConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLatestConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startConfigurationSession(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startConfigurationSessionAsync(array $args = [])
 */
class AppConfigDataClient extends AwsClient {}
