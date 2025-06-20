<?php
namespace ClikIT\Infinite_Uploads\Aws\CloudTrail;

use ClikIT\Infinite_Uploads\Aws\S3\S3Client;

/**
 * This class provides an easy way to read log files generated by AWS
 * CloudTrail.
 *
 * CloudTrail log files contain data about your AWS API calls and are stored in
 * Amazon S3. The log files are gzipped and contain structured data in JSON
 * format. This class will automatically ungzip and decode the data, and return
 * the data as an array of log records
 */
class LogFileReader
{
    /** @var S3Client S3 client used to perform GetObject operations */
    private $s3Client;

    /**
     * @param S3Client $s3Client S3 client used to retrieve objects
     */
    public function __construct(S3Client $s3Client)
    {
        $this->s3Client = $s3Client;
    }

    /**
     * Downloads, unzips, and reads a CloudTrail log file from Amazon S3
     *
     * @param string $s3BucketName The bucket name of the log file in Amazon S3
     * @param string $logFileKey   The key of the log file in Amazon S3
     *
     * @return array
     */
    public function read($s3BucketName, $logFileKey)
    {
        // Create a command for getting the log file object
        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => (string) $s3BucketName,
            'Key' => (string) $logFileKey,
            'ResponseContentEncoding' => 'x-gzip'
        ]);

        // Make sure gzip encoding header is sent and accepted in order to
        // inflate the response data.
        $command['@http']['headers']['Accept-Encoding'] = 'gzip';

        // Get the JSON response data and extract the log records
        $result = $this->s3Client->execute($command);
        $logData = json_decode($result['Body'], true);

        return isset($logData['Records']) ? $logData['Records'] : [];
    }
}
