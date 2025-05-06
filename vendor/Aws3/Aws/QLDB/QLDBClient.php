<?php
namespace ClikIT\Infinite_Uploads\Aws\QLDB;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon QLDB** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelJournalKinesisStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelJournalKinesisStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLedger(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLedgerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLedger(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLedgerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeJournalKinesisStream(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeJournalKinesisStreamAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeJournalS3Export(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeJournalS3ExportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLedger(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLedgerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result exportJournalToS3(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise exportJournalToS3Async(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getBlock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getBlockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDigest(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDigestAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRevision(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRevisionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listJournalKinesisStreamsForLedger(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listJournalKinesisStreamsForLedgerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listJournalS3Exports(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listJournalS3ExportsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listJournalS3ExportsForLedger(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listJournalS3ExportsForLedgerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLedgers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLedgersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result streamJournalToKinesis(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise streamJournalToKinesisAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateLedger(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateLedgerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateLedgerPermissionsMode(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateLedgerPermissionsModeAsync(array $args = [])
 */
class QLDBClient extends AwsClient {}
