<?php
namespace ClikIT\Infinite_Uploads\Aws\MediaPackageVod;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Elemental MediaPackage VOD** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result configureLogs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise configureLogsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAsset(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAssetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createPackagingConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createPackagingConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createPackagingGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createPackagingGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAsset(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAssetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePackagingConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePackagingConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePackagingGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePackagingGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAsset(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAssetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describePackagingConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describePackagingConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describePackagingGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describePackagingGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAssets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAssetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPackagingConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPackagingConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPackagingGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPackagingGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updatePackagingGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updatePackagingGroupAsync(array $args = [])
 */
class MediaPackageVodClient extends AwsClient {}
