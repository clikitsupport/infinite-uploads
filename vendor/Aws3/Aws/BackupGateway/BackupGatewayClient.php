<?php
namespace ClikIT\Infinite_Uploads\Aws\BackupGateway;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Backup Gateway** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateGatewayToServer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateGatewayToServerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createGateway(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createGatewayAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteGateway(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteGatewayAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteHypervisor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteHypervisorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateGatewayFromServer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateGatewayFromServerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getBandwidthRateLimitSchedule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getBandwidthRateLimitScheduleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getGateway(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getGatewayAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getHypervisor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getHypervisorAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getHypervisorPropertyMappings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getHypervisorPropertyMappingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getVirtualMachine(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getVirtualMachineAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importHypervisorConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importHypervisorConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listGateways(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listGatewaysAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listHypervisors(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listHypervisorsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listVirtualMachines(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listVirtualMachinesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putBandwidthRateLimitSchedule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putBandwidthRateLimitScheduleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putHypervisorPropertyMappings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putHypervisorPropertyMappingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putMaintenanceStartTime(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putMaintenanceStartTimeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startVirtualMachinesMetadataSync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startVirtualMachinesMetadataSyncAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result testHypervisorConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise testHypervisorConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateGatewayInformation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateGatewayInformationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateGatewaySoftwareNow(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateGatewaySoftwareNowAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateHypervisor(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateHypervisorAsync(array $args = [])
 */
class BackupGatewayClient extends AwsClient {}
