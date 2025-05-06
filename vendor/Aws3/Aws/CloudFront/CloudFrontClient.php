<?php
namespace ClikIT\Infinite_Uploads\Aws\CloudFront;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CloudFront** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCloudFrontOriginAccessIdentity(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCloudFrontOriginAccessIdentityAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDistribution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDistributionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createInvalidation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createInvalidationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createStreamingDistribution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createStreamingDistributionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCloudFrontOriginAccessIdentity(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteCloudFrontOriginAccessIdentityAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDistribution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDistributionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteStreamingDistribution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteStreamingDistributionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCloudFrontOriginAccessIdentity(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCloudFrontOriginAccessIdentityAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCloudFrontOriginAccessIdentityConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCloudFrontOriginAccessIdentityConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDistribution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDistributionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDistributionConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDistributionConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getInvalidation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getInvalidationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStreamingDistribution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStreamingDistributionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStreamingDistributionConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStreamingDistributionConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCloudFrontOriginAccessIdentities(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCloudFrontOriginAccessIdentitiesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionsByWebACLId(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionsByWebACLIdAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInvalidations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInvalidationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStreamingDistributions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStreamingDistributionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateCloudFrontOriginAccessIdentity(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateCloudFrontOriginAccessIdentityAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateDistribution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateDistributionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateStreamingDistribution(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateStreamingDistributionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDistributionWithTags(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDistributionWithTagsAsync(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createStreamingDistributionWithTags(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createStreamingDistributionWithTagsAsync(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = []) (supported in versions 2016-08-01, 2016-08-20, 2016-09-07, 2016-09-29, 2016-11-25, 2017-03-25, 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteServiceLinkedRole(array $args = []) (supported in versions 2017-03-25)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteServiceLinkedRoleAsync(array $args = []) (supported in versions 2017-03-25)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createFieldLevelEncryptionConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createFieldLevelEncryptionConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createFieldLevelEncryptionProfile(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createFieldLevelEncryptionProfileAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createPublicKey(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createPublicKeyAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFieldLevelEncryptionConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFieldLevelEncryptionConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFieldLevelEncryptionProfile(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFieldLevelEncryptionProfileAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePublicKey(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePublicKeyAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFieldLevelEncryption(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFieldLevelEncryptionAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFieldLevelEncryptionConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFieldLevelEncryptionConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFieldLevelEncryptionProfile(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFieldLevelEncryptionProfileAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFieldLevelEncryptionProfileConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFieldLevelEncryptionProfileConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPublicKey(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPublicKeyAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPublicKeyConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPublicKeyConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFieldLevelEncryptionConfigs(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFieldLevelEncryptionConfigsAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFieldLevelEncryptionProfiles(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFieldLevelEncryptionProfilesAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPublicKeys(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPublicKeysAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFieldLevelEncryptionConfig(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFieldLevelEncryptionConfigAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFieldLevelEncryptionProfile(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFieldLevelEncryptionProfileAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updatePublicKey(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updatePublicKeyAsync(array $args = []) (supported in versions 2017-10-30, 2018-06-18, 2018-11-05, 2019-03-26, 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateAlias(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateAliasAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateDistributionTenantWebACL(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateDistributionTenantWebACLAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateDistributionWebACL(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateDistributionWebACLAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result copyDistribution(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise copyDistributionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAnycastIpList(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAnycastIpListAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCachePolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCachePolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createConnectionGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createConnectionGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createContinuousDeploymentPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createContinuousDeploymentPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDistributionTenant(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDistributionTenantAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createInvalidationForDistributionTenant(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createInvalidationForDistributionTenantAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createKeyGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createKeyGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createKeyValueStore(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createKeyValueStoreAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createMonitoringSubscription(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createMonitoringSubscriptionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createOriginAccessControl(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createOriginAccessControlAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createOriginRequestPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createOriginRequestPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createRealtimeLogConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createRealtimeLogConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createResponseHeadersPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createResponseHeadersPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result createVpcOrigin(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createVpcOriginAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAnycastIpList(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAnycastIpListAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCachePolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteCachePolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteConnectionGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteConnectionGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteContinuousDeploymentPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteContinuousDeploymentPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDistributionTenant(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDistributionTenantAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteKeyGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteKeyGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteKeyValueStore(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteKeyValueStoreAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteMonitoringSubscription(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteMonitoringSubscriptionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteOriginAccessControl(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteOriginAccessControlAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteOriginRequestPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteOriginRequestPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRealtimeLogConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRealtimeLogConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResponseHeadersPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResponseHeadersPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteVpcOrigin(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteVpcOriginAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeKeyValueStore(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeKeyValueStoreAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateDistributionTenantWebACL(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateDistributionTenantWebACLAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateDistributionWebACL(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateDistributionWebACLAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAnycastIpList(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAnycastIpListAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCachePolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCachePolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCachePolicyConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCachePolicyConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getConnectionGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getConnectionGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getConnectionGroupByRoutingEndpoint(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getConnectionGroupByRoutingEndpointAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getContinuousDeploymentPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getContinuousDeploymentPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getContinuousDeploymentPolicyConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getContinuousDeploymentPolicyConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDistributionTenant(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDistributionTenantAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDistributionTenantByDomain(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDistributionTenantByDomainAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getInvalidationForDistributionTenant(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getInvalidationForDistributionTenantAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getKeyGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getKeyGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getKeyGroupConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getKeyGroupConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getManagedCertificateDetails(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getManagedCertificateDetailsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMonitoringSubscription(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMonitoringSubscriptionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getOriginAccessControl(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getOriginAccessControlAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getOriginAccessControlConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getOriginAccessControlConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getOriginRequestPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getOriginRequestPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getOriginRequestPolicyConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getOriginRequestPolicyConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRealtimeLogConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRealtimeLogConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResponseHeadersPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResponseHeadersPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResponseHeadersPolicyConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResponseHeadersPolicyConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result getVpcOrigin(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getVpcOriginAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAnycastIpLists(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAnycastIpListsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCachePolicies(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCachePoliciesAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listConflictingAliases(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listConflictingAliasesAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listConnectionGroups(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listConnectionGroupsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listContinuousDeploymentPolicies(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listContinuousDeploymentPoliciesAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionTenants(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionTenantsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionTenantsByCustomization(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionTenantsByCustomizationAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionsByAnycastIpListId(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionsByAnycastIpListIdAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionsByCachePolicyId(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionsByCachePolicyIdAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionsByConnectionMode(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionsByConnectionModeAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionsByKeyGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionsByKeyGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionsByOriginRequestPolicyId(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionsByOriginRequestPolicyIdAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionsByRealtimeLogConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionsByRealtimeLogConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionsByResponseHeadersPolicyId(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionsByResponseHeadersPolicyIdAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDistributionsByVpcOriginId(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDistributionsByVpcOriginIdAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDomainConflicts(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDomainConflictsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFunctions(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFunctionsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInvalidationsForDistributionTenant(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInvalidationsForDistributionTenantAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listKeyGroups(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listKeyGroupsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listKeyValueStores(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listKeyValueStoresAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOriginAccessControls(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOriginAccessControlsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOriginRequestPolicies(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOriginRequestPoliciesAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRealtimeLogConfigs(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRealtimeLogConfigsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResponseHeadersPolicies(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResponseHeadersPoliciesAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result listVpcOrigins(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listVpcOriginsAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result publishFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise publishFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result testFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise testFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateCachePolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateCachePolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateConnectionGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateConnectionGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateContinuousDeploymentPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateContinuousDeploymentPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateDistributionTenant(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateDistributionTenantAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateDistributionWithStagingConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateDistributionWithStagingConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateDomainAssociation(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateDomainAssociationAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFunction(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFunctionAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateKeyGroup(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateKeyGroupAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateKeyValueStore(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateKeyValueStoreAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateOriginAccessControl(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateOriginAccessControlAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateOriginRequestPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateOriginRequestPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateRealtimeLogConfig(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateRealtimeLogConfigAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateResponseHeadersPolicy(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateResponseHeadersPolicyAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateVpcOrigin(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateVpcOriginAsync(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\Aws\Result verifyDnsConfiguration(array $args = []) (supported in versions 2020-05-31)
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise verifyDnsConfigurationAsync(array $args = []) (supported in versions 2020-05-31)
 */
class CloudFrontClient extends AwsClient
{
    /**
     * Create a signed Amazon CloudFront URL.
     *
     * This method accepts an array of configuration options:
     *
     * - url: (string)  URL of the resource being signed (can include query
     *   string and wildcards). For example: rtmp://s5c39gqb8ow64r.cloudfront.net/videos/mp3_name.mp3
     *   http://d111111abcdef8.cloudfront.net/images/horizon.jpg?size=large&license=yes
     * - policy: (string) JSON policy. Use this option when creating a signed
     *   URL for a custom policy.
     * - expires: (int) UTC Unix timestamp used when signing with a canned
     *   policy. Not required when passing a custom 'policy' option.
     * - key_pair_id: (string) The ID of the key pair used to sign CloudFront
     *   URLs for private distributions.
     * - private_key: (string) The filepath to the private key used to sign
     *   CloudFront URLs for private distributions.
     *
     * @param array $options Array of configuration options used when signing
     *
     * @return string Signed URL with authentication parameters
     * @throws \InvalidArgumentException if url, key_pair_id, or private_key
     *     were not specified.
     * @link http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/WorkingWithStreamingDistributions.html
     */
    public function getSignedUrl(array $options)
    {
        foreach (['url', 'key_pair_id', 'private_key'] as $required) {
            if (!isset($options[$required])) {
                throw new \InvalidArgumentException("$required is required");
            }
        }

        $urlSigner = new UrlSigner(
            $options['key_pair_id'],
            $options['private_key']
        );

        return $urlSigner->getSignedUrl(
            $options['url'],
            isset($options['expires']) ? $options['expires'] : null,
            isset($options['policy']) ? $options['policy'] : null
        );
    }

    /**
     * Create a signed Amazon CloudFront cookie.
     *
     * This method accepts an array of configuration options:
     *
     * - url: (string)  URL of the resource being signed (can include query
     *   string and wildcards). For example: http://d111111abcdef8.cloudfront.net/images/horizon.jpg?size=large&license=yes
     * - policy: (string) JSON policy. Use this option when creating a signed
     *   URL for a custom policy.
     * - expires: (int) UTC Unix timestamp used when signing with a canned
     *   policy. Not required when passing a custom 'policy' option.
     * - key_pair_id: (string) The ID of the key pair used to sign CloudFront
     *   URLs for private distributions.
     * - private_key: (string) The filepath ot the private key used to sign
     *   CloudFront URLs for private distributions.
     *
     * @param array $options Array of configuration options used when signing
     *
     * @return array Key => value pairs of signed cookies to set
     * @throws \InvalidArgumentException if url, key_pair_id, or private_key
     *     were not specified.
     * @link http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/WorkingWithStreamingDistributions.html
     */
    public function getSignedCookie(array $options)
    {
        foreach (['key_pair_id', 'private_key'] as $required) {
            if (!isset($options[$required])) {
                throw new \InvalidArgumentException("$required is required");
            }
        }

        $cookieSigner = new CookieSigner(
            $options['key_pair_id'],
            $options['private_key']
        );

        return $cookieSigner->getSignedCookie(
            isset($options['url']) ? $options['url'] : null,
            isset($options['expires']) ? $options['expires'] : null,
            isset($options['policy']) ? $options['policy'] : null
        );
    }
}
