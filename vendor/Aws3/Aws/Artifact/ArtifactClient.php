<?php
namespace ClikIT\Infinite_Uploads\Aws\Artifact;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Artifact** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccountSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccountSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getReportMetadata(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getReportMetadataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTermForReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTermForReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCustomerAgreements(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCustomerAgreementsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listReports(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listReportsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccountSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccountSettingsAsync(array $args = [])
 */
class ArtifactClient extends AwsClient {}
