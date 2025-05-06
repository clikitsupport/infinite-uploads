<?php
namespace ClikIT\Infinite_Uploads\Aws\MigrationHubConfig;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Migration Hub Config** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createHomeRegionControl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createHomeRegionControlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteHomeRegionControl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteHomeRegionControlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeHomeRegionControls(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeHomeRegionControlsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getHomeRegion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getHomeRegionAsync(array $args = [])
 */
class MigrationHubConfigClient extends AwsClient {}
