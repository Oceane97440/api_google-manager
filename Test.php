<?php


$file_csv='./proxy_error_log';
$data = file_get_contents($file_csv);
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

                //var_dump(end($dataArray));
                var_dump($dataArray);

   
            }

        }        

}


?>