<?php

namespace ClikIT\Infinite_Uploads\Aws\S3\S3Transfer\Utils;

use ClikIT\Infinite_Uploads\Aws\S3\S3Transfer\Progress\AbstractTransferListener;
abstract class AbstractDownloadHandler extends AbstractTransferListener
{
    /**
     * Returns the handler result.
     * - For FileDownloadHandler it may return the file destination.
     * - For StreamDownloadHandler it may return an instance of StreamInterface
     *   containing the content of the object.
     *
     * @return mixed
     */
    abstract public function getHandlerResult(): mixed;
}
