<?php
namespace ClikIT\Infinite_Uploads\Aws\EBS;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Elastic Block Store** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result completeSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise completeSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSnapshotBlock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSnapshotBlockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listChangedBlocks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listChangedBlocksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSnapshotBlocks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSnapshotBlocksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putSnapshotBlock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putSnapshotBlockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startSnapshotAsync(array $args = [])
 */
class EBSClient extends AwsClient {}
