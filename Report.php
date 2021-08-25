
  
<?php

require 'vendor/autoload.php';
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\Util\v202108\ReportDownloader;
use Google\AdsApi\AdManager\Util\v202108\StatementBuilder;
use Google\AdsApi\AdManager\v202108\ExportFormat;
use Google\AdsApi\AdManager\v202108\ReportJob;
use Google\AdsApi\AdManager\v202108\ReportQueryAdUnitView;
use Google\AdsApi\AdManager\v202108\ServiceFactory;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\v202108\ReportService;

use UnexpectedValueException;

/**
 * This example retrieves and runs a saved report query.
 */
class RunSavedQuery
{

    const SAVED_QUERY_ID = 'INSERT_SAVED_QUERY_ID_HERE';

    public static function runExample(
        ServiceFactory $serviceFactory,
        AdManagerSession $session,
        int $savedQueryId
    ) {
        $reportService = $serviceFactory->createReportService($session);

        // Create statement to retrieve the saved query.
        $statementBuilder = (new StatementBuilder())->where('id = :id')
            ->orderBy('id ASC')
            ->limit(1)
            ->withBindVariableValue('id', $savedQueryId);

        $savedQueryPage = $reportService->getSavedQueriesByStatement(
            $statementBuilder->toStatement()
        );
        $savedQuery = $savedQueryPage->getResults()[0];

        if ($savedQuery->getIsCompatibleWithApiVersion() === false) {
            throw new UnexpectedValueException(
                'The saved query is not compatible with this API version.'
            );
        }

}}

RunSavedQuery::main();
