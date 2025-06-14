<?php
namespace ClikIT\Infinite_Uploads\Aws\IVSRealTime;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Interactive Video Service RealTime** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createEncoderConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createEncoderConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createIngestConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createIngestConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createParticipantToken(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createParticipantTokenAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createStage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createStageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createStorageConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createStorageConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteEncoderConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteEncoderConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteIngestConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteIngestConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deletePublicKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deletePublicKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteStage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteStageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteStorageConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteStorageConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disconnectParticipant(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disconnectParticipantAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getComposition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCompositionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getEncoderConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getEncoderConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getIngestConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getIngestConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getParticipant(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getParticipantAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPublicKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPublicKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStageAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStageSession(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStageSessionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getStorageConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getStorageConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result importPublicKey(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise importPublicKeyAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCompositions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCompositionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listEncoderConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listEncoderConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listIngestConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listIngestConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listParticipantEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listParticipantEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listParticipants(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listParticipantsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listPublicKeys(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listPublicKeysAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStageSessions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStageSessionsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStages(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStagesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listStorageConfigurations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listStorageConfigurationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startComposition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startCompositionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result stopComposition(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise stopCompositionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateIngestConfiguration(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateIngestConfigurationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateStage(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateStageAsync(array $args = [])
 */
class IVSRealTimeClient extends AwsClient {}
