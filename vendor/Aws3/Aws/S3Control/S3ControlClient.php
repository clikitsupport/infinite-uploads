<?php
namespace ClikIT\Infinite_Uploads\Aws\S3Control;

use ClikIT\Infinite_Uploads\Aws\AwsClient;
use ClikIT\Infinite_Uploads\Aws\CacheInterface;
use ClikIT\Infinite_Uploads\Aws\HandlerList;
use ClikIT\Infinite_Uploads\Aws\S3\UseArnRegion\Configuration;
use ClikIT\Infinite_Uploads\Aws\S3\UseArnRegion\ConfigurationInterface;
use ClikIT\Infinite_Uploads\Aws\S3\UseArnRegion\ConfigurationProvider as UseArnRegionConfigurationProvider;
use ClikIT\Infinite_Uploads\GuzzleHttp\Promise\PromiseInterface;

/**
 * This client is used to interact with the **AWS S3 Control** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateAccessGrantsIdentityCenter(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateAccessGrantsIdentityCenterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAccessGrant(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAccessGrantAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAccessGrantsInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAccessGrantsInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAccessGrantsLocation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAccessGrantsLocationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAccessPoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAccessPointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAccessPointForObjectLambda(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAccessPointForObjectLambdaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createBucket(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createBucketAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createMultiRegionAccessPoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createMultiRegionAccessPointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createStorageLensGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createStorageLensGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccessGrant(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccessGrantAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccessGrantsInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccessGrantsInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccessGrantsInstanceResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccessGrantsInstanceResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccessGrantsLocation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccessGrantsLocationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccessPoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccessPointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccessPointForObjectLambda(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccessPointForObjectLambdaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccessPointPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccessPointPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccessPointPolicyForObjectLambda(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccessPointPolicyForObjectLambdaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAccessPointScope(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAccessPointScopeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteBucket(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteBucketAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteBucketLifecycleConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteBucketLifecycleConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteBucketPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteBucketPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteBucketReplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteBucketReplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteBucketTagging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteBucketTaggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteJobTagging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteJobTaggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteMultiRegionAccessPoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteMultiRegionAccessPointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePublicAccessBlock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePublicAccessBlockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteStorageLensConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteStorageLensConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteStorageLensConfigurationTagging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteStorageLensConfigurationTaggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteStorageLensGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteStorageLensGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeMultiRegionAccessPointOperation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeMultiRegionAccessPointOperationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result dissociateAccessGrantsIdentityCenter(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise dissociateAccessGrantsIdentityCenterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessGrant(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessGrantAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessGrantsInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessGrantsInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessGrantsInstanceForPrefix(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessGrantsInstanceForPrefixAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessGrantsInstanceResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessGrantsInstanceResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessGrantsLocation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessGrantsLocationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessPoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessPointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessPointConfigurationForObjectLambda(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessPointConfigurationForObjectLambdaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessPointForObjectLambda(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessPointForObjectLambdaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessPointPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessPointPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessPointPolicyForObjectLambda(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessPointPolicyForObjectLambdaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessPointPolicyStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessPointPolicyStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessPointPolicyStatusForObjectLambda(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessPointPolicyStatusForObjectLambdaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccessPointScope(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccessPointScopeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getBucket(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getBucketAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getBucketLifecycleConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getBucketLifecycleConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getBucketPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getBucketPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getBucketReplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getBucketReplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getBucketTagging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getBucketTaggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getBucketVersioning(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getBucketVersioningAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDataAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDataAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getJobTagging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getJobTaggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMultiRegionAccessPoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMultiRegionAccessPointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMultiRegionAccessPointPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMultiRegionAccessPointPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMultiRegionAccessPointPolicyStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMultiRegionAccessPointPolicyStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMultiRegionAccessPointRoutes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMultiRegionAccessPointRoutesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPublicAccessBlock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPublicAccessBlockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStorageLensConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStorageLensConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStorageLensConfigurationTagging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStorageLensConfigurationTaggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStorageLensGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStorageLensGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccessGrants(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccessGrantsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccessGrantsInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccessGrantsInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccessGrantsLocations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccessGrantsLocationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccessPoints(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccessPointsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccessPointsForDirectoryBuckets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccessPointsForDirectoryBucketsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAccessPointsForObjectLambda(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAccessPointsForObjectLambdaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCallerAccessGrants(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCallerAccessGrantsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listMultiRegionAccessPoints(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listMultiRegionAccessPointsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRegionalBuckets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRegionalBucketsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStorageLensConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStorageLensConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStorageLensGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStorageLensGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccessGrantsInstanceResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccessGrantsInstanceResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccessPointConfigurationForObjectLambda(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccessPointConfigurationForObjectLambdaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccessPointPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccessPointPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccessPointPolicyForObjectLambda(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccessPointPolicyForObjectLambdaAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putAccessPointScope(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putAccessPointScopeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putBucketLifecycleConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putBucketLifecycleConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putBucketPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putBucketPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putBucketReplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putBucketReplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putBucketTagging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putBucketTaggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putBucketVersioning(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putBucketVersioningAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putJobTagging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putJobTaggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putMultiRegionAccessPointPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putMultiRegionAccessPointPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putPublicAccessBlock(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putPublicAccessBlockAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putStorageLensConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putStorageLensConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putStorageLensConfigurationTagging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putStorageLensConfigurationTaggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result submitMultiRegionAccessPointRoutes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise submitMultiRegionAccessPointRoutesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAccessGrantsLocation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAccessGrantsLocationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateJobPriority(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateJobPriorityAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateJobStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateJobStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateStorageLensGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateStorageLensGroupAsync(array $args = [])
 */
