
  
<?php

require 'vendor/autoload.php';
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\Util\v202011\ReportDownloader;
use Google\AdsApi\AdManager\Util\v202011\StatementBuilder;
use Google\AdsApi\AdManager\v202011\ExportFormat;
use Google\AdsApi\AdManager\v202011\ReportJob;
use Google\AdsApi\AdManager\v202011\ReportQueryAdUnitView;
use Google\AdsApi\AdManager\v202011\ServiceFactory;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\v202011\ReportService;

use Google\AdsApi\AdManager\v202011\SavedQuery;

//use UnexpectedValueException;

/**
 * This example retrieves and runs a saved report query.
 */

class RunSavedQuery
{

    const SAVED_QUERY_ID = '2895370071';

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
        $savedQuery = $savedQueryPage->getResults();

        if ($savedQuery->getIsCompatibleWithApiVersion() === false) {
            throw new UnexpectedValueException(
                'The saved query is not compatible with this API version.'
            );
        }

       /* // Optionally modify the query.
        $reportQuery = $savedQuery->getReportQuery();
        $reportQuery->setAdUnitView(ReportQueryAdUnitView::HIERARCHICAL);

        // Create report job using the saved query.
        $reportJob = new ReportJob();
        $reportJob->setReportQuery($reportQuery);

        $reportJob = $reportService->runReportJob($reportJob);

        // Create report downloader to poll report's status and download when
        // ready.
        $reportDownloader = new ReportDownloader(
            $reportService,
            $reportJob->getId()
        );
        if ($reportDownloader->waitForReportToFinish()) {
            // Write to system temp directory by default.
            $filePath = sprintf(
                '%s.csv.gz',
                tempnam(sys_get_temp_dir(), 'saved-report-')
            );
            printf("Downloading report to %s ...%s", $filePath, PHP_EOL);
            // Download the report.
            $reportDownloader->downloadReport(
                ExportFormat::CSV_DUMP,
                $filePath
            );
            print "done.\n";
        } else {
            print "Report failed.\n";
        }*/
    }

    public static function main()
    {
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()
            ->build();
        $session = (new AdManagerSessionBuilder())->fromFile()
            ->withOAuth2Credential($oAuth2Credential)
            ->build();
        self::runExample(
            new ServiceFactory(),
            $session,
            intval(self::SAVED_QUERY_ID)
        );
    }
}

RunSavedQuery::main();