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

// Requête liste tous les campagnes SMART qui ont pour format (INTERSTITIEL , MASTHEAD)
//$last_3_month =  date("Y-m-d",strtotime("-3 month"));
$last_3_month = date("Y-n-j", strtotime("first day of previous month"));

$req=$bdd->prepare('SELECT DISTINCT asb_insertions.campaign_id ,asb_campaigns.campaign_name FROM asb_insertions, asb_campaigns WHERE asb_insertions.format_id IN (79409,79633,44152) AND asb_insertions.campaign_id = asb_campaigns.campaign_id AND asb_campaigns.campaign_start_date >= ?
GROUP BY asb_insertions.campaign_id , asb_insertions.format_id  
ORDER BY `asb_campaigns`.`campaign_name` ASC');
$req->execute(array('2021-11-01'));


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
	$campaign_end_date = $campaign_admanager_exist['campaign_admanager_end_date'];

    $campaign_id_admanager = $campaign_admanager_exist['campaign_id'];


 


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
        
       $filePath = sprintf('campaignID-'.$campaign_id_admanager.'.csv.gz');

        // Download the report.
        $reportDownloader->downloadReport(
            ExportFormat::CSV_DUMP,
            $filePath
        );



        $path = 'data/csv/'.date('Y/m/d');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file_name='campaignID-'.$campaign_id_admanager.'.csv.gz';

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

        $file_exist = 'campaignID-'.$campaign_id_admanager.'.csv';

        if (file_exists($file_exist)) {

            rename($file_exist,'data/csv/'.date('Y/m/d').'/campaignID-'.$campaign_id_admanager.'.csv');
            unlink($file_name);
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