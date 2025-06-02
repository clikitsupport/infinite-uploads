<?php
namespace ClikIT\Infinite_Uploads\Aws\BedrockDataAutomationRuntime;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Runtime for Amazon Bedrock Data Automation** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDataAutomationStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDataAutomationStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result invokeDataAutomationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise invokeDataAutomationAsyncAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class BedrockDataAutomationRuntimeClient extends AwsClient {}
