<?php
namespace ClikIT\Infinite_Uploads\Aws\IotDataPlane;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Data Plane** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteThingShadow(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteThingShadowAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRetainedMessage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRetainedMessageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getThingShadow(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getThingShadowAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listNamedShadowsForThing(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listNamedShadowsForThingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRetainedMessages(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRetainedMessagesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result publish(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise publishAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateThingShadow(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateThingShadowAsync(array $args = [])
 */
class IotDataPlaneClient extends AwsClient {}
