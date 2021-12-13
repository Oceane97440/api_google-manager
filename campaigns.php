<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time', 0);

require ('vendor/autoload.php');

use Google\AdsApi\AdManager\v202108\DateTime;
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\Util\v202108\StatementBuilder;
use Google\AdsApi\AdManager\v202108\ServiceFactory;
use Google\AdsApi\Common\OAuth2TokenBuilder;

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
$orderService = $serviceFactory->createOrderService($session);

// Make a request
/*$network = $orderService->getOrdersByStatement();
printf(
    "Network with code %d and display name '%s' was found.\n",
    $network->getNetworkCode(),
    $network->getDisplayName()
);*/






        // Create a statement to select orders.
        $pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;
        $statementBuilder = (new StatementBuilder())->orderBy('id ASC')
            ->where('isArchived = false')
            ->limit(1000);

        // Retrieve a small amount of orders at a time, paging
        // through until all orders have been retrieved.
        $totalResultSetSize = 0;
        do {
            $page = $orderService->getOrdersByStatement(
                $statementBuilder->toStatement()
            );

            // Print out some information for each order.
            if ($page->getResults() !== null) {

                $totalResultSetSize = $page->getTotalResultSetSize();

                $i = $page->getStartIndex();
                foreach ($page->getResults() as $order) {

                   $campaign_id= $order->getId();
                   $campaign_name= $order->getName();
                   $advertiser_id = $order->getadvertiserId();
                   $campaign_status = $order->getstatus();

                
                   
                   /*printf(
                        "%d) Order with ID %d and name '%s' was found.%s advertiserId ",

                        $i++,
                        $order->getId(),
                        $order->getName(),
                        $order->getadvertiserId(),


                        PHP_EOL
                    );*/
                   

                    //Recupère l'ensemble des campagne qui match avec les campagne de gam
                    $req_campaign=$bdd->prepare("SELECT*FROM asb_campaigns WHERE campaign_name=?");
                    $req_campaign->execute(array($campaign_name) );
                    $campaign_exist=$req_campaign->rowCount();
                    $campaign_exist=$req_campaign->fetch();
                    $campaign_id_smart = $campaign_exist['campaign_id'];
                    $campaign_start_date = $campaign_exist['campaign_start_date'];
                    $campaign_end_date = $campaign_exist['campaign_end_date'];

                   //Test si la campaign_admanager_id existe 
                    $empty_campaign=$bdd->prepare("SELECT campaign_admanager_id FROM asb_campaigns_admanager WHERE `campaign_admanager_id` = ?");
                    $empty_campaign->execute(array($campaign_id));
                    $campaign_found=$empty_campaign->fetch();
                   $found = $campaign_found['campaign_admanager_id'];



                    //si la campagne n'existe pas on ajout a la bdd
                    if (empty($found)) {
                        var_dump($found);

                        $getcampaigns = $bdd -> prepare('INSERT INTO asb_campaigns_admanager (campaign_admanager_id,campaign_id,advertiser_admanager_id,campaign_admanager_name,campaign_admanager_start_date,campaign_admanager_end_date,campaign_admanager_status) VALUES (?,?,?,?,?,?,?)');
                        $getcampaigns ->execute(array($campaign_id,$campaign_id_smart,$advertiser_id,$campaign_name,$campaign_start_date,$campaign_end_date,$campaign_status));

                     
                    }else{
                        echo 'Les campagnes ont été ajoutées'.'<hr>';
                    }
                 


                
                }
        
            }

            $statementBuilder->increaseOffsetBy($pageSize);
        } while ($statementBuilder->getOffset() < $totalResultSetSize);

        printf("Number of results found: %d%s", $totalResultSetSize, PHP_EOL);
    


