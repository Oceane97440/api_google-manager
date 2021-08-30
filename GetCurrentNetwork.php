<?php
ini_set('max_execution_time', 0);


require 'vendor/autoload.php';
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202108\ApiException;
use Google\AdsApi\AdManager\v202108\ServiceFactory;
use Google\AdsApi\Common\OAuth2TokenBuilder;

use Google\AdsApi\AdManager\Util\v202108\ReportDownloader;
use Google\AdsApi\AdManager\Util\v202108\StatementBuilder;
use Google\AdsApi\AdManager\v202108\ExportFormat;
use Google\AdsApi\AdManager\v202108\ReportJob;
use Google\AdsApi\AdManager\v202108\ReportQueryAdUnitView;
//use UnexpectedValueException;

// Generate a refreshable OAuth2 credential for authentication.
$oAuth2Credential = (new OAuth2TokenBuilder())
    ->fromFile()
    ->build();
// Construct an API session configured from a properties file and the OAuth2
// credentials above.
$session = (new AdManagerSessionBuilder())
    ->fromFile()
    ->withOAuth2Credential($oAuth2Credential)
    ->build();

// Get a service.
$serviceFactory = new ServiceFactory();
$networkService = $serviceFactory->createNetworkService($session);

// Make a request
$network = $networkService->getCurrentNetwork();
printf(
    "Network with code %d and display name '%s' was found.\n",
    $network->getNetworkCode(),
    $network->getDisplayName()
);

class RunSavedQuery
{

    const SAVED_QUERY_ID = '12304979660';

    public static function runExample(
        ServiceFactory $serviceFactory,
        AdManagerSession $session,
        int $savedQueryId
    ) 
    {
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
               print('The saved query is not compatible with this API version.')
            );
        }else{
            print('Request ready');
  
        } 
        $reportQuery = $savedQuery->getReportQuery();

        // Create report job and start it.
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
            'csv.gz',
            rename('./csv.gz','./taskId/my_file.csv.gz')
        );
        
         

        printf("Downloading report to %s ...%s", $filePath, PHP_EOL);
        // Download the report.
        $reportDownloader->downloadReport(
            ExportFormat::CSV_DUMP,
            $filePath
        );

        print "done.\n";

        
        $path = 'taskId/'.date('Y/m/d/H');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

     
       /* $handle = fopen($filePath, "r");
        $data = fgetcsv($handle);
        var_dump($data);*/

        
        // open file for reading
        // $file_name= 'C:/wamp64/www/api_google-manager/taskId/my_file.csv.gz';
        $file_name= './taskId/my_file.csv.gz';

        $zp = gzopen($file_name, "r");
        echo gzread($zp, 3);
        gzpassthru($zp);
        gzclose($zp);



        //This input should be from somewhere else, hard-coded in this example

        //Raising this value may increase performance
        $buffer_size = 4096; //read 4kb at a time
        $out_file_name = str_replace('.gz', '', $file_name);

        //Open our files (in binary mode)
        $file = gzopen($file_name, 'rb');
        $out_file = fopen($out_file_name, 'wb');

        //Keep repeating until the end of the input file
        while(!gzeof($file)) {
            //Read buffer-size bytes
            //Both fwrite and gzread and binary-safe
            fwrite($out_file, gzread($file, $buffer_size));
        }

        //Files are done, close files
        fclose($out_file);
        gzclose($file);



        /*$zip = new ZipArchive;
        $res = $zip->open('C:/wamp64/www/api_google-manager/report.zip');
        $zip->extractTo('C:/wamp64/www/api_google-manager');
        $zip->close();*/
  

    } else {
        print "Report failed.\n";
    }

    
    
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


