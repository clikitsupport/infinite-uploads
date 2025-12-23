<?php
namespace ClikIT\Infinite_Uploads\Aws\DSQL;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Aurora DSQL** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createMultiRegionClusters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createMultiRegionClustersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteMultiRegionClusters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteMultiRegionClustersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getVpcEndpointServiceName(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getVpcEndpointServiceNameAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listClusters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listClustersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateClusterAsync(array $args = [])
 */
class DSQLClient extends AwsClient {}
