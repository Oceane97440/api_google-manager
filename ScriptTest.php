                               
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time', 0);

require("includes/config.php");

$last_3_month =  date("Y-m-d",strtotime("-3 month"));




$req=$bdd->prepare('SELECT DISTINCT asb_insertions.campaign_id ,asb_campaigns.campaign_name FROM asb_insertions, asb_campaigns WHERE asb_insertions.format_id IN (79409,79633,44152) AND asb_insertions.campaign_id = asb_campaigns.campaign_id AND asb_campaigns.campaign_start_date >= ?
GROUP BY asb_insertions.campaign_id , asb_insertions.format_id  
ORDER BY `asb_campaigns`.`campaign_name` ASC');
$req->execute(array($last_3_month));

$donnees = $req->fetch();

var_dump($donnees);


    $nom_file = "fichier.txt";

    echo  $nom_file;
    $texte =  $donnees['campaign_id'];

    // création du fichier
    $f = fopen($nom_file, "x+");
    // écriture
    fputs($f, $texte );
    // fermeture
    fclose($f);
?>

