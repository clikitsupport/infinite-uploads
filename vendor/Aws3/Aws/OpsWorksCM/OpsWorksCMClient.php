<?php
namespace ClikIT\Infinite_Uploads\Aws\OpsWorksCM;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS OpsWorks for Chef Automate** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateNode(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateNodeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createBackup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createBackupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createServer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createServerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteBackup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteBackupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteServer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteServerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAccountAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAccountAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeBackups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeBackupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeNodeAssociationStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeNodeAssociationStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeServers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeServersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateNode(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateNodeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result exportServerEngineAttribute(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise exportServerEngineAttributeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result restoreServer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise restoreServerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startMaintenance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startMaintenanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateServer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateServerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateServerEngineAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateServerEngineAttributesAsync(array $args = [])
 */
class OpsWorksCMClient extends AwsClient {}
