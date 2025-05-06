<?php
namespace ClikIT\Infinite_Uploads\Aws\Cloud9;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Cloud9** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createEnvironmentEC2(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createEnvironmentEC2Async(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createEnvironmentMembership(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createEnvironmentMembershipAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEnvironment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEnvironmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEnvironmentMembership(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEnvironmentMembershipAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEnvironmentMemberships(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEnvironmentMembershipsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEnvironmentStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEnvironmentStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEnvironments(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEnvironmentsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listEnvironments(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listEnvironmentsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateEnvironment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateEnvironmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateEnvironmentMembership(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateEnvironmentMembershipAsync(array $args = [])
 */
class Cloud9Client extends AwsClient {}
