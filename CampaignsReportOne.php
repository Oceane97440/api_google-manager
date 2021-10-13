<?php
ini_set('max_execution_time', 0);

include('./includes/config.php');
require 'vendor/autoload.php';


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

        $path = 'data/json';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

       
foreach (glob('data/csv/'.date('Y/m/d').'/*.csv') as $file_csv) :

    echo "$file_csv occupe " . filesize($file_csv) . "<br />\n";

    preg_match('/campaignID-([0-9]+)/', $file_csv, $matches);



    if (file_exists($file_csv) && (!empty($matches[0]))) {
        $campaign_id_admanager = $matches[0];

        var_dump($campaign_id_admanager);
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

                    //initialise les array somme impression et somme click
                    $sumAll = array();
                    $clicksAll = array();

                    //on parcours le tableau
                    foreach($dataArray as $key => $item):

                        //on recupére chaque impression et clicks puis push dans un array
                        $sumAll[] = $item[9];
                        $clicksAll[] = $item[10];


                        // si le format est un INTERSTITIEL
                        if ($arrayCorrespondance[$item[6]] === "79633") {
                            var_dump($item);

                             //on recupére et on fait la somme global impression/click de l'INTERSTITIEL

                             $sumInterstitielImpressions[] = $item[9];

                             $sumInterstitielClicks[] = $item[10];
                        }
                        if ($arrayCorrespondance[$item[6]] === "79637") { 
                             //on recupére et on fait la somme global impression/click de l'MASTHEAD

                            $sumMastheadImpressions[] = $item[9];
                            $sumMastheadClicks[] = $item[10];
                        }
                    endforeach;

                    // Créer l'object Interstitiel
                    if(!empty($sumInterstitielImpressions) && !empty($sumInterstitielClicks)) {
                        $sumInterstitielImpressionsTotal = array_sum($sumInterstitielImpressions);
                        $sumInterstitielClicksTotal = array_sum($sumInterstitielClicks);
                        $sumInterstitielCTR = round(($sumInterstitielClicksTotal/$sumInterstitielImpressionsTotal*100),2);
                        echo  $sumInterstitielCTR ;

                        $myObj['interstitiel'] = array(                                    
                            'impressions' =>  $sumInterstitielImpressionsTotal,
                            'clicks' => $sumInterstitielClicksTotal,
                            'ctr' => $sumInterstitielCTR,
                            'siteList' => array(                                    
                                                'site' =>"ANTENNEREUNION.FR (app)",
                                                'impressions' =>  $sumInterstitielImpressionsTotal,
                                                'clicks' => $sumInterstitielClicksTotal,
                                                'ctr' =>$sumInterstitielCTR
                                            )
                        );     
                    }

                    // Créer l'object Masthead
                    if(!empty($sumMastheadImpressions) && !empty($sumMastheadClicks)) {
                        $sumMastheadImpressionsTotal = array_sum($sumMastheadImpressions);
                        $sumMastheadClicksTotal = array_sum($sumMastheadClicks);
                        $sumMastheadCTR = round(($sumMastheadClicksTotal/$sumMastheadImpressionsTotal*100),2);
                        echo  $sumMastheadCTR ;
                        
                        $myObj['masthead'] = array(        
                            'impressions' => $sumMastheadImpressionsTotal,
                            'clicks' => $sumMastheadClicksTotal,
                            'ctr' => $sumInterstitielCTR,
                            'siteList' => array(                                    
                                'site' =>"ANTENNEREUNION.FR (app)",
                                'impressions' => $sumMastheadImpressionsTotal,
                                'clicks' => $sumMastheadClicksTotal,
                                'ctr' => $sumInterstitielCTR
                            )
                        );                              
                    }

                    $impressions_global = array_sum($sumAll);                                                        
                    $clicks_global = array_sum($clicksAll);
                    $ctr_global = round(($clicks_global / $impressions_global*100),2);
                    $reporting_start_date = date('Y-m-d H:i:s');
                    echo  $ctr_global ;

                    $myObj['campaign'] = array(
                        'campaign_id'=> $item[0],
                        'campaign_name' => $item[1],
                        'campaign_start_date' => $item[7],
                        'campaign_end_date' => $item[8],
                        'impressions' => $impressions_global,
                        'clicks' => $clicks_global,
                        'ctr' => $ctr_global,
                        'reporting_start_date' => $reporting_start_date
                       // 'ctr' => number_format($ctr_global, 2, '.', '')
                    );
                    
                    $myJSON = json_encode($myObj);
                    echo $myJSON;
                    $bytes = file_put_contents("./data/json/".$campaign_id_admanager.".json", $myJSON); 
                }

            }



           

        }



    }



