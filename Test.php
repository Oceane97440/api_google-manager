<?php
include('./includes/config.php');


$last_3_month = date("Y-m-d",strtotime("-3 month"));

$req=$bdd->prepare('SELECT DISTINCT asb_insertions.campaign_id ,asb_campaigns.campaign_name FROM asb_insertions, asb_campaigns WHERE asb_insertions.format_id IN (79409,79633,44152) AND asb_insertions.campaign_id = asb_campaigns.campaign_id AND asb_campaigns.campaign_start_date >= ?
GROUP BY asb_insertions.campaign_id , asb_insertions.format_id  
ORDER BY `asb_campaigns`.`campaign_name` ASC');

$req->execute(array($last_3_month));




$donnees=$req->fetch();


echo $donnees['campaign_id'];


?>