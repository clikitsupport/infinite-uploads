<?php
namespace ClikIT\Infinite_Uploads\Aws\Redshift;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Redshift** service.
 *
 * @method \ClikIT\Infinite_Uploads\Aws\Result acceptReservedNodeExchange(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise acceptReservedNodeExchangeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addPartner(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addPartnerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateDataShareConsumer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateDataShareConsumerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result authorizeClusterSecurityGroupIngress(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise authorizeClusterSecurityGroupIngressAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result authorizeDataShare(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise authorizeDataShareAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result authorizeEndpointAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise authorizeEndpointAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result authorizeSnapshotAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise authorizeSnapshotAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchDeleteClusterSnapshots(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchDeleteClusterSnapshotsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchModifyClusterSnapshots(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchModifyClusterSnapshotsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result cancelResize(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise cancelResizeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result copyClusterSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise copyClusterSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAuthenticationProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAuthenticationProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createClusterParameterGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createClusterParameterGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createClusterSecurityGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createClusterSecurityGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createClusterSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createClusterSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createClusterSubnetGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createClusterSubnetGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCustomDomainAssociation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCustomDomainAssociationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createEndpointAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createEndpointAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createEventSubscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createEventSubscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createHsmClientCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createHsmClientCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createHsmConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createHsmConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createIntegration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createIntegrationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createRedshiftIdcApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createRedshiftIdcApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createScheduledAction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createScheduledActionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSnapshotCopyGrant(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSnapshotCopyGrantAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSnapshotSchedule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSnapshotScheduleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createUsageLimit(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createUsageLimitAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deauthorizeDataShare(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deauthorizeDataShareAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAuthenticationProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAuthenticationProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteClusterParameterGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteClusterParameterGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteClusterSecurityGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteClusterSecurityGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteClusterSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteClusterSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteClusterSubnetGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteClusterSubnetGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteCustomDomainAssociation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteCustomDomainAssociationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEndpointAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEndpointAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEventSubscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEventSubscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteHsmClientCertificate(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteHsmClientCertificateAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteHsmConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteHsmConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteIntegration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteIntegrationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePartner(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePartnerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRedshiftIdcApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRedshiftIdcApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteScheduledAction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteScheduledActionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSnapshotCopyGrant(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSnapshotCopyGrantAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteSnapshotSchedule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteSnapshotScheduleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteUsageLimit(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteUsageLimitAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deregisterNamespace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deregisterNamespaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAccountAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAccountAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeAuthenticationProfiles(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeAuthenticationProfilesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusterDbRevisions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClusterDbRevisionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusterParameterGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClusterParameterGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusterParameters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClusterParametersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusterSecurityGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClusterSecurityGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusterSnapshots(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClusterSnapshotsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusterSubnetGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClusterSubnetGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusterTracks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClusterTracksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusterVersions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClusterVersionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeClusters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeClustersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeCustomDomainAssociations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeCustomDomainAssociationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDataShares(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDataSharesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDataSharesForConsumer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDataSharesForConsumerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDataSharesForProducer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDataSharesForProducerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDefaultClusterParameters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDefaultClusterParametersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEndpointAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEndpointAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEndpointAuthorization(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEndpointAuthorizationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEventCategories(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEventCategoriesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEventSubscriptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEventSubscriptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeHsmClientCertificates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeHsmClientCertificatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeHsmConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeHsmConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeInboundIntegrations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeInboundIntegrationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeIntegrations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeIntegrationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLoggingStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLoggingStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeNodeConfigurationOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeNodeConfigurationOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeOrderableClusterOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeOrderableClusterOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describePartners(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describePartnersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeRedshiftIdcApplications(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeRedshiftIdcApplicationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeReservedNodeExchangeStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeReservedNodeExchangeStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeReservedNodeOfferings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeReservedNodeOfferingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeReservedNodes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeReservedNodesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeResize(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeResizeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeScheduledActions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeScheduledActionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSnapshotCopyGrants(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSnapshotCopyGrantsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSnapshotSchedules(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSnapshotSchedulesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeStorage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeStorageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTableRestoreStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTableRestoreStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeTags(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeTagsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeUsageLimits(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeUsageLimitsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disableLogging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disableLoggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disableSnapshotCopy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disableSnapshotCopyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateDataShareConsumer(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateDataShareConsumerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result enableLogging(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise enableLoggingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result enableSnapshotCopy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise enableSnapshotCopyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result failoverPrimaryCompute(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise failoverPrimaryComputeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getClusterCredentials(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getClusterCredentialsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getClusterCredentialsWithIAM(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getClusterCredentialsWithIAMAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getReservedNodeExchangeConfigurationOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getReservedNodeExchangeConfigurationOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getReservedNodeExchangeOfferings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getReservedNodeExchangeOfferingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRecommendations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRecommendationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyAquaConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyAquaConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyAuthenticationProfile(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyAuthenticationProfileAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyClusterDbRevision(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyClusterDbRevisionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyClusterIamRoles(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyClusterIamRolesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyClusterMaintenance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyClusterMaintenanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyClusterParameterGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyClusterParameterGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyClusterSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyClusterSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyClusterSnapshotSchedule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyClusterSnapshotScheduleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyClusterSubnetGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyClusterSubnetGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyCustomDomainAssociation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyCustomDomainAssociationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyEndpointAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyEndpointAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyEventSubscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyEventSubscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyIntegration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyIntegrationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyRedshiftIdcApplication(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyRedshiftIdcApplicationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyScheduledAction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyScheduledActionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifySnapshotCopyRetentionPeriod(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifySnapshotCopyRetentionPeriodAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifySnapshotSchedule(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifySnapshotScheduleAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyUsageLimit(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyUsageLimitAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result pauseCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise pauseClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result purchaseReservedNodeOffering(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise purchaseReservedNodeOfferingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result rebootCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise rebootClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerNamespace(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerNamespaceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result rejectDataShare(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise rejectDataShareAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result resetClusterParameterGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise resetClusterParameterGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result resizeCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise resizeClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result restoreFromClusterSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise restoreFromClusterSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result restoreTableFromClusterSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise restoreTableFromClusterSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result resumeCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise resumeClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result revokeClusterSecurityGroupIngress(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise revokeClusterSecurityGroupIngressAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result revokeEndpointAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise revokeEndpointAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result revokeSnapshotAccess(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise revokeSnapshotAccessAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result rotateEncryptionKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise rotateEncryptionKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updatePartnerStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updatePartnerStatusAsync(array $args = [])
 */
class RedshiftClient extends AwsClient {}
