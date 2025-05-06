<?php
namespace ClikIT\Infinite_Uploads\Aws\Keyspaces;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Keyspaces** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createKeyspace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createKeyspaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteKeyspace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteKeyspaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getKeyspace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getKeyspaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTableAutoScalingSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTableAutoScalingSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listKeyspaces(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listKeyspacesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTables(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTypes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTypesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result restoreTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise restoreTableAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateKeyspace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateKeyspaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateTable(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateTableAsync(array $args = [])
 */
class KeyspacesClient extends AwsClient {}
