<?php
namespace ClikIT\Infinite_Uploads\Aws\Route53RecoveryCluster;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Route53 Recovery Cluster** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRoutingControlState(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRoutingControlStateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRoutingControls(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRoutingControlsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateRoutingControlState(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateRoutingControlStateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateRoutingControlStates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateRoutingControlStatesAsync(array $args = [])
 */
class Route53RecoveryClusterClient extends AwsClient {}
