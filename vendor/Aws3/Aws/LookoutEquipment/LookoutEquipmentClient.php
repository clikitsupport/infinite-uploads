<?php
namespace ClikIT\Infinite_Uploads\Aws\LookoutEquipment;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Lookout for Equipment** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createDataset(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createDatasetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createInferenceScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createInferenceSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLabel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLabelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createLabelGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createLabelGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createRetrainingScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createRetrainingSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteDataset(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteDatasetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteInferenceScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteInferenceSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLabel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLabelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteLabelGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteLabelGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteRetrainingScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteRetrainingSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDataIngestionJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDataIngestionJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeDataset(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeDatasetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeInferenceScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeInferenceSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLabel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLabelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeLabelGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeLabelGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeModelVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeModelVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeRetrainingScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeRetrainingSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importDataset(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importDatasetAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importModelVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importModelVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDataIngestionJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDataIngestionJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listDatasets(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listDatasetsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInferenceEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInferenceEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInferenceExecutions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInferenceExecutionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listInferenceSchedulers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listInferenceSchedulersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLabelGroups(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLabelGroupsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listLabels(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listLabelsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listModelVersions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listModelVersionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listModels(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listModelsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRetrainingSchedulers(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRetrainingSchedulersAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listSensorStatistics(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listSensorStatisticsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putResourcePolicy(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putResourcePolicyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startDataIngestionJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startDataIngestionJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startInferenceScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startInferenceSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startRetrainingScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startRetrainingSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopInferenceScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopInferenceSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopRetrainingScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopRetrainingSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateActiveModelVersion(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateActiveModelVersionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateInferenceScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateInferenceSchedulerAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateLabelGroup(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateLabelGroupAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateModel(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateModelAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateRetrainingScheduler(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateRetrainingSchedulerAsync(array $args = [])
 */
class LookoutEquipmentClient extends AwsClient {}
