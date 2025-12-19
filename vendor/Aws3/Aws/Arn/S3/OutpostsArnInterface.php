<?php

namespace ClikIT\Infinite_Uploads\Aws\Arn\S3;

use ClikIT\Infinite_Uploads\Aws\Arn\ArnInterface;
/**
 * @internal
 */
interface OutpostsArnInterface extends ArnInterface
{
    public function getOutpostId();
}