endforeach;




















exit;


            $campaign_admanager_name = 'CANAL CBOX - 70063';
            $campaign_id_admanager = '1921947';
           //$campaign_admanager_name = 'ARIBEV - 69483';
            //$campaign_id_admanager =  1912738;

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

                            //initialise les array somme impression et somme click
                            $sumAll = array();
                            $clicksAll = array();

                            //on parcours le tableau
                            foreach($dataArray as $key => $item):

                                //on recupére chaque impression et clicks puis push dans un array
                                $sumAll[] = $item[9];
                                $clicksAll[] = $item[10];


                                // si le format est un INTERSTITIEL
                                if ($arrayCorrespondance[$item[6]] === "79633") {
                                    var_dump($item);

                                     //on recupére et on fait la somme global impression/click de l'INTERSTITIEL

                                     $sumInterstitielImpressions[] = $item[9];

                                     $sumInterstitielClicks[] = $item[10];
                                }
                                if ($arrayCorrespondance[$item[6]] === "79637") { 
                                     //on recupére et on fait la somme global impression/click de l'MASTHEAD

                                    $sumMastheadImpressions[] = $item[9];
                                    $sumMastheadClicks[] = $item[10];
                                }
                            endforeach;

                            // Créer l'object Interstitiel
                            if(!empty($sumInterstitielImpressions) && !empty($sumInterstitielClicks)) {
                                $sumInterstitielImpressionsTotal = array_sum($sumInterstitielImpressions);
                                var_dump($sumInterstitielImpressionsTotal);
                                $sumInterstitielClicksTotal = array_sum($sumInterstitielClicks);
                                $sumInterstitielCTR = round(($sumInterstitielClicksTotal/$sumInterstitielImpressionsTotal*100),2);

                                $myObj['interstitiel'] = array(                                    
                                    'impressions' =>  $sumInterstitielImpressionsTotal,
                                    'clicks' => $sumInterstitielClicksTotal,
                                    'ctr' => $sumInterstitielCTR,
                                    'siteList' => array(                                    
                                                        'site' =>"ANTENNEREUNION (APP)",
                                                        'impressions' =>  $sumInterstitielImpressionsTotal,
                                                        'clicks' => $sumInterstitielClicksTotal,
                                                        'ctr' =>$sumInterstitielCTR
                                                    )
                                );     
                            }

                            // Créer l'object Masthead
                            if(!empty($sumMastheadImpressions) && !empty($sumMastheadClicks)) {
                                $sumMastheadImpressionsTotal = array_sum($sumMastheadImpressions);
                                $sumMastheadClicksTotal = array_sum($sumMastheadClicks);
                                $sumMastheadCTR = round(($sumMastheadClicksTotal/$sumMastheadImpressionsTotal *100),2);
                                
                                $myObj['masthead'] = array(        
                                    'impressions' => $sumMastheadImpressionsTotal,
                                    'clicks' => $sumMastheadClicksTotal,
                                    'ctr' => $sumInterstitielCTR,
                                    'siteList' => array(                                    
                                        'site' =>"ANTENNEREUNION (APP)",
                                        'impressions' => $sumMastheadImpressionsTotal,
                                        'clicks' => $sumMastheadClicksTotal,
                                        'ctr' => $sumInterstitielCTR
                                    )
                                );                              
                            }
     
                            $impressions_global = array_sum($sumAll);                                                        
                            $clicks_global = array_sum($clicksAll);
                            $ctr_global = round(($clicks_global / $impressions_global *100),2);
                            var_dump($ctr_global);
                            
                            $myObj['campaign'] = array(
                                'campaign_id'=> $item[0],
                                'campaign_name' => $item[1],
                                'campaign_start_date' => $item[7],
                                'campaign_end_date' => $item[8],
                                'impressions' => $impressions_global,
                                'clicks' => $clicks_global,
                                'ctr' => $ctr_global
                               // 'ctr' => number_format($ctr_global, 2, '.', '')
                            );
                            
                            $myJSON = json_encode($myObj);
                            echo $myJSON;
                            $bytes = file_put_contents("./taskId/json/campaignID-".$campaign_id_admanager.".json", $myJSON); 
                        }

                    }



                   

                }



            }

        





    
     

   
    
  

  



    





exit();



    


?>