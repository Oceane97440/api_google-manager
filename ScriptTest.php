<?php
 // lecture des fichiers csv
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
?>