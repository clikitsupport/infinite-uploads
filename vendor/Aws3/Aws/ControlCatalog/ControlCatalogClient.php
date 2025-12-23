<?php
namespace ClikIT\Infinite_Uploads\Aws\ControlCatalog;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Control Catalog** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result getControl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getControlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCommonControls(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCommonControlsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listControls(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listControlsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDomains(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDomainsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listObjectives(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listObjectivesAsync(array $args = [])
 */
class ControlCatalogClient extends AwsClient {}
