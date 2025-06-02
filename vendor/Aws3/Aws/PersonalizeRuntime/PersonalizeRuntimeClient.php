<?php
namespace ClikIT\Infinite_Uploads\Aws\PersonalizeRuntime;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Personalize Runtime** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result getActionRecommendations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getActionRecommendationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getPersonalizedRanking(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getPersonalizedRankingAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getRecommendations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getRecommendationsAsync(array $args = [])
 */
class PersonalizeRuntimeClient extends AwsClient {}
