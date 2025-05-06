<?php
namespace ClikIT\Infinite_Uploads\Aws\LexRuntimeV2;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Lex Runtime V2** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSession(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSessionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSession(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSessionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putSession(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putSessionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result recognizeText(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise recognizeTextAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result recognizeUtterance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise recognizeUtteranceAsync(array $args = [])
 */
class LexRuntimeV2Client extends AwsClient {}
