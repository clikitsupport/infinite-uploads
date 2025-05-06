<?php
namespace ClikIT\Infinite_Uploads\Aws\OAM;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **CloudWatch Observability Access Manager** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLink(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLinkAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSink(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSinkAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLink(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLinkAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSink(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSinkAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getLink(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getLinkAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSink(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSinkAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSinkPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSinkPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAttachedLinks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAttachedLinksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLinks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLinksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSinks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSinksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putSinkPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putSinkPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateLink(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateLinkAsync(array $args = [])
 */
class OAMClient extends AwsClient {}
