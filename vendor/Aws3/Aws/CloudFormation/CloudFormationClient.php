<?php
namespace ClikIT\Infinite_Uploads\Aws\CloudFormation;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CloudFormation** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result activateOrganizationsAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise activateOrganizationsAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result activateType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise activateTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchDescribeTypeConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchDescribeTypeConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelUpdateStack(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelUpdateStackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result continueUpdateRollback(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise continueUpdateRollbackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createChangeSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createChangeSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createGeneratedTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createGeneratedTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createStack(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createStackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createStackInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createStackInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createStackRefactor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createStackRefactorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createStackSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createStackSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deactivateOrganizationsAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deactivateOrganizationsAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deactivateType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deactivateTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteChangeSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteChangeSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteGeneratedTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteGeneratedTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteStack(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteStackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteStackInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteStackInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteStackSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteStackSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deregisterType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deregisterTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAccountLimits(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAccountLimitsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeChangeSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeChangeSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeChangeSetHooks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeChangeSetHooksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeGeneratedTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeGeneratedTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeOrganizationsAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeOrganizationsAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describePublisher(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describePublisherAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeResourceScan(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeResourceScanAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStackDriftDetectionStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStackDriftDetectionStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStackEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStackEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStackInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStackInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStackRefactor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStackRefactorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStackResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStackResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStackResourceDrifts(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStackResourceDriftsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStackResources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStackResourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStackSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStackSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStackSetOperation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStackSetOperationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStacks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStacksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTypeRegistration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTypeRegistrationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result detectStackDrift(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise detectStackDriftAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result detectStackResourceDrift(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise detectStackResourceDriftAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result detectStackSetDrift(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise detectStackSetDriftAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result estimateTemplateCost(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise estimateTemplateCostAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result executeChangeSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise executeChangeSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result executeStackRefactor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise executeStackRefactorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getGeneratedTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getGeneratedTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStackPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStackPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTemplateSummary(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTemplateSummaryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importStacksToStackSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importStacksToStackSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listChangeSets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listChangeSetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listExports(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listExportsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listGeneratedTemplates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listGeneratedTemplatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listHookResults(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listHookResultsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listImports(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listImportsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResourceScanRelatedResources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResourceScanRelatedResourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResourceScanResources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResourceScanResourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResourceScans(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResourceScansAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStackInstanceResourceDrifts(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStackInstanceResourceDriftsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStackInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStackInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStackRefactorActions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStackRefactorActionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStackRefactors(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStackRefactorsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStackResources(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStackResourcesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStackSetAutoDeploymentTargets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStackSetAutoDeploymentTargetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStackSetOperationResults(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStackSetOperationResultsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStackSetOperations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStackSetOperationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStackSets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStackSetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStacks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStacksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTypeRegistrations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTypeRegistrationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTypeVersions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTypeVersionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTypes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTypesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result publishType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise publishTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result recordHandlerProgress(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise recordHandlerProgressAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerPublisher(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerPublisherAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result rollbackStack(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise rollbackStackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setStackPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setStackPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setTypeConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setTypeConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setTypeDefaultVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setTypeDefaultVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result signalResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise signalResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startResourceScan(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startResourceScanAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopStackSetOperation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopStackSetOperationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result testType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise testTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateGeneratedTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateGeneratedTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateStack(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateStackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateStackInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateStackInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateStackSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateStackSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateTerminationProtection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateTerminationProtectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result validateTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise validateTemplateAsync(array $args = [])
 */
class CloudFormationClient extends AwsClient {}
