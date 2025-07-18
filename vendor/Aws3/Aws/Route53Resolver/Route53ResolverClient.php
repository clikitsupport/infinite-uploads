<?php
namespace ClikIT\Infinite_Uploads\Aws\Route53Resolver;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Route 53 Resolver** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateFirewallRuleGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateFirewallRuleGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateResolverEndpointIpAddress(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateResolverEndpointIpAddressAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateResolverQueryLogConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateResolverQueryLogConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateResolverRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateResolverRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createFirewallDomainList(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createFirewallDomainListAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createFirewallRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createFirewallRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createFirewallRuleGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createFirewallRuleGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createOutpostResolver(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createOutpostResolverAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createResolverEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createResolverEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createResolverQueryLogConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createResolverQueryLogConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createResolverRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createResolverRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFirewallDomainList(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFirewallDomainListAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFirewallRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFirewallRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteFirewallRuleGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFirewallRuleGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteOutpostResolver(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteOutpostResolverAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResolverEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResolverEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResolverQueryLogConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResolverQueryLogConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResolverRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResolverRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateFirewallRuleGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateFirewallRuleGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateResolverEndpointIpAddress(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateResolverEndpointIpAddressAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateResolverQueryLogConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateResolverQueryLogConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateResolverRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateResolverRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFirewallConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFirewallConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFirewallDomainList(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFirewallDomainListAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFirewallRuleGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFirewallRuleGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFirewallRuleGroupAssociation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFirewallRuleGroupAssociationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getFirewallRuleGroupPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFirewallRuleGroupPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getOutpostResolver(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getOutpostResolverAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResolverConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResolverConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResolverDnssecConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResolverDnssecConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResolverEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResolverEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResolverQueryLogConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResolverQueryLogConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResolverQueryLogConfigAssociation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResolverQueryLogConfigAssociationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResolverQueryLogConfigPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResolverQueryLogConfigPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResolverRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResolverRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResolverRuleAssociation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResolverRuleAssociationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResolverRulePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResolverRulePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importFirewallDomains(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importFirewallDomainsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFirewallConfigs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFirewallConfigsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFirewallDomainLists(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFirewallDomainListsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFirewallDomains(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFirewallDomainsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFirewallRuleGroupAssociations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFirewallRuleGroupAssociationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFirewallRuleGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFirewallRuleGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFirewallRules(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFirewallRulesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listOutpostResolvers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listOutpostResolversAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResolverConfigs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResolverConfigsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResolverDnssecConfigs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResolverDnssecConfigsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResolverEndpointIpAddresses(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResolverEndpointIpAddressesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResolverEndpoints(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResolverEndpointsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResolverQueryLogConfigAssociations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResolverQueryLogConfigAssociationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResolverQueryLogConfigs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResolverQueryLogConfigsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResolverRuleAssociations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResolverRuleAssociationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listResolverRules(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listResolverRulesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putFirewallRuleGroupPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putFirewallRuleGroupPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putResolverQueryLogConfigPolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putResolverQueryLogConfigPolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putResolverRulePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putResolverRulePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFirewallConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFirewallConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFirewallDomains(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFirewallDomainsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFirewallRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFirewallRuleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateFirewallRuleGroupAssociation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFirewallRuleGroupAssociationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateOutpostResolver(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateOutpostResolverAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateResolverConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateResolverConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateResolverDnssecConfig(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateResolverDnssecConfigAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateResolverEndpoint(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateResolverEndpointAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateResolverRule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateResolverRuleAsync(array $args = [])
 */
class Route53ResolverClient extends AwsClient {}
