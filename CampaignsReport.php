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

// Requête liste tous les campagnes SMART qui ont pour format (INTERSTITIEL , MASTHEAD)
$req=$bdd->query('SELECT DISTINCT asb_insertions.campaign_id ,asb_campaigns.campaign_name FROM asb_insertions, asb_campaigns WHERE asb_insertions.format_id IN (79409,79633,44152) AND asb_insertions.campaign_id = asb_campaigns.campaign_id AND asb_campaigns.campaign_start_date >= "2021-06-01 00:00:00"
GROUP BY asb_insertions.campaign_id , asb_insertions.format_id  
ORDER BY `asb_campaigns`.`campaign_name` ASC');

/*
SELECT DISTINCT asb_insertions.campaign_id ,asb_campaigns.campaign_name FROM asb_insertions, asb_campaigns WHERE asb_insertions.format_id IN (79409,79633,44152) AND asb_insertions.campaign_id = asb_campaigns.campaign_id AND asb_campaigns.campaign_start_date >= '2021-06-01 00:00:00'
GROUP BY asb_insertions.campaign_id , asb_insertions.format_id  
ORDER BY `asb_campaigns`.`campaign_name` ASC
*/


$donnees = $req->fetch();



// On boucle sur la campagne
$i = 0;
while ($donnees = $req->fetch())
{

    
    $campaign_id= $donnees['campaign_id'];
    $campaign_name = $donnees['campaign_name'];

    //Vérification si la campagne_name existe déja dans la table asb_campaigns_admanager
    $req_admanager=$bdd->prepare("SELECT*FROM asb_campaigns_admanager WHERE campaign_id=? AND campaign_admanager_name=? AND campaign_admanager_status='APPROVED' "); 
    $req_admanager->execute(array($campaign_id,$campaign_name));
    $admanagerexist=$req_admanager->rowCount();


    $campaign_admanager_exist=$req_admanager->fetch();

    $campaign_admanager_name = $campaign_admanager_exist['campaign_admanager_name'];
    $campaign_start_date = $campaign_admanager_exist['campaign_admanager_start_date'];

 


     //si sa existe on crée les rapport pour les campagnes trouvées
     if($admanagerexist==1)
     {

        
        echo $campaign_admanager_name.' 2-'.date('h:i:s') . "<br>";

    
    
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

        
        // lecture des fichiers csv

        $file_exist = './file-'.$campaign_admanager_name.'.csv';

        if (file_exists($file_exist)) {

            rename($file_exist,'./taskId/file-'.$campaign_admanager_name.'.csv');
            unlink($file_name);
        }





        $file_csv='./taskId/file-'.$campaign_admanager_name.'.csv';

        $row = 1;
        if (($handle = fopen($file_csv, "r")) !== FALSE) {

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);

                $row++;

                for ($c=0; $c < $num; $c++) {

                    $json = json_encode($data[$c]);
                  //  $bytes = file_put_contents("./taskId/json/".$campaign_admanager_name.".json", $json); 
                    //echo $json ;

                }

            

            }
            fclose($handle);
        }
        
        



        }




    
     }

   
    
     if($i === 5){
       sleep(60);

        $i = 0;
      }  
    
      $i++;
  

  

  



    }




exit();



    


?>