class S3ControlClient extends AwsClient 
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        return $args + [
            'use_dual_stack_endpoint' => [
                'type' => 'config',
                'valid' => ['bool'],
                'doc' => 'Set to true to send requests to an S3 Control Dual Stack'
                    . ' endpoint by default, which enables IPv6 Protocol.'
                    . ' Can be enabled or disabled on individual operations by setting'
                    . ' \'@use_dual_stack_endpoint\' to true or false.',
                'default' => false,
            ],
            'use_arn_region' => [
                'type'    => 'config',
                'valid'   => [
                    'bool',
                    Configuration::class,
                    CacheInterface::class,
                    'callable'
                ],
                'doc'     => 'Set to true to allow passed in ARNs to override'
                    . ' client region. Accepts...',
                'fn' => [__CLASS__, '_apply_use_arn_region'],
                'default' => [UseArnRegionConfigurationProvider::class, 'defaultProvider'],
            ],
        ];
    }

    public static function _apply_use_arn_region($value, array &$args, HandlerList $list)
    {
        if ($value instanceof CacheInterface) {
            $value = UseArnRegionConfigurationProvider::defaultProvider($args);
        }
        if (is_callable($value)) {
            $value = $value();
        }
        if ($value instanceof PromiseInterface) {
            $value = $value->wait();
        }
        if ($value instanceof ConfigurationInterface) {
            $args['use_arn_region'] = $value;
        } else {
            // The Configuration class itself will validate other inputs
            $args['use_arn_region'] = new Configuration($value);
        }
    }

    /**
     * {@inheritdoc}
     *
     * In addition to the options available to
     * {@see ClikIT\Infinite_Uploads\Aws\AwsClient::__construct}, S3ControlClient accepts the following
     * option:
     *
     * - use_dual_stack_endpoint: (bool) Set to true to send requests to an S3
     *   Control Dual Stack endpoint by default, which enables IPv6 Protocol.
     *   Can be enabled or disabled on individual operations by setting
     *   '@use_dual_stack_endpoint\' to true or false. Note:
     *   you cannot use it together with an accelerate endpoint.
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        parent::__construct($args);

        if ($this->isUseEndpointV2()) {
            $this->processEndpointV2Model();
        } else {
            $stack = $this->getHandlerList();
            $stack->appendBuild(
                EndpointArnMiddleware::wrap(
                    $this->getApi(),
                    $this->getRegion(),
                    [
                        'use_arn_region' => $this->getConfig('use_arn_region'),
                        'dual_stack' =>
                            $this->getConfig('use_dual_stack_endpoint')->isUseDualStackEndpoint(),
                        'endpoint' => isset($args['endpoint'])
                            ? $args['endpoint']
                            : null,
                        'use_fips_endpoint' => $this->getConfig('use_fips_endpoint'),
                    ],
                    $this->isUseEndpointV2()
                ),
                's3control.endpoint_arn_middleware'
            );
        }
    }

    /**
     * Modifies API definition to remove `AccountId`
     * host prefix.  This is now handled by the endpoint ruleset.
     *
     * @return void
     *
     * @internal
     */
    private function processEndpointV2Model()
    {
        $definition = $this->getApi()->getDefinition();
        $this->removeHostPrefix($definition);
        $this->removeRequiredMember($definition);
        $this->getApi()->setDefinition($definition);
    }

    private function removeHostPrefix(&$definition)
    {
        foreach($definition['operations'] as &$operation) {
            if (isset($operation['endpoint']['hostPrefix'])
                && $operation['endpoint']['hostPrefix'] === '{AccountId}.'
            ) {
                $operation['endpoint']['hostPrefix'] = str_replace(
                    '{AccountId}.',
                    '',
                    $operation['endpoint']['hostPrefix']
                );
            }
        }
    }

    private function removeRequiredMember(&$definition)
    {
        foreach($definition['shapes'] as &$shape) {
            if (isset($shape['required'])
            ) {
                $found = array_search('AccountId', $shape['required']);

                if ($found !== false) {
                    unset($shape['required'][$found]);
                }
            }
        }
    }
}
