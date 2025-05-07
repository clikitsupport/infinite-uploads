<?php
namespace ClikIT\Infinite_Uploads\Aws\CloudHsm;

use ClikIT\Infinite_Uploads\Aws\Api\ApiProvider;
use ClikIT\Infinite_Uploads\Aws\Api\DocModel;
use ClikIT\Infinite_Uploads\Aws\Api\Service;
use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with **AWS CloudHSM**.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result addTagsToResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addTagsToResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createHapg(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createHapgAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createHsm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createHsmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLunaClient(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLunaClientAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteHapg(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteHapgAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteHsm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteHsmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLunaClient(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLunaClientAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeHapg(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeHapgAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeHsm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeHsmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLunaClient(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLunaClientAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getConfigFiles(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getConfigFilesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAvailableZones(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAvailableZonesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listHapgs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listHapgsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listHsms(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listHsmsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLunaClients(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLunaClientsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyHapg(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyHapgAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyHsm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyHsmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyLunaClient(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyLunaClientAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeTagsFromResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeTagsFromResourceAsync(array $args = [])
 */
class CloudHsmClient extends AwsClient {}
