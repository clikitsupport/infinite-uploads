<?php
namespace ClikIT\Infinite_Uploads\Aws\LaunchWizard;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Launch Wizard** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDeployment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDeploymentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDeployment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDeploymentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDeployment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDeploymentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getWorkload(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getWorkloadAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getWorkloadDeploymentPattern(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getWorkloadDeploymentPatternAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDeploymentEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDeploymentEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDeployments(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDeploymentsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listWorkloadDeploymentPatterns(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listWorkloadDeploymentPatternsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listWorkloads(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listWorkloadsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class LaunchWizardClient extends AwsClient {}
