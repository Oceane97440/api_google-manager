<?php
ini_set('max_execution_time', 0);

include('./includes/config.php');
require 'vendor/autoload.php';


$arrayCorrespondance = array(
    '480x320' => '79633',
    '1024x768' => '79633',
    '768x1024' => '79633',
    '320x480' => '79633',
    '320x50' => '79637',
    '"768x1024 - PORSCHE"'=>'79633',
    '"768x1024 - ETNIA"'=>'79633',
    '"480x320 - PORSCHE"'=>'79633',
    '"320x480 - ETNIA"'=>'79633',
    '"320x480 - PORSCHE"'=>'79633',
    '"480x320 - ETNIA"'=>'79633',
    '"1024x768 - ETNIA"'=>'79633',
    '"1024x768 - PORSCHE"'=>'79633',
	"768 x 1024"=>'79633',
	"320 x 480"=>'79633',
	"1024 x 768"=>'79633',
	
	'"480 x 320"'=> '79633',
    '"1024 x 768"' => '79633',
    '"768 x 1024"' => '79633',
    '"320 x 480"' => '79633',
    '"320 x 50"' => '79637',
    );

    $arrayCorrespondance2 = array(
        '480x320' => 'INTERSTITIEL',
        '1024x768' => 'INTERSTITIEL',
        '768x1024' => 'INTERSTITIEL',
        '320x480' => 'INTERSTITIEL',
        '320x50' => 'MASTHEAD',
		"768 x 1024"=>'INTERSTITIEL',
		"320 x 480"=>'INTERSTITIEL',
		"1024 x 768"=>'INTERSTITIEL',
		"480 x 320"=> 'INTERSTITIEL',
        '"768x1024 - PORSCHE"'=>'INTERSTITIEL',
        '"768x1024 - ETNIA"'=>'INTERSTITIEL',
        '"480x320 - PORSCHE"'=>'INTERSTITIEL',
        '"320x480 - ETNIA"'=>'INTERSTITIEL',
        '"320x480 - PORSCHE"'=>'INTERSTITIEL',
        '"480x320 - ETNIA"'=>'INTERSTITIEL',
        '"1024x768 - ETNIA"'=>'INTERSTITIEL',
        '"1024x768 - PORSCHE"'=>'INTERSTITIEL'
    
        );

        $path = 'data/json';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

       
foreach (glob('data/csv/'.date('Y/m/d').'/*.csv') as $file_csv) :

$myObj = array();
    echo "$file_csv occupe " . filesize($file_csv) . "<br />\n";

    preg_match('/campaignID-([0-9]+)/', $file_csv, $matches);


    if (file_exists($file_csv) && (!empty($matches[0]))) {
        $campaign_id_admanager = $matches[0];

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
              //  var_dump($dataArray);

                if(!empty($dataArray)) {

                    //initialise les array somme impression et somme click
                    $sumAll = array();
                    $clicksAll = array();
                    $sumInterstitielImpressions = array();
                    $sumInterstitielClicks = array();
                    $sumMastheadImpressions = array();
                    $sumMastheadClicks = array();

                    //on parcours le tableau
                    foreach($dataArray as $key => $item):

                        //on recupére chaque impression et clicks puis push dans un array
                        $sumAll[] = $item[9]; 
                        $clicksAll[] = $item[10];

                        // var_dump($item[5]); var_dump($arrayCorrespondance[$item[5]]); exit;
                        // var_dump($item[6]); var_dump($arrayCorrespondance[$item[6]]); exit;

                        // si le format est un INTERSTITIEL
                        if ($arrayCorrespondance[$item[6]] === "79633") {
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

                   var_dump($sumInterstitielImpressions);
                    var_dump($sumMastheadImpressions);
                  
                    // exit;


                    // Créer l'object Interstitiel
                    if(!empty($sumInterstitielImpressions) && !empty($sumInterstitielClicks)) {

                        $sumInterstitielImpressionsTotal = array_sum($sumInterstitielImpressions);
                        $sumInterstitielClicksTotal = array_sum($sumInterstitielClicks);
                        $sumInterstitielCTR = round(($sumInterstitielClicksTotal/$sumInterstitielImpressionsTotal*100),2);
                        var_dump($sumInterstitielCTR);

                        echo '----------------------';

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
                   // var_dump($sumMastheadImpressions);
                   echo '<hr />';
                    // Créer l'object Masthead
                    if(!empty($sumMastheadImpressions) && !empty($sumMastheadClicks)) {
                        $sumMastheadImpressionsTotal = array_sum($sumMastheadImpressions);
                        $sumMastheadClicksTotal = array_sum($sumMastheadClicks);
                        $sumMastheadCTR = round(($sumMastheadClicksTotal/$sumMastheadImpressionsTotal*100),2);
                        
                        var_dump($sumMastheadCTR);
                        echo '----------------------';


                                $myObj['masthead'] = array(        
                                    'impressions' => $sumMastheadImpressionsTotal,
                                    'clicks' => $sumMastheadClicksTotal,
                                    'ctr' => $sumMastheadCTR,
                                    'siteList' => array(                                    
                                        'site' =>"ANTENNEREUNION.FR (app)",
                                        'impressions' => $sumMastheadImpressionsTotal,
                                        'clicks' => $sumMastheadClicksTotal,
                                        'ctr' => $sumMastheadCTR
                                    )
                                );  
                            
                            
                         
                 
                    }

                    $impressions_global = array_sum($sumAll);                                                        
                    $clicks_global = array_sum($clicksAll);
                    $ctr_global = round(($clicks_global / $impressions_global*100),2);
                    $reporting_start_date = date('Y-m-d H:i:s');

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




    


?>