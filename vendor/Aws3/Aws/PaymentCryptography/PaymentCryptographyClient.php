<?php
namespace ClikIT\Infinite_Uploads\Aws\PaymentCryptography;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Payment Cryptography Control Plane** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result exportKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise exportKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAliasAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getParametersForExport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getParametersForExportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getParametersForImport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getParametersForImportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPublicKeyCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPublicKeyCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAliases(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAliasesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listKeys(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listKeysAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result restoreKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise restoreKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startKeyUsage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startKeyUsageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopKeyUsage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopKeyUsageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAlias(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAliasAsync(array $args = [])
 */
class PaymentCryptographyClient extends AwsClient {}
