<?php
namespace ClikIT\Infinite_Uploads\Aws\CodeGuruReviewer;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CodeGuru Reviewer** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateRepository(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateRepositoryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createCodeReview(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createCodeReviewAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeCodeReview(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeCodeReviewAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeRecommendationFeedback(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeRecommendationFeedbackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result describeRepositoryAssociation(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise describeRepositoryAssociationAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateRepository(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateRepositoryAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCodeReviews(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCodeReviewsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRecommendationFeedback(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRecommendationFeedbackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRecommendations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRecommendationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listRepositoryAssociations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listRepositoryAssociationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putRecommendationFeedback(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putRecommendationFeedbackAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class CodeGuruReviewerClient extends AwsClient {}
