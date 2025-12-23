<?php
namespace ClikIT\Infinite_Uploads\Aws\PCS;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Parallel Computing Service** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createComputeNodeGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createComputeNodeGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createQueue(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createQueueAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteComputeNodeGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteComputeNodeGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteQueue(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteQueueAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getComputeNodeGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getComputeNodeGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getQueue(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getQueueAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listClusters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listClustersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listComputeNodeGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listComputeNodeGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listQueues(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listQueuesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerComputeNodeGroupInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerComputeNodeGroupInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateComputeNodeGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateComputeNodeGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateQueue(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateQueueAsync(array $args = [])
 */
class PCSClient extends AwsClient {}
