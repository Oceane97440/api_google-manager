<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time', 0);

require ('vendor/autoload.php');

use Google\AdsApi\AdManager\Util\v202108\AdManagerDateTimes;
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


$last_date = date("Y-m-d",strtotime("-2 month"));

$req=$bdd->prepare('SELECT MIN(campaign_admanager_start_date)FROM `asb_campaigns_admanager` WHERE campaign_admanager_start_date >= ?');
$req->execute(array($last_date));


$donnees = $req->fetch();
$campaign_start_date = $donnees[0];


    
        $reportService = $serviceFactory->createReportService($session);

        $reportQuery = new ReportQuery();
        $reportQuery->setDimensions(
            [
                Dimension::ORDER_ID,
                Dimension::ORDER_NAME,
                  //format id format_name
                  Dimension::PLACEMENT_ID,
                  Dimension::PLACEMENT_NAME,
                  //recupération data creative 
                  Dimension::CREATIVE_ID,
                  Dimension::CREATIVE_NAME,
                //  Dimension::CREATIVE_TYPE,
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
   
           // Set the filter statement.
         $reportQuery->setStatement($statementBuilder->toStatement());
  
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
        
       $filePath = sprintf('campaignID-fileAll.csv.gz');

        // Download the report.
        $reportDownloader->downloadReport(
            ExportFormat::CSV_DUMP,
            $filePath
        );



        $path = 'data/csv/'.date('Y/m/d');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file_name='campaignID-fileAll.csv.gz';

        //Fonction qui extrait le fichier csv du dossier comprésser
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

        $file_exist = 'campaignID-fileAll.csv';


        if (file_exists($file_exist)) {

            rename($file_exist,'data/csv/'.date('Y/m/d').'/campaignID-fileAll.csv');
            unlink($file_name);


        // lecture des fichiers csv
        $file_csv = 'data/csv/'.date('Y/m/d').'/campaignID-fileAll.csv';

    if (file_exists($file_csv)) {

        $srcFile = new SplFileObject($file_csv);
        foreach ($srcFile as $line) {
            $item = explode(',',$line);

            if(count($item) > 1) {
                $dataArray[] = $item;
            }
        }

        $campaigns = array();
        if(!empty($dataArray)) {

        $columnKey = $dataArray[0];
        $keyPrimary = $columnKey[0]; 

        foreach($dataArray as $k => $i) {

            if((count($columnKey) === count($i)) && ($k != 0)) {  

                $newItem = array(); 
                for($y = 0; $y < count($i); $y++) {
                    $newItem[trim($columnKey[$y])] = trim($i[$y]);
                }
            }
            if(!empty($newItem) ) {

                $valuePrimary = $newItem[$keyPrimary];
                $campaigns[$valuePrimary][] = $newItem;
            }       
        }      
        if (!empty($campaigns) && (count($campaigns) > 0)) {
            foreach ($campaigns  as $key => $value){
                echo $key;

                $fp = fopen('data/csv/'.date('Y/m/d').'/campaignID-'.$key.'.csv', 'w');
                $arrayLabels = array_keys($value[0]);

                fputcsv($fp, $arrayLabels);

                foreach ($value as $key0 => $value0) {
                    fputcsv($fp,$value0);
                    var_dump($value0);
                }            
                fclose($fp);
            }         
        }
        echo '<hr />';
    }

    }
            

}

   


        
        
        



}




    
     

   
    




exit();



    


?>