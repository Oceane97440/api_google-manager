<?php
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
$req=$bdd->query('SELECT campaign_id , format_id FROM asb_insertions WHERE format_id IN (79409,79633,44152) GROUP BY campaign_id , format_id');

$donnees = $req->fetch();


// On boucle sur la campagne
while ($donnees = $req->fetch())
{
    // pour chaque campagne on recupérer la campagne_id et campagne_name
    $req_campaign=$bdd->prepare("SELECT*FROM asb_campaigns WHERE campaign_id=?");
    $campaign_id= $donnees['campaign_id'];
    $req_campaign->execute(array($campaign_id) );
    $campaign_exist=$req_campaign->rowCount();
    $campaign_exist=$req_campaign->fetch();
    $campaign_name = $campaign_exist['campaign_name'];


    //Vérification si la campagne_name existe déja dans la table asb_campaigns_admanager
    $req_admanager=$bdd->prepare("SELECT*FROM asb_campaigns_admanager WHERE campaign_admanager_name=? AND campaign_admanager_status='APPROVED' "); 
    $req_admanager->execute(array($campaign_name));
    $admanagerexist=$req_admanager->rowCount();

    $campaign_admanager_exist=$req_admanager->fetch();
    $campaign_admanager_name = $campaign_admanager_exist['campaign_admanager_name'];
    $campaign_admanager_id = $campaign_admanager_exist['advertiser_admanager_id'];
    $campaign_admanager_status = $campaign_admanager_exist['campaign_admanager_status'];



    //si sa existe on crée les rapport pour les campagnes trouvées
    if($admanagerexist==1)
    {

        echo $campaign_admanager_name;
        echo $campaign_admanager_id;
        echo $campaign_admanager_status;
    
    
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
 
        // var_dump($reportQuery);
 
        // Set the start and end dates or choose a dynamic date range type.
        $reportQuery->setDateRangeType(DateRangeType::TODAY);

        //var_dump($reportQuery);

        
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

        var_dump($filePath);


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

      /*  $file_csv='./taskId/file-'.$campaign_admanager_id.'.csv';

        if (file_exists($file_csv)) {
            $handle = fopen($file_csv, "r");
            $data = fgetcsv($handle);
          //  var_dump($data);  
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
            // Définir le chemin d'accès au fichier CSV
            $csv = $file_csv;
            $csv = read($csv);
            echo json_encode($csv);

        }




*/







    }

    
  

}
// $req->closeCursor(); // Termine le traitement de la requête



    


?>