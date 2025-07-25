<?php
namespace ClikIT\Infinite_Uploads\Aws\AmplifyUIBuilder;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Amplify UI Builder** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result createComponent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createComponentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createForm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createFormAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createTheme(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createThemeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteComponent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteComponentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteForm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteFormAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteTheme(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteThemeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result exchangeCodeForToken(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise exchangeCodeForTokenAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result exportComponents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise exportComponentsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result exportForms(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise exportFormsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result exportThemes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise exportThemesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getCodegenJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getCodegenJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getComponent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getComponentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getForm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getFormAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getMetadata(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getMetadataAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTheme(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getThemeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listCodegenJobs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listCodegenJobsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listComponents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listComponentsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listForms(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFormsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listThemes(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listThemesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result putMetadataFlag(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise putMetadataFlagAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result refreshToken(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise refreshTokenAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startCodegenJob(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startCodegenJobAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateComponent(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateComponentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateForm(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateFormAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateTheme(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateThemeAsync(array $args = [])
 */
class AmplifyUIBuilderClient extends AwsClient {}
