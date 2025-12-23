<?php
namespace ClikIT\Infinite_Uploads\Aws\Repostspace;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS re:Post Private** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchAddRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchAddRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchRemoveRole(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchRemoveRoleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSpace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSpaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSpace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSpaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deregisterAdmin(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deregisterAdminAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSpace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSpaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSpaces(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSpacesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerAdmin(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerAdminAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result sendInvites(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise sendInvitesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateSpace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateSpaceAsync(array $args = [])
 */
class RepostspaceClient extends AwsClient {}
