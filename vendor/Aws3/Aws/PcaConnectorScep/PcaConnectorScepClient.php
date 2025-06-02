<?php
namespace ClikIT\Infinite_Uploads\Aws\PcaConnectorScep;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Private CA Connector for SCEP** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createChallenge(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createChallengeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createConnector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createConnectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteChallenge(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteChallengeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteConnector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteConnectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getChallengeMetadata(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getChallengeMetadataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getChallengePassword(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getChallengePasswordAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getConnector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getConnectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listChallengeMetadata(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listChallengeMetadataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listConnectors(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listConnectorsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class PcaConnectorScepClient extends AwsClient {}
