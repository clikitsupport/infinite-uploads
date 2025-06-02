<?php
namespace ClikIT\Infinite_Uploads\Aws\ManagedBlockchainQuery;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Managed Blockchain Query** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchGetTokenBalance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchGetTokenBalanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAssetContract(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAssetContractAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTokenBalance(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTokenBalanceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getTransaction(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getTransactionAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAssetContracts(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAssetContractsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listFilteredTransactionEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listFilteredTransactionEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTokenBalances(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTokenBalancesAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTransactionEvents(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTransactionEventsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTransactions(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTransactionsAsync(array $args = [])
 */
class ManagedBlockchainQueryClient extends AwsClient {}
