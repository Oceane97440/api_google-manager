<?php
 // lecture des fichiers csv
 $file_csv = 'data/csv/'.date('Y/m/d').'/campaignID-fileAll.csv';

 if (file_exists($file_csv)) {
    $srcFile = new SplFileObject($file_csv);
    foreach ($srcFile as $line) {
        $item = explode(',',$line);
     // var_dump($item);
        if(count($item) > 1) {
         $dataArray[] = $item;
        }
    }
    $campaigns = array();
    if(!empty($dataArray)) {
        $columnKey = $dataArray[0];
        $keyPrimary = $columnKey[0];      
     //   echo $keyPrimary; exit;
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

               /* for ($i=0; $i < count($newItem); $i++) { 
                    $fp = fopen('data/csv/'.date('Y/m/d').'/campaignID-'.$valuePrimary.'.csv', 'w');

                 
                }*/
        
            }       
        }       


        if (!empty($campaigns) && (count($campaigns) > 0)  ) {

            foreach ($campaigns  as $key => $value){

                echo $key;

                $fp = fopen('data/csv/'.date('Y/m/d').'/campaignID-'.$key.'.csv', 'w');

                $array = array_keys($value[0]);
               // fputcsv($fp,array_keys($value));
                fputscsv($fp, $array);

                var_dump(array_keys($value[0]));

                exit;

                foreach ($value as $key0 => $value0) {

                    fputcsv($fp,$value0);

                }

                echo '  ------  ';

               // echo $value[0];

                var_dump( $value);

                fclose($fp);

            }
            /*foreach ($campaigns as  $campaigns[$valuePrimary]) {

                if (!empty($campaigns[$valuePrimary])) {
    
                    var_dump($campaigns[$valuePrimary]);
    
                    fputcsv($fp,$campaigns['2925179751']);
    
    
                }
            }*/
            
           // fclose($fp);

        }

   

        

        echo '<hr />';
    }
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
      //   $o = json_encode($csv);
*/
        // echo $o;  
          exit;
 }
?>

<?php
 // lecture des fichiers csv
 $file_csv = 'campaignID-fileAll.csv';
 if (file_exists($file_csv)) {
    $srcFile = new SplFileObject($file_csv);
    foreach ($srcFile as $line) {
        $item = explode(',',$line);
     // var_dump($item);
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
        if (!empty($campaigns) && (count($campaigns) > 0)  ) {
            foreach ($campaigns  as $key => $value){
                echo $key;
                $fp = fopen('campaignID-'.$key.'.csv', 'w');
                $arrayLabels = array_keys($value[0]);
               fputcsv($fp, $arrayLabels);
                foreach ($value as $key0 => $value0) {
                    fputcsv($fp,$value0);
                }            
                fclose($fp);
            }         
        }
        echo '<hr />';
    }
 }
?>