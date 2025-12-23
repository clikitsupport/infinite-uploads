<?php
namespace ClikIT\Infinite_Uploads\Aws\DocDB;

use ClikIT\Infinite_Uploads\Aws\AwsClient;
use ClikIT\Infinite_Uploads\Aws\PresignUrlMiddleware;

/**
 * This client is used to interact with the **Amazon DocumentDB with MongoDB compatibility** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result addSourceIdentifierToSubscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addSourceIdentifierToSubscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result addTagsToResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise addTagsToResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result applyPendingMaintenanceAction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise applyPendingMaintenanceActionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result copyDBClusterParameterGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise copyDBClusterParameterGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result copyDBClusterSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise copyDBClusterSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDBCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDBClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDBClusterParameterGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDBClusterParameterGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDBClusterSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDBClusterSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDBInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDBInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDBSubnetGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDBSubnetGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createEventSubscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createEventSubscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createGlobalCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createGlobalClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDBCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDBClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDBClusterParameterGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDBClusterParameterGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDBClusterSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDBClusterSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDBInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDBInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDBSubnetGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDBSubnetGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEventSubscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEventSubscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteGlobalCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteGlobalClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeCertificates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeCertificatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDBClusterParameterGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDBClusterParameterGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDBClusterParameters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDBClusterParametersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDBClusterSnapshotAttributes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDBClusterSnapshotAttributesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDBClusterSnapshots(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDBClusterSnapshotsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDBClusters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDBClustersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDBEngineVersions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDBEngineVersionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDBInstances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDBInstancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDBSubnetGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDBSubnetGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEngineDefaultClusterParameters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEngineDefaultClusterParametersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEventCategories(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEventCategoriesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEventSubscriptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEventSubscriptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeGlobalClusters(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeGlobalClustersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeOrderableDBInstanceOptions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeOrderableDBInstanceOptionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describePendingMaintenanceActions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describePendingMaintenanceActionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result failoverDBCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise failoverDBClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result failoverGlobalCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise failoverGlobalClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyDBCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyDBClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyDBClusterParameterGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyDBClusterParameterGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyDBClusterSnapshotAttribute(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyDBClusterSnapshotAttributeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyDBInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyDBInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyDBSubnetGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyDBSubnetGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyEventSubscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyEventSubscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result modifyGlobalCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise modifyGlobalClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result rebootDBInstance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise rebootDBInstanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeFromGlobalCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeFromGlobalClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeSourceIdentifierFromSubscription(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeSourceIdentifierFromSubscriptionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result removeTagsFromResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise removeTagsFromResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result resetDBClusterParameterGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise resetDBClusterParameterGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result restoreDBClusterFromSnapshot(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise restoreDBClusterFromSnapshotAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result restoreDBClusterToPointInTime(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise restoreDBClusterToPointInTimeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startDBCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startDBClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopDBCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopDBClusterAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result switchoverGlobalCluster(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise switchoverGlobalClusterAsync(array $args = [])
 */
class DocDBClient extends AwsClient {
    public function __construct(array $args)
    {
        $args['with_resolved'] = function (array $args) {
            $this->getHandlerList()->appendInit(
                PresignUrlMiddleware::wrap(
                    $this,
                    $args['endpoint_provider'],
                    [
                        'operations' => [
                            'CopyDBClusterSnapshot',
                            'CreateDBCluster',
                        ],
                        'service' => 'rds',
                        'presign_param' => 'PreSignedUrl',
                        'require_different_region' => true,
                        'extra_query_params' => [
                            'CopyDBClusterSnapshot' => ['DestinationRegion'],
                            'CreateDBCluster' => ['DestinationRegion'],
                        ]
                    ]
                ),
                'rds.presigner'
            );
        };
        parent::__construct($args);
    }
}
