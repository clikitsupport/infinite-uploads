<?php
namespace ClikIT\Infinite_Uploads\Aws\Support;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * AWS Support client.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result addAttachmentsToSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addAttachmentsToSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addCommunicationToCase(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addCommunicationToCaseAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCase(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCaseAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAttachment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAttachmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeCases(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeCasesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeCommunications(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeCommunicationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeCreateCaseOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeCreateCaseOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeServices(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeServicesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSeverityLevels(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSeverityLevelsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSupportedLanguages(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSupportedLanguagesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTrustedAdvisorCheckRefreshStatuses(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTrustedAdvisorCheckRefreshStatusesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTrustedAdvisorCheckResult(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTrustedAdvisorCheckResultAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTrustedAdvisorCheckSummaries(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTrustedAdvisorCheckSummariesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTrustedAdvisorChecks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTrustedAdvisorChecksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result refreshTrustedAdvisorCheck(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise refreshTrustedAdvisorCheckAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result resolveCase(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise resolveCaseAsync(array $args = [])
 */
class SupportClient extends AwsClient {}
