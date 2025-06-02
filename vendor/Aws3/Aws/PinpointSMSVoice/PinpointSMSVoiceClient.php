<?php
namespace ClikIT\Infinite_Uploads\Aws\PinpointSMSVoice;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Pinpoint SMS and Voice Service** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createConfigurationSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createConfigurationSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createConfigurationSetEventDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createConfigurationSetEventDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteConfigurationSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteConfigurationSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteConfigurationSetEventDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteConfigurationSetEventDestinationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getConfigurationSetEventDestinations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getConfigurationSetEventDestinationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listConfigurationSets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listConfigurationSetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendVoiceMessage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendVoiceMessageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateConfigurationSetEventDestination(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateConfigurationSetEventDestinationAsync(array $args = [])
 */
class PinpointSMSVoiceClient extends AwsClient {}
