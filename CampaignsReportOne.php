<?php
ini_set('max_execution_time', 0);

include('./includes/config.php');
require 'vendor/autoload.php';

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








 
 //$campaign_admanager_name = 'ARACT REUNION - 68877';
 $campaign_admanager_name = 'CANAL CBOX - 70063';
 $campaign_admanager_id = '19554';





 
        

    
    
        $reportService = $serviceFactory->createReportService($session);

        $reportQuery = new ReportQuery();
        $reportQuery->setDimensions(
            [
                Dimension::ORDER_ID,
                Dimension::ORDER_NAME,
  
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

            ]
        );

            // Create statement to filter for an order.
            $statementBuilder = (new StatementBuilder())
            ->where('ORDER_NAME = :orderName')
            ->withBindVariableValue(
                'orderName',
                $campaign_admanager_name
                
            );
   
           // Set the filter statement.
         $reportQuery->setStatement($statementBuilder->toStatement());
  
        // Set the start and end dates or choose a dynamic date range type.

        $campaign_start_date = '2021-07-02 09:14:00';
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
        
       $filePath = sprintf('file-'.$campaign_admanager_name.'.csv.gz');

        // Download the report.
        $reportDownloader->downloadReport(
            ExportFormat::CSV_DUMP,
            $filePath
        );


        $path = 'taskId';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file_name='./file-'.$campaign_admanager_name.'.csv.gz';

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


        $file_exist = './file-'.$campaign_admanager_name.'.csv';


        if (file_exists($file_exist)) {

        rename($file_exist,'./taskId/file-'.$campaign_admanager_name.'.csv');
        unlink($file_name);
        }




   



        }

            $file_csv='./taskId/file-'.$campaign_admanager_name.'.csv';

            if (file_exists($file_csv)) {
                $handle = fopen($file_csv, "r");
                $data = fgetcsv($handle);
        
                $row = 1;
                $handle = fopen($file_csv, "r");
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);
                  
                    $row++;
                 
                        $myObj = new stdClass();
                        $myObj->campaign_id = $data[0];
                        $myObj->campaign_name = $data[1];
                        $myObj->campaign_start_date = $data[2];
                        $myObj->campaign_end_date = $data[3];
                        $myObj->impressions = $data[4];
    
    
                        $myJSON = json_encode($myObj);
    
                        echo $myJSON;

                        $bytes = file_put_contents("./taskId/json/campaignID-".$campaign_admanager_id, $myJSON); 

                    
                }
                fclose($handle);
                
        /*
                function read($csv){
                    $file = fopen($csv, 'r');
                    while (!feof($file) ) {
                        $line[] = fgetcsv($file, 1024);
                    }
                    fclose($file);
                    return $line;
                }
                // Définir le chemin d'accès au fichier CSV
                $csv = $file_csv;
                $csv = read($csv);
                $json_encode =  json_encode($csv);
               // echo $json_encode[3][1];*/

               /* $json = json_encode($csv);
                $bytes = file_put_contents("./campaignID-".$campaign_admanager_id.".json", $json); */
        
            }
        





    
     

   
    
  

  



    





exit();



    


?>