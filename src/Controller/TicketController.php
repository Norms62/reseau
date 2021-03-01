<?php

namespace App\Controller;

use App\Entity\Import;
use App\Entity\Upload;
use League\Csv\Reader;
use App\Entity\Filtrer;
use App\Form\BoutonType;
use App\Form\UploadType;
use App\Form\FiltrerType;
use App\Entity\Traitement;
use App\Form\TraitementType;
use App\Controller\IndexController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @IsGranted("ROLE_USER")
 */
class TicketController extends IndexController
{
    /**
     * @Route("/", name="ticket")
     */
    public function listeTicket(EntityManagerInterface $manager, Request $request)
    {   
        // Formulaire des filtres
        $test = array();
        $form = $this -> createForm(BoutonType::class , $test);
        $form-> handleRequest($request);

        //Info qui servira a comparer la date de mise a jour pour voir si il y a eu modif sur le ticket
        $conn = $manager->getConnection();
        $affichage= $conn   ->query("SELECT * FROM affichage order by mise_a_jour desc")->fetchAll();
        $traitement= $conn  ->query("SELECT * FROM traitement order by type")->fetchAll();
        $colonne = $conn    ->query("SELECT COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'traitement' 
                                     and COLUMN_NAME not in ('id','date_creation','nb_ticket_regroup')
                                     and COLUMN_NAME not like ('ticket%')")->fetchAll();
        $selectNouveauTicket = $conn->query("SELECT * from traitement where id not in (select traitement_id from affichage)")->fetchAll();
        
        //Afficher le tableau peu importe le nb de colonne
        $colonneTable = $conn->query("  SELECT COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'affichage' 
                                        and COLUMN_NAME not in ('id','type','mise_a_jour','date_creation','nb_ticket_regroup','ref','traitement_id') ")->fetchAll();

        //Infos qui servira au filtre
        $presta = $conn     ->query("SELECT * FROM prestataire ")->fetchAll();
        $priorite=$conn     ->query("SELECT DISTINCT(priorite) from affichage where priorite != '' ");
        $impact = $conn     ->query("SELECT DISTINCT(impact) from affichage where impact != '' ");
        $etat = $conn       ->query("SELECT DISTINCT(etat) from affichage where etat != ''");
        $resolution = $conn ->query("SELECT DISTINCT(resolution) from affichage where resolution != ''");

        //Si on valide les mise a jour, le traitement va vers affichage
        if(isset($_POST["btnModif"])) {
            $this->modifTraitementVersAffichage($traitement , $affichage,$colonne, $manager);
            $this->insertAffichage($manager);
            $this->gestionGlobal($manager);
            return $this->redirectToRoute('ticket');
        }

        //Permet de filtrer selon les infos demandées
        if ($form->isSubmitted() && $form->isValid()) {
            $filtre_presta = $_POST['nomPresta'];
            $filtre_priorite=$_POST['priorite'];
            $filtre_impact=$_POST['impact'];
            $filtre_resolution=$_POST['resolution'];
            $filtre_etat=$_POST['etat'];
            
            //Début de la requete
            // where id!='' permet de passer direct apres a la condition AND
            $conn = $manager->getConnection();
            $affichage = "SELECT * FROM affichage WHERE id!=''";
            //On regarde quel filtre l'utilisateur a choisi et on adapte la requete
            if($filtre_presta != "tout"){
                $affichage = $affichage." AND type='$filtre_presta'";
            }
            if($filtre_etat != "tout"){
                $affichage = $affichage." AND etat='$filtre_etat'";
            }
            if($filtre_impact != "tout"){
                $affichage = $affichage." AND impact='$filtre_impact'";
            }
            if($filtre_priorite != "tout"){
                $affichage = $affichage." AND priorite='$filtre_priorite'";
            }
            if($filtre_resolution != "tout"){
                $affichage = $affichage." AND resolution='$filtre_resolution'";
            }
            // Puis on éxécute la requete
            $affichage = $affichage." order by mise_a_jour desc";
            $affichage= $conn->query($affichage)->fetchAll();

            //return $this->redirectToRoute('ticket');
        }

        return $this->render('ticket/listeTicket.html.twig', [
            'listeAffichage' => $affichage,
            'form' => $form->createView(),
            'presta' => $presta,
            'traitement' => $traitement,
            'colonneTable' => $colonneTable,
            'priorite'=>$priorite,
            'impact'=>$impact,
            'etat'=>$etat,
            'resolution'=>$resolution,
            'nvxTickets' =>$selectNouveauTicket
        ]);
    }

    /**
     * @Route("/ticket/{id}" , name = "modifTicket")
     */
    public function modifTicket($id , Request $request, EntityManagerInterface $manager) {
        // Formulaire permettant de modifier le ticket
        $conn = $manager->getConnection(); 
        $DonneesTraitement= $conn->query("SELECT * FROM traitement WHERE id=$id")->fetch();
        $colonne = $conn->query("SELECT COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'traitement' 
                                and COLUMN_NAME not in ('id','type','date_creation','date_soumission','nb_ticket_regroup','ref')
                                and COLUMN_NAME not like ('ticket%')")->fetchAll();

        $test = array();
        $form = $this -> createForm(BoutonType::class , $test);
        $form-> handleRequest($request);

        //Si on clique sur supprimer
        //On supprime dans l'odre affichage -> traitement -> Presta
        //Avant de supp traitement on récupere le nom du presta et son ID
        if(isset($_POST["btnSupp"])) {
            $conn = $manager->getConnection();
            $selectPresta = $conn->query("SELECT * FROM prestataire")->fetchAll();
            //On regarde si le ticket vient d'un presta ou non 
            $nomPresta=$conn->query("SELECT type FROM traitement WHERE id=".$id)->fetch();
            $prestaOuNon = false;
            foreach($selectPresta as $presta){ 
                if($presta['nom']==$nomPresta['type']) {
                    $prestaOuNon = true;
                }
            }

            //S'il vient d'un presta on le supprime de partout (affichahe -> traitement -> presta)
            //Sinon on supprime que dans affichage -> traitement
            //Avant de supp dans presta on selectionne son id depuis traitement
            if($prestaOuNon==true){
                $idPresta=$conn->query("SELECT ticket_".$nomPresta['type']." FROM traitement where id=$id ")->fetch();
                $suppAffichage=$conn->query("DELETE FROM affichage WHERE traitement_id=$id");
                $suppTraitement=$conn->query("DELETE FROM traitement where id=$id");
                //Suppression dans tickets regroupés.  
                $ticketR = $conn->query("SELECT count(id) as nb , id from tickets_regroup where ".$nomPresta['type']."_id = ".$idPresta["ticket_".$nomPresta['type']])->fetch();
                if($ticketR['nb'] == 1 ){
                    $supprimer = $conn->query("DELETE from tickets_regroup where id=".$ticketR['id']);
                }
                $suppPresta=$conn->query("DELETE FROM ".$nomPresta['type']." WHERE id=".$idPresta["ticket_".$nomPresta['type']]);
                
            }
            else{
                $suppAffichage=$conn->query("DELETE FROM affichage WHERE traitement_id=$id");
                $suppTraitement=$conn->query("DELETE FROM traitement where id=$id");
            }
            return $this->redirectToRoute('ticket');
        }

        // Une fois validé, on modifie ce ticket puis on affiche la liste des tickets
        if($form->isSubmitted()){
            if(!isset($_POST["btnSupp"])) {
                $conn = $manager->getConnection(); 
                foreach($colonne as $c){
                    $update = $conn->query('UPDATE traitement set '.$c['COLUMN_NAME'].' = "'.$_POST[$c['COLUMN_NAME']].'" where id='.$id);
                }
                $DonneesTraitement= $conn->query("SELECT * FROM traitement WHERE id=$id")->fetch();
                $this->modifUnTraitement($DonneesTraitement,$colonne,$manager);
                return $this->redirectToRoute('ticket');
            }
        }

        return $this->render('ticket/modifTicket.html.twig',[
            'colonne' =>  $colonne,
            'form' => $form->createView(),
            'donneesTraitement' => $DonneesTraitement
        ]);
    }

    /**
     * @Route("/ticketRegroup" , name = "tickets_regroup")
     */
    public function listeTicketsRegroup(Request $request, EntityManagerInterface $manager){
        //Selection de tous les tickets Regroups
        $tab[]='';
        $conn = $manager->getConnection(); 
        $selectTicketsRegroups = $conn->query("SELECT * FROM tickets_regroup")->fetchAll();
        //Selection de tous les prestas
        $selectPresta = $conn->query("SELECT * FROM prestataire")->fetchAll();
        foreach($selectTicketsRegroups as $ticketsRegroups){
            //Si le ticket est lié a un presta on le stock dans un tableau nommé tab['id du ticket regroupé']['nom prestataire']
            foreach($selectPresta as $presta){
                if($ticketsRegroups[$presta['nom'].'_id'] != ''){
                    $ticket=$conn->query("SELECT * from traitement where ticket_".$presta['nom']."=".$ticketsRegroups[$presta['nom'].'_id'])->fetchAll();
                    $tab[$ticketsRegroups['id']][$presta['nom'].'_id']=$ticket;
                }
            }
        }
        // Selection colonne pour l'affichage et colonne pour affichage des données
        $colonneTable = $conn->query("  SELECT COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'affichage' 
                                        and COLUMN_NAME not in ('id','type','mise_a_jour','date_creation','nb_ticket_regroup','ref','traitement_id') ")->fetchAll();

        return $this->render('ticket/ticketsRegroup.html.twig',[
            'ticketsRegroup' => $selectTicketsRegroups,
            'tab'=>$tab,
            'colonneTable'=>$colonneTable,
        ]);
    }

    /**
     * @Route("/ajoutTicket" , name = "ajoutTicket")
     */
    public function ajoutTicket(Request $request, EntityManagerInterface $manager, UserInterface $user) {
        $form = $this -> createForm(BoutonType::class) ;
        $form-> handleRequest($request);
        $conn = $manager->getConnection(); 
        $colonne = $conn->query("SELECT COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'traitement' 
                                and COLUMN_NAME not in ('id','type','date_creation','date_soumission','mise_a_jour','temps','rapporteur','nb_ticket_regroup','ref')
                                and COLUMN_NAME not like ('ticket%')")->fetchAll();

        if($form->isSubmitted()){
            $nomUtilisateur= $user->getPrenom();
            $date = new \DateTime();
            $date = $date->format('Y-m-d');
            //On insère la premiere ligne puis on update
            $conn = $manager->getConnection(); 
            $insertTraitement=$conn->query("INSERT INTO traitement (type) values ('".$nomUtilisateur."') ");
            $idTraitement = $conn->lastInsertId();
            foreach($colonne as $c){
                    $valeur = \str_replace("\"" , " " ,$_POST[$c['COLUMN_NAME']]);
                    $updateTraitement=$conn->query('UPDATE traitement set '.$c['COLUMN_NAME'].' = "'.$valeur.'" where id='.$idTraitement);
            }
            $updateMiseAJour = $conn->query('UPDATE traitement set mise_a_jour="'.$date.'" where id='.$idTraitement);
            $updateRef = $conn -> query('UPDATE traitement set ref = "'.$nomUtilisateur.$idTraitement.'"where id='.$idTraitement);
            $this->insertAffichage($manager);
            return $this->redirectToRoute('ticket');
        }

        return $this->render('ticket/ajoutTicket.html.twig',[
            'form' => $form->createView(),
            'colonne'=>$colonne
        ]);
    }

}
