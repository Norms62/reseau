<?php

namespace App\Controller;

use App\Entity\Masao;
use App\Entity\Upload;
use League\Csv\Reader;
use App\Entity\Filtrer;
use App\Form\BoutonType;
use App\Form\UploadType;
use App\Form\FiltrerType;
use App\Entity\Traitement;
use App\Controller\IndexController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_USER")
 */
class ImportController extends IndexController
{

    /**
     * @Route("/import" , name="import")
     */
    public function import(Request $request, EntityManagerInterface $manager){
        
        // Si on tombe sur la page d'erreur et on choisi d'insérer tout de même les données : 
        if(isset($_POST["btnInserer"])) {
            $conn = $manager->getConnection();
            $presta = $_POST['presta'];
            $ColonnePrestaCsv = $conn->query("SELECT * from csv_$presta")->fetchAll();
            // csv_presta permet de mémoriser les colonnes du csv que l'on souhaite garder
            // Si cette table n'existe pas , TableExist = false (Cela changera l'affichage)
            $selectTouteLesTables= $conn->query("   SELECT COLUMN_NAME, TABLE_NAME as nomTable
                                                    FROM INFORMATION_SCHEMA.COLUMNS 
                                                    WHERE COLUMN_NAME LIKE 'date_creation' ")->fetchAll();
            $tableExist = false;
            foreach($selectTouteLesTables as $uneTable){
                if($uneTable['nomTable'] == 'csv_'.$presta){
                    $tableExist = true ;
                }
            }
            return $this->render('import/messageApresImport.html.twig',[
                'presta' => $presta,
                'tableExist' => $tableExist,
                'colonnePrestaCsv'=>$ColonnePrestaCsv,
            ]);
        }

        //Voir si la table intermédiaire existe
        //Return false si la table n'existe pas
        $conn = $manager->getConnection();
        $selectInter = $conn->query("SELECT  TABLE_NAME
        FROM INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='intermediaire' ")->fetch();
        if($selectInter != false ){
            $suppTable = $conn->query("DROP TABLE intermediaire");
        }
        // Affiche le formulaire pour faire l'importation
        $upload = new Upload();
        $form = $this->createForm(UploadType::class , $upload);  
        $form -> handleRequest($request);

        // Une fois validé, on génère le fichier pour le mettre dans un dossier upload 
        // Puis on insère les données du fichier dans une table intermédiaire (baseIntermédiaire(csv))
        if($form->isSubmitted() && $form-> isValid()) {
            $file = $upload->getNom();  
            $presta = $upload->getPresta();;
            $presta = $presta->getNom();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('upload_directory'), $fileName);
            $upload -> setNom($fileName);
            $chemin = $this->getParameter('upload_directory').'/'.$fileName;
            $csv = Reader::createFromPath($chemin , 'r');   
            $nbErreur=$this->baseIntermediaire($csv , $manager);
            //Suppression du fichier dans upload 
            \unlink($chemin);
            if(count($nbErreur)>0 && $nbErreur[0] != 0 ) {
                return $this->render('import/ErreurInfoTicket.html.twig',[
                    'nbErreur' => $nbErreur,
                    'presta' => $presta
                ]);
            }
            else{
                // csv_presta permet de mémoriser les colonnes du csv que l'on souhaite garder
                // Si cette table n'existe pas , TableExist = false (Cela changera l'affichage)
                $conn = $manager->getConnection();
                $selectTouteLesTables= $conn->query("   SELECT COLUMN_NAME, TABLE_NAME as nomTable
                                                        FROM INFORMATION_SCHEMA.COLUMNS 
                                                        WHERE COLUMN_NAME LIKE 'date_creation' ")->fetchAll();
                $tableExist = false;
                foreach($selectTouteLesTables as $uneTable){
                    if($uneTable['nomTable'] == 'csv_'.$presta){
                        $tableExist = true ;
                    }
                }
                if($tableExist == true){
                    //Pour afficher les colonnes liés entre presta et csv 
                    $ColonnePrestaCsv = $conn->query("SELECT * from csv_$presta")->fetchAll();
                    //Colonnes du csv qui ne sont pas utiliser 
                    $colonneNonUtilise = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'intermediaire' and COLUMN_NAME != 'idPresta'
                                                       and COLUMN_NAME not in ( SELECT colonneCSV from csv_$presta )")->fetchAll();
                    //Colonnes qui sont dans csv_presta mais plus dans le fichier CSV 
                    $colonneExistePlus = $conn->query("SELECT ColonneCSV from csv_$presta where ColonneCSV not in 
                                                       (select COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'intermediaire' and COLUMN_NAME != 'idPresta') ")->fetchAll();

                    return $this->render('import/messageApresImport.html.twig',[
                        'presta' => $presta,
                        'tableExist' => $tableExist,
                        'colonnePrestaCsv'=>$ColonnePrestaCsv,
                        'colonneNonUtilise' => $colonneNonUtilise,
                        'colonneExistePlus' => $colonneExistePlus
                    ]); 
                }
                else{
                    return $this->render('import/messageApresImport.html.twig',[
                        'presta' => $presta,
                        'tableExist' => $tableExist,
                    ]);
                }
            }
            
                
        }
            return $this->render('ticket/import.html.twig',[
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/liaison/{presta}/{exist}" , name="liaison")
     */
    public function Liaison($presta,$exist, EntityManagerInterface $manager,Request $request) {
        // Si oui on veux changer les liaisons
        if($exist == "oui"){
            $test = array();
            $form = $this->createForm(BoutonType::class,$test);
            $form-> handleRequest($request);
            // Envoie des colonnes du presta et colonnnes des tickets 
            $conn = $manager->getConnection();
            $colonnePresta= $conn->query( " SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME ='$presta'  
                                            and COLUMN_NAME not in ('id' , 'date_creation')")->fetchAll();
            $colonneCSV= $conn->query( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='intermediaire' 
                                        and COLUMN_NAME not in ('idPresta')")->fetchAll();

                if($form->isSubmitted() && $form->isValid()){
                    // Parcours de toutes les tables de la base
                    $conn = $manager->getConnection();
                    $selectTouteLesTables= $conn->query("SELECT COLUMN_NAME, TABLE_NAME as nomTable
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE COLUMN_NAME LIKE 'date_creation' ")->fetchAll();

                    // Vérification si la table csv_presta existe
                    $tableExist = false;
                    foreach($selectTouteLesTables as $uneTable){
                        if($uneTable['nomTable'] == 'csv_'.$presta){
                            $tableExist = true ;
                        }
                    }
                    // Si la table csv_presta n'existe pas, on la créer
                    if($tableExist == false){
                        $conn = $manager->getConnection();
                        $create= $conn->query( "CREATE TABLE csv_$presta (  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT , date_creation VARCHAR(255) , 
                                                                            colonnePresta VARCHAR(255) , colonneCSV VARCHAR(255)  )  ");
                    }
                    //Si elle existe, on la supprime et on la recréer.
                    else{
                        $conn = $manager->getConnection();
                        $drop = $conn->query("DROP TABLE csv_$presta");
                        $create= $conn->query( "CREATE TABLE csv_$presta ( id INT PRIMARY KEY NOT NULL AUTO_INCREMENT , date_creation VARCHAR(255) , colonnePresta VARCHAR(255) , colonneCSV VARCHAR(255))  ");
                    }
                    //Puis on insère les colonnes choisis par l'utilisateur
                    foreach($colonneCSV as $c) {
                        if($_POST[$c['COLUMN_NAME']] != ""){
                            $conn = $manager->getConnection();
                            $insert = $conn->query("INSERT INTO csv_$presta (colonneCsv , colonnePresta) 
                                                    VALUES (\"".$c['COLUMN_NAME']."\"  ,\"".$_POST[$c['COLUMN_NAME']]."\")");
                        }
                    }
                    //On fini par insérer les données selon les colonnes
                    $this->insertDonneesCSV($presta,$manager);
                    IndexController::gestionGlobal($manager);
                    return $this->redirectToRoute('ticket');
                }
                return $this->render('import/liaison.html.twig',[
                    'form' => $form->createView(),
                    'colonnePresta'=>$colonnePresta,
                    'colonneCSV' => $colonneCSV,
                    'presta'=>$presta
                ]);        }
        //Si on ne veut pas changer les colonnes, on insère les données
        else{
            $this->insertDonneesCSV($presta,$manager);
            IndexController::gestionGlobal($manager);
            return $this->redirectToRoute('ticket');        
        }    
    }

    // Insertion des données dans la table du presta
    public function insertDonneesCSV($presta , $manager){
        $conn = $manager->getConnection();
        $baseInter= $conn->query( "SELECT * from intermediaire")->fetchAll();
        $listeColonne = $conn->query("SELECT * from csv_$presta ")->fetchAll();
        $selectPresta = $conn->query("SELECT * from $presta")->fetchAll();
        $cp=1;
        //On parcourt la table csv_presta et on stocke dans les variables les noms des colonnes du csv et du presta
        //On stocke en variable le nom de la colonne csv pour voir apres si le ticket existe deja
        foreach($listeColonne as $l ){
            ${'colonnePresta'.$cp} = $l['colonnePresta'];
            ${'colonneCSV'.$cp} = $l['colonneCSV'];
            if($l['colonnePresta'] == 'ref'){
                $refColonneCsv = $l['colonneCSV'];
            }
            $cp = $cp+1;
        }
        // On parcourt tout les tickets du fichier csv que l'on a mis dans une base intermédiaire
        // si i == 1 , il s'agit de la première colonne, donc on insère le ticket puis au autre colonne on update ce ticket
        foreach($baseInter as $ligneInter){
            $TicketExist = false;
            foreach($selectPresta as $unTicket){
                if($unTicket['ref'] == $ligneInter[$refColonneCsv]){
                    $TicketExist = true;
                }
            }
            //Parcourt de toutes les colonnes
            for ($i=1; $i < $cp ; $i++) { 
                if($TicketExist == false){
                    if($i==1){
                        //Première colonne, donc on insert
                        $insert = $conn->query("INSERT INTO ".$presta." (".${'colonnePresta'.$i}.") VALUES (\"".$ligneInter[${'colonneCSV'.$i}]."\") ");
                        $cpID = $conn->lastInsertId();
                        $date = new \DateTime();
                        $date = $date->format('d/m/Y');
                        $update = $conn->query("UPDATE ".$presta." SET date_creation = \"".$date."\" WHERE id = ".$cpID."");
                    }
                    else{
                        //Reste des colonnes donc on modifie 
                        $update = $conn->query("UPDATE ".$presta." SET ".${'colonnePresta'.$i}." = \"".$ligneInter[${'colonneCSV'.$i}]."\" WHERE id = ".$cpID."");
                        //Si la date de mise a jour est null , on la crée 00/01/2000
                        $selectDate = $conn->query("SELECT mise_a_jour from $presta where id = ".$cpID."")->fetch();
                        if($selectDate['mise_a_jour'] == ''){
                            $updateMiseAJour = $conn->query("UPDATE ".$presta." SET mise_a_jour = '2000-01-01' WHERE id = ".$cpID."");
                        }
                    }
                }
                //Si le ticket existe déja , on récupère l'id du presta et on le modifie. 
                else{
                    $idPresta = $conn->query("SELECT id FROM $presta WHERE ref=".$ligneInter[$refColonneCsv])->fetch();
                    $update = $conn->query("UPDATE ".$presta." SET ".${'colonnePresta'.$i}." = \"".$ligneInter[${'colonneCSV'.$i}]."\" WHERE id = ".$idPresta['id']."");
                    //Si la date de mise a jour est null , on la crée 00/01/2000
                    $selectDate = $conn->query("SELECT mise_a_jour from $presta where id = ".$idPresta['id']."")->fetch();
                    if($selectDate['mise_a_jour'] == ''){
                        $updateMiseAJour = $conn->query("UPDATE ".$presta." SET mise_a_jour = '2000-01-01' WHERE id = ".$idPresta['id']."");
                    }

                }
            }
        }
        // Une fois tout les tickets insérés, on supprime la table intermédiaire
        $deleteTableInter = $conn ->query("DROP TABLE intermediaire");
    }
    
    public function baseIntermediaire($csv,$manager){
        // On parcourt touts les tickets
        // Premère ligne = titre des colonnes
        $cpLigne = 1 ; 
        $cpErreur = 0 ;
        $nbTicketErreur[]=0;
        foreach($csv as $ticket){
            if($cpLigne==1){
                $cpColonne=1;
                foreach($ticket as $t){
                    $t = \str_replace(array(" ", "(" , ")",) , "_" , $t);
                    ${'titreColonne'.$cpColonne}=$t;
                    if($cpColonne==1){
                        $conn = $manager->getConnection();
                        $insert= $conn->query("CREATE TABLE intermediaire ( idPresta int primary key not null auto_increment , ".$t." varchar(255))");
                    }
                    else{
                        $conn = $manager->getConnection();
                        $insert= $conn->query("ALTER TABLE intermediaire ADD ".$t." varchar(2000)");
                    }
                    $cpColonne = $cpColonne +1 ;
                }
                $nbColonne = \count($ticket);
                $cpLigne = $cpLigne +1 ;
            }
            // Ensuite on insère les données
            // Première fois on insère puis ensuite on update
            else{
                $cpDonnees = 1;
                $nbInfoTicket=\count($ticket);
                if($nbInfoTicket > $nbColonne){
                    $nbTicketErreur[$cpErreur] = $ticket[0] ;
                    $cpErreur = $cpErreur +1 ;
                }
                else{
                    foreach($ticket as $d){
                        $d = \str_replace(array("\"") , " " , $d);
                        if($cpDonnees == 1) {
                            $conn = $manager->getConnection();
                            $insert= $conn->query("INSERT INTO intermediaire (".${'titreColonne'.$cpDonnees}.") VALUES (".$d.")");
                            $cpID = $conn->lastInsertId();
                        }
                        else{
                            $conn = $manager->getConnection();
                            $update= $conn->query("UPDATE intermediaire SET ".${'titreColonne'.$cpDonnees}." = \"".$d."\" WHERE idPresta = ".$cpID."");
                        }
                        $cpDonnees =$cpDonnees +1 ;
                    }
                }
                $cpLigne = $cpLigne+1;
            }
        }
        return $nbTicketErreur;
    }
}