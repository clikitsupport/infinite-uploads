<?php
namespace ClikIT\Infinite_Uploads\Aws\ivschat;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Interactive Video Service Chat** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createChatToken(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createChatTokenAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLoggingConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLoggingConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createRoom(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createRoomAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLoggingConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLoggingConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteMessage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteMessageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRoom(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRoomAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disconnectUser(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disconnectUserAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLoggingConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLoggingConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRoom(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRoomAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLoggingConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLoggingConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRooms(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRoomsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendEvent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendEventAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateLoggingConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateLoggingConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateRoom(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateRoomAsync(array $args = [])
 */
class ivschatClient extends AwsClient {}
