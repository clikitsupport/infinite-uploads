<?php
namespace ClikIT\Infinite_Uploads\Aws\ElasticLoadBalancingV2;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Elastic Load Balancing** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result addListenerCertificates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addListenerCertificatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addTrustStoreRevocations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addTrustStoreRevocationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createListener(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createListenerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLoadBalancer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLoadBalancerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTargetGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTargetGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTrustStore(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTrustStoreAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteListener(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteListenerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLoadBalancer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLoadBalancerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSharedTrustStoreAssociation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSharedTrustStoreAssociationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTargetGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTargetGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTrustStore(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTrustStoreAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deregisterTargets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deregisterTargetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAccountLimits(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAccountLimitsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeCapacityReservation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeCapacityReservationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeListenerAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeListenerAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeListenerCertificates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeListenerCertificatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeListeners(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeListenersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLoadBalancerAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLoadBalancerAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLoadBalancers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLoadBalancersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeRules(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeRulesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSSLPolicies(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSSLPoliciesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTargetGroupAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTargetGroupAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTargetGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTargetGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTargetHealth(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTargetHealthAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTrustStoreAssociations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTrustStoreAssociationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTrustStoreRevocations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTrustStoreRevocationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTrustStores(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTrustStoresAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTrustStoreCaCertificatesBundle(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTrustStoreCaCertificatesBundleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTrustStoreRevocationContent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTrustStoreRevocationContentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyCapacityReservation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyCapacityReservationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyIpPools(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyIpPoolsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyListener(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyListenerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyListenerAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyListenerAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyLoadBalancerAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyLoadBalancerAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyTargetGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyTargetGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyTargetGroupAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyTargetGroupAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyTrustStore(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyTrustStoreAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerTargets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerTargetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeListenerCertificates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeListenerCertificatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeTrustStoreRevocations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeTrustStoreRevocationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setIpAddressType(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setIpAddressTypeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setRulePriorities(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setRulePrioritiesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setSecurityGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setSecurityGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result setSubnets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise setSubnetsAsync(array $args = [])
 */
class ElasticLoadBalancingV2Client extends AwsClient {}
