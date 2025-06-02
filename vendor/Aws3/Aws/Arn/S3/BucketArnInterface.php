<?php
namespace ClikIT\Infinite_Uploads\Aws\Arn\S3;

use ClikIT\Infinite_Uploads\Aws\Arn\ArnInterface;

/**
 * @internal
 */
interface BucketArnInterface extends ArnInterface
{
    public function getBucketName();
}
