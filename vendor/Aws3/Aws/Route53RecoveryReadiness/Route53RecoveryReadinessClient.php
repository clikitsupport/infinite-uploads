<?php
namespace ClikIT\Infinite_Uploads\Aws\Route53RecoveryReadiness;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Route53 Recovery Readiness** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCell(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCellAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCrossAccountAuthorization(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCrossAccountAuthorizationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createReadinessCheck(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createReadinessCheckAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createRecoveryGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createRecoveryGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createResourceSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createResourceSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCell(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteCellAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCrossAccountAuthorization(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteCrossAccountAuthorizationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteReadinessCheck(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteReadinessCheckAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRecoveryGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRecoveryGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResourceSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResourceSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getArchitectureRecommendations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getArchitectureRecommendationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCell(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCellAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCellReadinessSummary(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCellReadinessSummaryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getReadinessCheck(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getReadinessCheckAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getReadinessCheckResourceStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getReadinessCheckResourceStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getReadinessCheckStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getReadinessCheckStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRecoveryGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRecoveryGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRecoveryGroupReadinessSummary(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRecoveryGroupReadinessSummaryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResourceSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResourceSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCells(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCellsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCrossAccountAuthorizations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCrossAccountAuthorizationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listReadinessChecks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listReadinessChecksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRecoveryGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRecoveryGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResourceSets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResourceSetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRules(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRulesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateCell(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateCellAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateReadinessCheck(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateReadinessCheckAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateRecoveryGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateRecoveryGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateResourceSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateResourceSetAsync(array $args = [])
 */
class Route53RecoveryReadinessClient extends AwsClient {}
