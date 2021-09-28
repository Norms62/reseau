<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\BoutonType;


/**
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/reseauadminentreprendre", name="admin")
     */
    public function index(EntityManagerInterface $manager): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/erreurDonnees", name="erreur_donnees")
     */
    public function erreurDonnees(EntityManagerInterface $manager): Response
    {
        $conn = $manager->getConnection();
        // Erreur sur le temps
        $selectDistinctTemps = $conn->query("SELECT temps,traitement_id as id from affichage where temps !=''")->fetchAll();
        $ErreurTemps[0]=0;
        $TempsTropLong[0] = 0;
        $cpErreurTemps=0;
        $cpTempsTropLong = 0 ; 
        foreach($selectDistinctTemps as $s){
            if(is_numeric($s['temps']) == false){
                $ErreurTemps[$cpErreurTemps] = $s['id'];
                $cpErreurTemps +=1;
            }
            if($s['temps']>1000){
                $TempsTropLong[$cpTempsTropLong] = $s['id'];
                $cpTempsTropLong +=1;
            }
        }


        // erreur sur la résolution
        $selectResolution = $conn->query("SELECT resolution , traitement_id as id from affichage where resolution != '' ")->fetchAll();
        $erreurResolution[0]=0;
        $cpErreurResolution =0;
        $cpResolutionTropLongue = 0;
        $ResolutionTropLongue[0]=0;
        foreach($selectResolution as $s) {
            if(is_numeric($s['resolution']) == true){
                $erreurResolution[$cpErreurResolution] = $s['id'];
                $cpErreurResolution +=1;
            } 
            if(\strlen($s['resolution']) > 20 ) {
                $ResolutionTropLongue[$cpResolutionTropLongue] =$s['id'];
                $cpResolutionTropLongue +=1;
            } 
        }

        // erreur sur les résumé
        $selectResume = $conn->query("SELECT resume , traitement_id as id from affichage where resume != '' ")->fetchAll();
        $erreurResume[0]=0;
        $cpErreurResume =0;
        foreach($selectResume as $s) {
            if(\strlen($s['resume']) > 500 ) {
                $erreurResume[$cpErreurResume] =$s['id'];
                $cpErreurResume +=1;
            } 
        }
        return $this->render('admin/erreur_donnees.html.twig',[
            'erreurTemps' => $ErreurTemps,
            'tempsTropLong' => $TempsTropLong,
            'erreurResolution' => $erreurResolution,
            'ResolutionTropLongue' => $ResolutionTropLongue,
            'erreurResume' => $erreurResume
        ]);
    }

    /**
     * @Route("/suppTicket" , name="supp_ticket")
     */
    public function suppTicket(EntityManagerInterface $manager, Request $request): Response
    {
        $form = $this -> createForm(BoutonType::class);
        $form-> handleRequest($request);

        $conn = $manager->getConnection();
        $selectAssigne = $conn->query("SELECT distinct(assigne) from affichage")->fetchAll();
        $selectDistinctResolution=$conn->query("SELECT distinct(resolution) from affichage where resolution != '' ")->fetchAll();
        $selectPresta = $conn->query("SELECT nom from prestataire")->fetchAll();

        if ($form->isSubmitted()) {
            
            $assigne = $_POST['assigne'];
            $resolution = $_POST['resolution'];
            $mois=$_POST['mois'];

            if($assigne != 'non' || $resolution != 'non' || $mois !='non'){
                $conn = $manager->getConnection();
                $deleteAffichage = "DELETE FROM affichage WHERE id !=''";
                $deleteTraitement = "DELETE from traitement where id != '' ";
                $deletePresta = "";
                if($assigne != 'non'){
                    $deleteAffichage = $deleteAffichage." and assigne='$assigne' ";
                    $deleteTraitement = $deleteTraitement." and assigne='$assigne' ";
                    $deletePresta = $deletePresta." and assigne='$assigne' ";
                }
                if($resolution != 'non'){
                    $deleteAffichage = $deleteAffichage." and resolution='$resolution' ";
                    $deleteTraitement = $deleteTraitement." and resolution='$resolution' ";
                    $deletePresta =$deletePresta." and resolution='$resolution' ";
                }
                if($mois != 'non'){
                    $deleteAffichage = $deleteAffichage." and mise_a_jour <= date(now()- INTERVAL ".$mois." MONTH) ";
                    $deleteTraitement = $deleteTraitement." and mise_a_jour <= date(now()- INTERVAL ".$mois." MONTH) ";
                    $deletePresta =$deletePresta." and mise_a_jour <= date(now()- INTERVAL ".$mois." MONTH) ";
                }
                $deleteAffichage= $conn->query($deleteAffichage);
                $deleteTraitement= $conn->query($deleteTraitement);
                foreach($selectPresta as $s){
                        $supPresta="DELETE from ".$s['nom']." where id != '' ".$deletePresta;
                        $supPresta= $conn->query($supPresta);
                }
            }

                return $this->redirectToRoute('ticket');
        }
    
        return $this->render('admin/supp_ticket.html.twig',[
            'presta'=>$selectPresta,
            'form'=>$form->createView(),
            'resolution' => $selectDistinctResolution,
            'assigne' => $selectAssigne
        ]);
    }

    /**
     * @Route("/listeAssociation" , name="liste_asso")
     */
    public function listeAsso(EntityManagerInterface $manager):Response
    {
        $conn = $manager->getConnection();
        $selectDistinctAsso = $conn->query("SELECT DISTINCT(nom_asso), region_adm,region_RE , id from associations where nom_asso is not null order by nom_asso ")->fetchAll();
   
        foreach ($selectDistinctAsso as $s){
            $nom_asso = $s['nom_asso'];
            $selectNbTicketParAsso = $conn->query("SELECT nom , prenom from associations where nom_asso = \"$nom_asso\"");
            $cpTicket = 0;
            foreach($selectNbTicketParAsso as $a){
                $nomComplet = $a['nom'].' '.$a['prenom'];
                $nomComplet2 = $a['prenom'].' '.$a['nom'];
                $selectCountTicket = $conn->query("SELECT count(*)  as nbTicket from affichage where rapporteur = \"$nomComplet\" or rapporteur = \"$nomComplet2\"")->fetch();
                $cpTicket += $selectCountTicket['nbTicket'];
            }
            $tabNbTicketParAsso[$s['id']] =$cpTicket;
        }

        $selectDistinctAsso = $conn->query("SELECT distinct(nom_asso) , ANY_VALUE(region_adm) as 'region_adm' ,ANY_VALUE(region_RE) as 'region_RE'  , ANY_VALUE(id) as 'id' from associations where nom_asso is not null group by nom_asso  order by nom_asso      ");

        return $this->render('admin/listeAsso.html.twig',[
            'listeAsso' =>$selectDistinctAsso,
            'nbTicket' => $tabNbTicketParAsso
        ]);
    }

    /**
     * @Route("/listeAsso/{nom_asso}" , name="liste_une_asso")
     */
    public function listeUneAsso($nom_asso,EntityManagerInterface $manager):Response
    {
        $conn = $manager->getConnection();
        $selectUneAsso = $conn->query("SELECT id ,nom,prenom,sexe,fonction from associations where nom_asso=\"$nom_asso\"")->fetchAll();
        foreach ($selectUneAsso as $s) {
            $nomComplet = $s['nom'].' '.$s['prenom'];
            $nomComplet2 = $s['prenom'].' '.$s['nom'];
            $selectCountTicket = $conn->query("SELECT rapporteur , count(*)  as nbTicket from affichage where rapporteur = \"$nomComplet\" or rapporteur = \"$nomComplet2\" group by rapporteur")->fetch();
            $NbTicketParPersonne[$s['id']] = $selectCountTicket['nbTicket'];
            $rapporteur[$s['id']] = $selectCountTicket['rapporteur'];
        }

        return $this->render('admin/listeUneAsso.html.twig',[
            'Asso' =>$selectUneAsso,
            'nom_asso'=>$nom_asso,
            'tabNbTicket' => $NbTicketParPersonne,
            'rapporteur' => $rapporteur
        ]); 
    }

    /**
     * @Route("/ListeRapporteur" , name="rapporteur_non_present")
     */
    public function listeRapporteurNonPresent(EntityManagerInterface $manager){
        $cp=0;
        $conn = $manager->getConnection();
        $selectNomPersonneAsso = $conn->query("SELECT id , nom,prenom from associations ")->fetchAll();
        $selectNomRapporteur = $conn->query("SELECT distinct(rapporteur) as 'rapporteur' from affichage")->fetchAll();
        foreach ($selectNomRapporteur as $r) {
            $presentAnnuaire = false;
            $nomRapporteurDebut = strtolower($r['rapporteur']);
            $nomRapporteur = str_replace(' ','',$nomRapporteurDebut);
            foreach ($selectNomPersonneAsso as $s){
                $nomComplet = $s['nom'].$s['prenom'];
                $nomComplet2 = $s['prenom'].$s['nom'];
                $nomComplet = strtolower($nomComplet);
                $nomComplet2 = strtolower($nomComplet2);
                $nomComplet2 = str_replace(' ','',$nomComplet2);
                $nomComplet = str_replace(' ','',$nomComplet);
                if($nomRapporteur== $nomComplet){
                    $presentAnnuaire = true;
                }
                elseif($nomRapporteur == $nomComplet2){
                    $presentAnnuaire = true;
                }
            }
            if($presentAnnuaire == false){
                $cp+=1;
                $tabRapporteur[$cp]=$nomRapporteurDebut;
            }
        }
        return $this->render('admin/rapporteur_non_present.html.twig',[
            'cp'=>$cp,
            'tab'=>$tabRapporteur
        ]);
    }

    /**
     * @Route("/ajoutRapporteur/{nomRapporteur}" , name="ajout_rapporteur")
     */
    public function ajoutRapporteur($nomRapporteur,EntityManagerInterface $manager,Request $request){
        $form = $this -> createForm(BoutonType::class);
        $form-> handleRequest($request);
        $conn = $manager->getConnection();

        $selectAsso = $conn->query("SELECT distinct(nom_asso) from associations")->fetchAll();

        if(isset($_POST["BtnAjouterGenerique"])) {
            $asso = $_POST['association'];
            $insert = $conn->query("INSERT INTO associations (prenom,nom_asso) values ('$nomRapporteur','$asso')");
            return $this->redirectToRoute('rapporteur_non_present');
        }
        
        if ($form->isSubmitted()) {
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $sexe = $_POST['sexe'];
            $fonction = $_POST['fonction'];
            $asso = $_POST['association'];
            $nomComplet = $prenom.' '.$nom;
            $insertAsso = $conn->query("INSERT into associations (nom,prenom,sexe,fonction,nom_asso) values (\"$nom\",\"$prenom\",\"$sexe\",\"$fonction\",\"$asso\")");
            $modifNomRapporteurAffichage = $conn->query("UPDATE affichage set rapporteur='$nomComplet' where rapporteur = '$nomRapporteur'");
            $modifNomRapporteurTraitement = $conn->query("UPDATE traitement set rapporteur='$nomComplet' where rapporteur = '$nomRapporteur'");
            $selectPresta = $conn->query("SELECT nom from prestataire")->fetchAll();
            foreach($selectPresta as $s){
                $nom = $s['nom'];
                $modifNomRapporteurPresta = $conn->query("UPDATE $nom set rapporteur='$nomComplet' where rapporteur = '$nomRapporteur'");
            }

            return $this->redirectToRoute('rapporteur_non_present');
        }
        return $this->render('admin/ajoutRapporteur.html.twig',[
            'nomRapporteur'=>$nomRapporteur,
            'form'=>$form->createView(),
            'association'=>$selectAsso
        ]);
    }

    /**
     * @Route("/modifPersonneAsso/{id}" , name="modif_personne_asso")
     */
    public function modifPersonneAsso($id,EntityManagerInterface $manager,Request $request){
        $form = $this -> createForm(BoutonType::class);
        $form-> handleRequest($request);
        $conn = $manager->getConnection();
        $selectNomAsso = $conn->query("SELECT nom_asso from associations where id=$id")->fetch();
        $selectPersonne = $conn->query("SELECT nom,prenom,sexe,fonction from associations where id=$id")->fetch();

        if(isset($_POST["btnSupp"])) {
            $conn = $manager->getConnection();
            $delete = $conn->query("DELETE from associations where id=$id");

            return $this->redirectToRoute('liste_une_asso' , array('nom_asso' => $selectNomAsso['nom_asso']));
        }

        if($form->isSubmitted()){
            $conn = $manager->getConnection();
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $fonction = $_POST['fonction'];
            $sexe = $_POST['sexe'];

            $update = $conn->query("UPDATE associations set nom='$nom' , prenom='$prenom' , sexe='$sexe',fonction='$fonction' where id=$id");
            
            return $this->redirectToRoute('liste_une_asso' , array('nom_asso' => $selectNomAsso['nom_asso']));
        }

        return $this->render('admin/modifPersonneAsso.html.twig',[
            'id'=>$id,
            'personne' => $selectPersonne,
            'form'=>$form->createView()
        ]);

    }

    /**
     * @Route("/lierRapporteur/{nomRapporteur}" , name="lier_rapporteur")
     */
    public function lierRapporteur($nomRapporteur,EntityManagerInterface $manager,Request $request){
        $form = $this -> createForm(BoutonType::class);
        $form-> handleRequest($request);
        $conn = $manager->getConnection();
        
        $selectNomPersonneAsso = $conn->query("SELECT nom,prenom from associations order by nom")->fetchAll();
        
        if($form->isSubmitted()){
            $conn = $manager->getConnection();
            $personne = $_POST['personne'];
            
            $modifNomRapporteurAffichage = $conn->query("UPDATE affichage set rapporteur='$personne' where rapporteur = '$nomRapporteur'");
            $modifNomRapporteurTraitement = $conn->query("UPDATE traitement set rapporteur='$personne' where rapporteur = '$nomRapporteur'");
            $selectPresta = $conn->query("SELECT nom from prestataire")->fetchAll();
            foreach($selectPresta as $s){
                $nom = $s['nom'];
                $modifNomRapporteurPresta = $conn->query("UPDATE $nom set rapporteur='$personne' where rapporteur = '$nomRapporteur'");
            }

            return $this->redirectToRoute('rapporteur_non_present');
        }

        return $this->render('admin/lierRapporteur.html.twig',[
            'nomRapporteur'=>$nomRapporteur,
            'form'=>$form->createView(),
            'personne'=>$selectNomPersonneAsso
        ]);

    }

    /**
     * @Route("/modifAsso/{asso}" , name="modif_asso")
     */
    public function modifAsso($asso,EntityManagerInterface $manager,Request $request){
        $form = $this -> createForm(BoutonType::class);
        $form-> handleRequest($request);
        $conn = $manager->getConnection();

        $selectInfoAsso = $conn->query("SELECT distinct(nom_asso),ANY_VALUE(region_adm) as 'region_adm' ,ANY_VALUE(region_RE) as 'region_RE' from associations where nom_asso = \"$asso\" group by nom_asso")->fetch();

        if(isset($_POST["btnSupp"])) {
            $conn = $manager->getConnection();
            $delete = $conn->query("DELETE from associations where nom_asso = \"$asso\" ");

            return $this->redirectToRoute('liste_asso');
        }

        if($form->isSubmitted()){
            $conn = $manager->getConnection();
            $nom_asso = $_POST['nom_asso'];
            $pays = $_POST['pays'];
            $region = $_POST['region'];
            
            $modifAsso = $conn->query("UPDATE associations set region_adm = \"$pays\" , region_RE = \"$region\" , nom_asso = \"$nom_asso\" where nom_asso = \"$asso\" ");
            
            return $this->redirectToRoute('liste_asso');
        }

        return $this->render('admin/modifAsso.html.twig',[
            'asso'=>$selectInfoAsso,
            'form'=>$form->createView(),
        ]);
    }

    /**
     * @Route("/ajoutAsso" , name="ajout_asso")
     */
    public function ajoutAsso(EntityManagerInterface $manager,Request $request){
        $form = $this -> createForm(BoutonType::class) ;
        $form-> handleRequest($request);
        $conn = $manager->getConnection(); 

        if($form->isSubmitted()){
            $conn = $manager->getConnection();
            $nom_asso = $_POST['nom_asso'];
            $pays = $_POST['pays'];
            $region = $_POST['region'];
            
            $insertAsso = $conn->query("INSERT into associations (nom_asso,region_adm,region_RE) values (\"$nom_asso\",\"$pays\",\"$region\") ");
            
            return $this->redirectToRoute('liste_asso');
        }

        return $this->render('admin/ajoutAsso.html.twig',[
            'form' => $form->createView(),
        ]);
    }
}