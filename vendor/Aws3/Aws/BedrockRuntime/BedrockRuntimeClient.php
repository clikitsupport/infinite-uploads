<?php
namespace ClikIT\Infinite_Uploads\Aws\BedrockRuntime;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Bedrock Runtime** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result applyGuardrail(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise applyGuardrailAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result converse(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise converseAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result converseStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise converseStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAsyncInvoke(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAsyncInvokeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result invokeModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise invokeModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result invokeModelWithResponseStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise invokeModelWithResponseStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAsyncInvokes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAsyncInvokesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startAsyncInvoke(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startAsyncInvokeAsync(array $args = [])
 */
class BedrockRuntimeClient extends AwsClient {}
