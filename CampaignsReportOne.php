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
/*
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
*/
            $campaign_admanager_name = 'ARIBEV - 69483';
            $campaign_id_admanager = '1912738';

            $arrayCorrespondance = array(
                '480 x 320' => '79633',
                '1024 x 768' => '79633',
                '768 x 1024' => '79633',
                '320 x 480' => '79633',
                '320 x 50' => '79637'
    
            
                );

                $arrayCorrespondance2 = array(
                    '480 x 320' => 'INTERSTITIEL ',
                    '1024 x 768' => 'INTERSTITIEL',
                    '768 x 1024' => 'INTERSTITIEL',
                    '320 x 480' => 'INTERSTITIEL',
                    '320 x 50' => 'MASTHEAD'
        
                
                    );



                $file_csv='./taskId/campaignID-'.$campaign_id_admanager.'.csv';
                if (file_exists($file_csv)) {
                // Récupére l'ensemble du contenu du fichier
                $data = file_get_contents($file_csv);
               
                // 
                if(!empty($data) && (preg_match_all('(.*)',$data,$out) )) {
                    $dataArray = array();

                    // Créer un tableau à partir d'un string                   
                    if(count($out) > 0) {
                        foreach($out[0] as $key => $item):
                            if(!empty($item) and ($key > 0)) {
                                $dataArray[] = explode(',',$item);
                            }
                        endforeach;
                       
                        if(!empty($dataArray)) {
                            foreach($dataArray as $key => $item):
                                $myObj[] = array(
                                    'campaign_id '=> $item[0],
                                    'campaign_name' => $item[1],
                                    'format_id'=>  $arrayCorrespondance[$item[6]],
                                    'format_name' => $arrayCorrespondance2[$item[6]],
                                    'creative_id' => $item[4],
                                    'creative_name' => $item[5],
                                    'creative_size' => $item[6],
                                    'campaign_start_date' => $item[7],
                                    'campaign_end_date' => $item[8],
                                    'impressions' => $item[9],
                                    'clicks' => $item[10],
                                    'ctr' => $item[11]
                                );
                            endforeach;
                            
                            $myJSON = json_encode($myObj);

                            echo $myJSON;


                            $bytes = file_put_contents("./taskId/json/campaignID-".$campaign_id_admanager.".json", $myJSON); 
                        }

                    }


                    
                   

                }



            }

        





    
     

   
    
  

  



    





exit();



    


?>