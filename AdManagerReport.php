<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time', 0);

require 'vendor/autoload.php';


//use Google\AdsApi\AdManager\v202108\DateTime;


//use Google\AdsApi\AdManager\v202108\DateTimeZone;
use Google\AdsApi\AdManager\Util\v202108\AdManagerDateTimes;
// use DateTime;
// use DateTimeZone;

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


use Google\AdsApi\AdManager\v202108\Column;
use Google\AdsApi\AdManager\v202108\DateRangeType;
use Google\AdsApi\AdManager\v202108\Dimension;
use Google\AdsApi\AdManager\v202108\DimensionAttribute;
use Google\AdsApi\AdManager\v202108\ReportQuery;
include('includes/config.php');

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
//$last_3_month =  date("Y-m-d",strtotime("-3 month"));
$last_date = date("Y-m-d",strtotime("-2 month"));

$req=$bdd->prepare('SELECT MIN(campaign_admanager_start_date)FROM `asb_campaigns_admanager` WHERE campaign_admanager_start_date >= ?');
$req->execute(array($last_date));


$donnees = $req->fetch();
$campaign_start_date = $donnees[0];

var_dump($campaign_start_date);

class RunSavedQuery
{

public function bdd() {
   
   
    $last_date = date("Y-m-d",strtotime("-2 month"));

    $req=$bdd->prepare('SELECT MIN(campaign_admanager_start_date)FROM `asb_campaigns_admanager` WHERE campaign_admanager_start_date >= ?');
    $req->execute(array($last_date));
   
    $last_date = date("Y-m-d",strtotime("-2 month"));

    $req=$bdd->prepare('SELECT MIN(campaign_admanager_start_date)FROM `asb_campaigns_admanager` WHERE campaign_admanager_start_date >= ?');
    $req->execute(array($last_date));


    $donnees = $req->fetch();
    $campaign_start_date = $donnees[0];

    return $campaign_start_date;

    // var_dump($campaign_start_date);

}






    //campagne_id de google ad manager
    //const ORDER_ID = '2925179751';

    const ORDER_DELIVERY_STATUS = 'STARTED';

    public static function runExample(
        ServiceFactory $serviceFactory,
        AdManagerSession $session
        //int $orderId
    ) 
    {
        $reportService = $serviceFactory->createReportService($session);

        // Create report query.
        $reportQuery = new ReportQuery();
        $reportQuery->setDimensions(
            [
                Dimension::ORDER_ID,
                Dimension::ORDER_NAME,
                //format id format_name
                Dimension::PLACEMENT_ID,
                Dimension::PLACEMENT_NAME,
                //recup??ration data creative 
                Dimension::CREATIVE_ID,
                Dimension::CREATIVE_NAME,
                Dimension::CREATIVE_TYPE,
                Dimension::CREATIVE_SIZE,



                


            ]
        );
        $reportQuery->setDimensionAttributes(
            [
                DimensionAttribute::ORDER_START_DATE_TIME,
                DimensionAttribute::ORDER_END_DATE_TIME
            ]
        );
        $reportQuery->setColumns(
            [
                Column::AD_SERVER_IMPRESSIONS,
                Column::AD_SERVER_CLICKS,
                Column::AD_SERVER_CTR,

            ]
        );

            // Create statement to filter for an order.
            $statementBuilder = (new StatementBuilder())
            ->where('ORDER_DELIVERY_STATUS = :orderDeliveryStatus')
            ->withBindVariableValue(
                'orderDeliveryStatus',
               'STARTED'
            );
           /* ->where('ORDER_ID = :orderId')
            ->withBindVariableValue(
                'orderId',
                $orderId
            );*/
  
         
          // Set the filter statement.
        $reportQuery->setStatement($statementBuilder->toStatement());

        $campaign_start_date = bdd();
                    // Set the start and end dates or choose a dynamic date range type.
        $reportQuery->setDateRangeType(DateRangeType::CUSTOM_DATE);
        $reportQuery->setStartDate(
            AdManagerDateTimes::fromDateTime(
                new DateTime(
                    $campaign_start_date,
                    new DateTimeZone('America/New_York')
                )
            )
                ->getDate()
        );

         $reportQuery->setEndDate(
            AdManagerDateTimes::fromDateTime(
                new DateTime(
                    	'now',
                    new DateTimeZone('America/New_York')
                )
            )
                ->getDate()
        );
        var_dump($reportQuery);


        // Create report job and start it.
      $reportJob = new ReportJob();
      $reportJob->setReportQuery($reportQuery);
      $reportJob = $reportService->runReportJob($reportJob);


      //var_dump($reportJob);
  
      // Create report downloader to poll report's status and download when
      // ready.
      $reportDownloader = new ReportDownloader(
        $reportService,
        $reportJob->getId()
    );

   // var_dump($reportDownloader);


    if ($reportDownloader->waitForReportToFinish()) {
        // Write to system temp directory by default.

        $filePath = sprintf('file.csv.gz');

       // printf("Downloading report to %s ...%s", $filePath, PHP_EOL);
        // Download the report.
        $reportDownloader->downloadReport(
            ExportFormat::CSV_DUMP,
            $filePath
        );

       // print "done.\n";

        
        $path = 'taskId/'.date('Y/m/d/H');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file_name='./file.csv.gz';

        //Fonction qui extrait le fichier csv du dossier compr??sser
        //Raising this value may increase performance
        $buffer_size = 4096; //read 4kb at a time
        $out_file_name = str_replace('.gz', '', $file_name);

        //Open our files (in binary mode)
        $file = gzopen($file_name, 'rb');
        $out_file = fopen($out_file_name, 'wb');

        //Keep repeating until the end of the input file
        while(!gzeof($file)) {
            fwrite($out_file, gzread($file, $buffer_size));
        }

        //Files are done, close files
        fclose($out_file);
        gzclose($file);


        $file_exist = './file.csv';
        if (file_exists($file_exist)) {

        rename('./file.csv','./taskId/file.csv');
        unlink('./file.csv.gz');


        }

     
  

    } else {
        print "Report failed.\n";
    }

         $file_csv='./taskId/file.csv';

        if (file_exists($file_csv)) {
           // $handle = fopen($file_csv, "r");
            //$data = fgetcsv($handle);
           // var_dump($data[0]);  
          //  exit;
            //renvoi la data en json     
            // echo json_encode($data);


            function read($csv){
                $file = fopen($csv, 'r');
                while (!feof($file) ) {
                    $line[] = fgetcsv($file, 1024);
                }
                fclose($file);
                return $line;
            }
            // D??finir le chemin d'acc??s au fichier CSV
            $csv = $file_csv;
    
            $csv = read($csv);
           // var_dump($csv[0]);  
            //  exit;
            $o = json_encode($csv);

            var_dump($o[0]);  
             exit;

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
            intval(self::ORDER_DELIVERY_STATUS)
        );
    }
}

RunSavedQuery::main();


