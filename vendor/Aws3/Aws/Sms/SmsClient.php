<?php
namespace ClikIT\Infinite_Uploads\Aws\Sms;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Server Migration Service** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createApp(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAppAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createReplicationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createReplicationJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteApp(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAppAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAppLaunchConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAppLaunchConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAppReplicationConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAppReplicationConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAppValidationConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAppValidationConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteReplicationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteReplicationJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteServerCatalog(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteServerCatalogAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateConnector(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateConnectorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result generateChangeSet(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise generateChangeSetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result generateTemplate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise generateTemplateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getApp(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAppAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAppLaunchConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAppLaunchConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAppReplicationConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAppReplicationConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAppValidationConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAppValidationConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAppValidationOutput(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAppValidationOutputAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getConnectors(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getConnectorsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getReplicationJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getReplicationJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getReplicationRuns(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getReplicationRunsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importAppCatalog(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importAppCatalogAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importServerCatalog(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importServerCatalogAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result launchApp(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise launchAppAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listApps(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAppsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result notifyAppValidationOutput(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise notifyAppValidationOutputAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAppLaunchConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAppLaunchConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAppReplicationConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAppReplicationConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAppValidationConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAppValidationConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startAppReplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startAppReplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startOnDemandAppReplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startOnDemandAppReplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startOnDemandReplicationRun(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startOnDemandReplicationRunAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopAppReplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopAppReplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result terminateApp(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise terminateAppAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateApp(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAppAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateReplicationJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateReplicationJobAsync(array $args = [])
 */
class SmsClient extends AwsClient {}
