<?php

namespace App\Controller;

use App\Entity\Om;
use App\Entity\Masao;
use App\Entity\Affichage;
use App\Entity\Traitement;
use App\Entity\TicketsRegroup;
use App\Repository\OmRepository;
use App\Controller\EntityManager;
use App\Repository\MasaoRepository;
use App\Controller\RequeteController;
use App\Repository\AffichageRepository;
use App\Repository\TraitementRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TicketsRegroupRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
/**
 * @IsGranted("ROLE_USER")
 */
class IndexController extends AbstractController
{

    public function gestionGlobal(EntityManagerInterface $manager){
        $conn = $manager->getConnection();
        $selectPresta= $conn->query("SELECT * from prestataire ")->fetchAll();
        $cp= 0;
        // On parcourt tout les presta pour les stocker dans des variables
        // Ex : liste1 = Tout les tickets Masao , nomPresta1 = masao
        // cp = nombre de presta
        foreach($selectPresta as $unPresta){
            $cp= $cp +1 ; 
            $nomBase = $unPresta['nom'];
            ${'liste'.$cp} =  $conn->query("SELECT * from $nomBase ")->fetchAll();
            ${'nomPresta'.$cp} = $nomBase;
        }
        // On parcourt les tickets d'un presta à la fois, puis on compare la ref avec les tickets des autres presta 
        // Limit permet de parcourir tout les autres presta
        for ($i=1; $i<=$cp ; $i++) { 
            foreach (${'liste'.$i} as $ticket1){
                $limit = $i+1;
                while($limit <= $cp) {
                    foreach (${'liste'.$limit} as $ticket2){
                        $this->compareRef($ticket1 ,$ticket2 , ${'nomPresta'.$i} , ${'nomPresta'.$limit} , $manager) ; 
                    }
                    $limit = $limit +1;
                }
                // Insertion de chaque ticket dans traitement
                $this->insertTraitement($ticket1,${'nomPresta'.$i},$manager);
            }
        }
        //Insertion des tickets regroup dans traitement
        $conn = $manager->getConnection();
        $listeTicketsRegroup =  $conn->query("SELECT * from tickets_regroup ")->fetchAll();    
        foreach($listeTicketsRegroup as $unTicketRegroup) {
            $this->insertTraitement($unTicketRegroup , 'regroup' , $manager);
        }
        
    }
    
    public function compareRef($ticket1 , $ticket2, $presta1 , $presta2 , EntityManagerInterface $manager){
        $ref1 = $ticket1['ref'];
        $ref2 = $ticket2['ref'];
        // Premièrement, on regarde si la ref des tickets est commune , si elle ne l'est pas on ne fait rien
        if($ref1 == $ref2 ) {
            $repoTicketRegroup = $this->getDoctrine()->getRepository(TicketsRegroup::class);
            $listeTicketsRegroup = $repoTicketRegroup->findAll();
            $cpTicketRegroup=0;
             // Si oui, on regarde si le ticket n'est pas déja regroupé. S'il y est on update quand même car un ticket peut-être lié entre plus de 2 presta
            foreach($listeTicketsRegroup as $listeTicket){
                if($listeTicket->getRef()== $ref1){
                    $cpTicketRegroup=1;
                    $conn = $manager->getConnection();
                    $update= $conn->query("UPDATE tickets_regroup set ".$presta1."_id = ".$ticket1['id']." where ref =".$ref1);
                    $update= $conn->query("UPDATE tickets_regroup set ".$presta2."_id = ".$ticket2['id']." where ref =".$ref1);
                }
            }
            // S'il n'est pas encore regroupé, on créer le ticket_regroup avec les deux id de tickets
            if($cpTicketRegroup == 0 ){
                $ticketsRegroup = new TicketsRegroup();
                $ticketsRegroup -> setDateCreation(new \DateTime);
                $ticketsRegroup -> setRef($ref1);
                $manager -> persist($ticketsRegroup);
                $manager->flush();

                $conn = $manager->getConnection();
                $update= $conn->query("UPDATE tickets_regroup set ".$presta1."_id = ".$ticket1['id']." where ref =".$ref1);
                $update= $conn->query("UPDATE tickets_regroup set ".$presta2."_id = ".$ticket2['id']." where ref =".$ref1);
            }
        }

    }
    
