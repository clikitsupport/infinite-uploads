<?php
namespace ClikIT\Infinite_Uploads\Aws\SavingsPlans;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Savings Plans** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createSavingsPlan(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createSavingsPlanAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteQueuedSavingsPlan(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteQueuedSavingsPlanAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSavingsPlanRates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSavingsPlanRatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSavingsPlans(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSavingsPlansAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSavingsPlansOfferingRates(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSavingsPlansOfferingRatesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeSavingsPlansOfferings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeSavingsPlansOfferingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result returnSavingsPlan(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise returnSavingsPlanAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class SavingsPlansClient extends AwsClient {}
