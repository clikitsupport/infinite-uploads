<?php
namespace ClikIT\Infinite_Uploads\Aws\IoTDeviceAdvisor;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Core Device Advisor** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSuiteDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSuiteDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSuiteDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSuiteDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSuiteDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSuiteDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSuiteRun(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSuiteRunAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSuiteRunReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSuiteRunReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSuiteDefinitions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSuiteDefinitionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSuiteRuns(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSuiteRunsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startSuiteRun(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startSuiteRunAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopSuiteRun(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopSuiteRunAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateSuiteDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateSuiteDefinitionAsync(array $args = [])
 */
class IoTDeviceAdvisorClient extends AwsClient {}