    public function insertTraitement($ticket,$presta,EntityManagerInterface $manager){

        $conn = $manager->getConnection();
        $listeTraitement = $conn->query("SELECT * FROM traitement")->fetchAll();
        // On récupère les colonnes qui sont utiles aux informations d'un ticket 
        $colonne= $conn->query("SELECT COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'traitement' 
                                and COLUMN_NAME not in ('id','date_creation','nb_ticket_regroup' ,'type','commentaire','action')
                                and COLUMN_NAME not like ('ticket%')")->fetchAll();
        // On regarde si le ticket n'est pas déja dans la base (avec la ref et le type)
        // type = nom du presta
        // S'il y est déja alors on le modifie 
        $cpTraitement=0;
        foreach ($listeTraitement as $unTraitement){
            if ($presta== $unTraitement['type'] && $ticket['ref'] == $unTraitement['ref']){
                foreach($colonne as $c){
                    $update = $conn->query(' UPDATE traitement SET '.$c['COLUMN_NAME'].' = "'.$ticket[$c['COLUMN_NAME']].'" where id='.$unTraitement['id']);
                }
                $cpTraitement = 1 ;
            }
        }
        // S'il n'y est pas, selon le type(regrouper ou non) , on insère le ticket dans traitement
        if($cpTraitement==0 && $presta != 'regroup'){
            $cpColonne=1;
            foreach($colonne as $c){
                if($cpColonne ==1 ){
                    $insert=$conn->query('INSERT INTO traitement ('.$c['COLUMN_NAME'].') VALUES ("'.$ticket[$c['COLUMN_NAME']].'")');
                    $cpID = $conn->lastInsertId();
                }
                else{
                    $update = $conn->query(' UPDATE traitement SET '.$c['COLUMN_NAME'].' = "'.$ticket[$c['COLUMN_NAME']].'" where id='.$cpID);
                }
                $cpColonne = $cpColonne +1 ;
            }
            $updateType = $conn->query('UPDATE traitement SET type = "'.$presta.'" where id='.$cpID);
            $updateIdPresta_Traitement = $conn->query('UPDATE traitement SET ticket_'.$presta.' = '.$ticket['id'].' where id='.$cpID);
            $date = new \DateTime();
            $date = $date->format('d/m/Y');
            $updateDateCreation = $conn->query('UPDATE traitement SET date_creation = "'.$date.'" where id='.$cpID);
        }
        else if ($cpTraitement==0 && $presta == 'regroup'){
            // A MODIF 

            /*$traitement = new Traitement;
            $traitement -> setType($presta);
            $traitement -> setRef($ticket['ref']);
            $traitement -> setDateCreation(new \DateTime);
            $manager -> persist($traitement);
            $manager->flush();

            $conn = $manager->getConnection();
            $update= $conn->query("UPDATE traitement set ticket_".$presta." = ".$ticket['id']." where ref =".$ticket['ref']." and type ='$presta' ");*/

        }      
    }
 
    public function insertAffichage(EntityManagerInterface $manager){

        $conn = $manager->getConnection();
        $ticketTraitement = $conn->query("SELECT * FROM traitement")->fetchAll();
        $ticketsAffichage = $conn->query("SELECT * FROM affichage")->fetchAll();
        $colonne= $conn->query("SELECT COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'traitement' 
                                and COLUMN_NAME not in ('id','date_creation','nb_ticket_regroup')
                                and COLUMN_NAME not like ('ticket%')")->fetchAll();

        // On regarde si le ticket n'est pas déja dans la base (avec la ref et le type)
        foreach($ticketTraitement as $t){
            $cp=0;
            foreach($ticketsAffichage as $ticketAffichage){
                // S'il y est, on le modifie
                if($ticketAffichage['ref'] == $t['ref'] && $ticketAffichage['type'] == $t['type']) {
                    //$this->modifTraitementVersAffichage($t , $ticketAffichage , $manager);
                    $cp=1;
                }
            }
            // Si non, on l'insère dans la base
            if($cp==0) {
                $cpColonne=1;
                foreach($colonne as $c){
                    if($cpColonne==1){
                        $insert=$conn->query('INSERT INTO affichage ('.$c['COLUMN_NAME'].') VALUES ("'.$t[$c['COLUMN_NAME']].'")');
                        $cpID = $conn->lastInsertId();
                    }
                    else{
                        $update=$conn->query('UPDATE affichage SET '.$c['COLUMN_NAME'].' = "'.$t[$c['COLUMN_NAME']].'" where id='.$cpID);
                    }
                    $cpColonne = $cpColonne +1 ; 
                }
                $insertIdTraitement = $conn->query('UPDATE affichage SET traitement_id ='.$t['id'].' where id='.$cpID );
            }       
        }   
    }
 
    public function modifTraitementVersAffichage($traitement , $affichage ,$colonne, EntityManagerInterface $manager  ){
        // on parcours tout les traitements et les affichages
        //Si l'id est en commun et que la mise à jour est != , on parcourt toutes les colonnes et on modifie 
        $conn = $manager->getConnection();
            foreach($traitement as $t){
                foreach($affichage as $a){
                    if($a['traitement_id'] == $t['id']){
                        if($a['mise_a_jour'] != $t['mise_a_jour']){
                            foreach($colonne as $c){
                                $update = $conn->query('UPDATE affichage set '.$c['COLUMN_NAME'].' = "'.$t[$c['COLUMN_NAME']].'" where traitement_id ='.$t['id']);
                            }
                        }
                    }
                }
            }
    }

    public function modifUnTraitement($traitement,$colonne,EntityManagerInterface $manager){
        // Modification de un traitement
        $conn = $manager->getConnection();
        foreach($colonne as $c){
            $updateTraitement = $conn->query('UPDATE traitement set '.$c['COLUMN_NAME'].' = "'.$traitement[$c['COLUMN_NAME']].'" where id ='.$traitement['id']);
            $updateAffichage = $conn->query('UPDATE affichage set '.$c['COLUMN_NAME'].' = "'.$traitement[$c['COLUMN_NAME']].'" where traitement_id ='.$traitement['id']);
        }
    }


}
