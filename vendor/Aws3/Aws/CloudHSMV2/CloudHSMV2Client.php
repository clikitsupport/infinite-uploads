<?php
namespace ClikIT\Infinite_Uploads\Aws\CloudHSMV2;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CloudHSM V2** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result copyBackupToRegion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise copyBackupToRegionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createHsm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createHsmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteBackup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteBackupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteHsm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteHsmAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeBackups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeBackupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClustersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result initializeCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise initializeClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyBackupAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyBackupAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result restoreBackup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise restoreBackupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class CloudHSMV2Client extends AwsClient {}
