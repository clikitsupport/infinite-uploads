<?php
namespace ClikIT\Infinite_Uploads\Aws\Retry;

use ClikIT\Infinite_Uploads\Aws\Exception\AwsException;
use ClikIT\Infinite_Uploads\Aws\ResultInterface;

trait RetryHelperTrait
{
    private function addRetryHeader($request, $retries, $delayBy)
    {
        return $request->withHeader('aws-sdk-retry', "{$retries}/{$delayBy}");
    }


    private function updateStats($retries, $delay, array &$stats)
    {
        if (!isset($stats['total_retry_delay'])) {
            $stats['total_retry_delay'] = 0;
        }

        $stats['total_retry_delay'] += $delay;
        $stats['retries_attempted'] = $retries;
    }

    private function updateHttpStats($value, array &$stats)
    {
        if (empty($stats['http'])) {
            $stats['http'] = [];
        }

        if ($value instanceof AwsException) {
            $resultStats = $value->getTransferInfo();
            $stats['http'] []= $resultStats;
        } elseif ($value instanceof ResultInterface) {
            $resultStats = isset($value['@metadata']['transferStats']['http'][0])
                ? $value['@metadata']['transferStats']['http'][0]
                : [];
            $stats['http'] []= $resultStats;
        }
    }

    private function bindStatsToReturn($return, array $stats)
    {
        if ($return instanceof ResultInterface) {
            if (!isset($return['@metadata'])) {
                $return['@metadata'] = [];
            }

            $return['@metadata']['transferStats'] = $stats;
        } elseif ($return instanceof AwsException) {
            $return->setTransferInfo($stats);
        }

        return $return;
    }
}
