<?php

namespace ClikIT\Infinite_Uploads\Aws\S3\S3Transfer\Models;

use ClikIT\Infinite_Uploads\Aws\Result;
final class UploadResult extends Result
{
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
