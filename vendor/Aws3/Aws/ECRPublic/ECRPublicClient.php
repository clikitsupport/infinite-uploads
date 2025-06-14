<?php
namespace ClikIT\Infinite_Uploads\Aws\ECRPublic;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Elastic Container Registry Public** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchCheckLayerAvailability(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchCheckLayerAvailabilityAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchDeleteImage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchDeleteImageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result completeLayerUpload(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise completeLayerUploadAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createRepository(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createRepositoryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRepository(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRepositoryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRepositoryPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRepositoryPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeImageTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeImageTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeImages(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeImagesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeRegistries(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeRegistriesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeRepositories(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeRepositoriesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAuthorizationToken(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAuthorizationTokenAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRegistryCatalogData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRegistryCatalogDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRepositoryCatalogData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRepositoryCatalogDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRepositoryPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRepositoryPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result initiateLayerUpload(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise initiateLayerUploadAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putImage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putImageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRegistryCatalogData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRegistryCatalogDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRepositoryCatalogData(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRepositoryCatalogDataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setRepositoryPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setRepositoryPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result uploadLayerPart(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise uploadLayerPartAsync(array $args = [])
 */
class ECRPublicClient extends AwsClient {}
