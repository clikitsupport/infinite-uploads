<?php
namespace ClikIT\Infinite_Uploads\Aws\Ecs;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with **Amazon ECS**.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCapacityProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCapacityProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createService(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createServiceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTaskSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTaskSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccountSetting(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccountSettingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCapacityProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteCapacityProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteService(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteServiceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTaskDefinitions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTaskDefinitionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTaskSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTaskSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deregisterContainerInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deregisterContainerInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deregisterTaskDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deregisterTaskDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeCapacityProviders(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeCapacityProvidersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClustersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeContainerInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeContainerInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeServiceDeployments(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeServiceDeploymentsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeServiceRevisions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeServiceRevisionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeServices(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeServicesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTaskDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTaskDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTaskSets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTaskSetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTasks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTasksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result discoverPollEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise discoverPollEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result executeCommand(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise executeCommandAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTaskProtection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTaskProtectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccountSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccountSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listClusters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listClustersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listContainerInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listContainerInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServiceDeployments(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServiceDeploymentsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServices(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServicesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listServicesByNamespace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listServicesByNamespaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTaskDefinitionFamilies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTaskDefinitionFamiliesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTaskDefinitions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTaskDefinitionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTasks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTasksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccountSetting(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccountSettingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccountSettingDefault(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccountSettingDefaultAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putClusterCapacityProviders(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putClusterCapacityProvidersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerContainerInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerContainerInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerTaskDefinition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerTaskDefinitionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result runTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise runTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopServiceDeployment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopServiceDeploymentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopTask(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopTaskAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result submitAttachmentStateChanges(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise submitAttachmentStateChangesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result submitContainerStateChange(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise submitContainerStateChangeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result submitTaskStateChange(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise submitTaskStateChangeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateCapacityProvider(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateCapacityProviderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateClusterSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateClusterSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateContainerAgent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateContainerAgentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateContainerInstancesState(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateContainerInstancesStateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateService(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateServiceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateServicePrimaryTaskSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateServicePrimaryTaskSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateTaskProtection(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateTaskProtectionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateTaskSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateTaskSetAsync(array $args = [])
 */
class EcsClient extends AwsClient {}
