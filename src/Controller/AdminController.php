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
        $selectDistinctTemps = $conn->query("SELECT temps, id from affichage where temps !=''")->fetchAll();
        $ErreurTemps[0]=0;
        $TempsTropLong[0] = 0;
        $cpErreurTemps=0;
        $cpTempsTropLong = 0 ; 
        foreach($selectDistinctTemps as $s){
            if(is_numeric($s['temps']) == false){
                $ErreurTemps[$cpErreurTemps] = $s['id'];
                $cpErreurTemps +=1;
            }
            if($s['temps']>60){
                $TempsTropLong[$cpTempsTropLong] = $s['id'];
                $cpTempsTropLong +=1;
            }
        }

        // erreur sur la priorité
        $selectDistinctPriorite = $conn->query("SELECT priorite , id  from affichage where priorite!= '' ")->fetchAll();
        $erreurPriorite[0] = 0 ;
        $PrioriteTropLongue[0] = 0; 
        $cpErreurPriorite = 0;
        $cpPrioriteTropLongue = 0 ; 
        foreach($selectDistinctPriorite as $s){
            if(is_numeric($s['priorite']) == true){
                $erreurPriorite[$cpErreurPriorite] = $s['id'];
                $cpErreurPriorite +=1;
            }
            if(\strlen($s['priorite']) > 20 ) {
                $PrioriteTropLongue[$cpPrioriteTropLongue] = $s['id'];
                $cpPrioriteTropLongue +=1;
            } 
        }

        // erreur sur l'impact
        $selectDistinctImpact = $conn->query("SELECT impact , id from affichage where impact!= '' ")->fetchAll();
        $erreurImpact[0] = 0 ;
        $impactTropLong[0] = 0; 
        $cpErreurImpact = 0;
        $cpImpactTropLong = 0;
        foreach($selectDistinctImpact as $s){
            if(is_numeric($s['impact']) == true){
                $erreurImpact[$cpErreurImpact] = $s['id'];
                $cpErreurImpact +=1;
            }
            if(\strlen($s['impact']) > 20 ) {
                $impactTropLong[$cpImpactTropLong] =$s['id'];
                $cpImpactTropLong +=1;
            } 
        }

        // erreur sur la résolution
        $selectResolution = $conn->query("SELECT resolution , id from affichage where resolution != '' ")->fetchAll();
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

        // erreur sur les états
        $selectEtat = $conn->query("SELECT etat , id from affichage where etat != '' ")->fetchAll();
        $erreurEtat[0]=0;
        $cpErreurEtat =0;
        $EtatTropLong[0] = 0;
        $cpEtatTropLong=0;
        foreach($selectEtat as $s) {
            if(is_numeric($s['etat']) == true){
                $erreurEtat[$cpErreurEtat] = $s['id'];
                $cpErreurEtat +=1;
            } 
            if(\strlen($s['etat']) > 20 ) {
                $EtatTropLong[$cpEtatTropLong] =$s['id'];
                $cpEtatTropLong +=1;
            } 
        }

        // erreur sur les résumé
        $selectResume = $conn->query("SELECT resume , id from affichage where resume != '' ")->fetchAll();
        $erreurResume[0]=0;
        $cpErreurResume =0;
        foreach($selectResume as $s) {
            if(\strlen($s['resume']) > 100 ) {
                $erreurResume[$cpErreurResume] =$s['id'];
                $cpErreurResume +=1;
            } 
        }
        return $this->render('admin/erreur_donnees.html.twig',[
            'erreurTemps' => $ErreurTemps,
            'tempsTropLong' => $TempsTropLong,
            'erreurPriorite' => $erreurPriorite,
            'prioriteTropLongue' => $PrioriteTropLongue, 
            'erreurImpact'=>$erreurImpact,
            'impactTropLong' => $impactTropLong,
            'erreurResolution' => $erreurResolution,
            'ResolutionTropLongue' => $ResolutionTropLongue,
            'EtatTropLong' => $EtatTropLong,
            'erreurEtat' => $erreurEtat,
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
        $selectPresta = $conn->query("SELECT nom from prestataire")->fetchAll();
        $selectDistinctResolution=$conn->query("SELECT distinct(resolution) from affichage where resolution != '' ")->fetchAll();

        if ($form->isSubmitted()) {
            $presta = $_POST['nomPresta'];
            $resolution = $_POST['resolution'];
            $mois=$_POST['mois'];

            $conn = $manager->getConnection();
            $deleteAffichage = "DELETE FROM affichage WHERE id !=''";
            $deleteTraitement = "DELETE from traitement where id != '' ";
            $deletePresta = "";
            if($presta != 'non'){
                $deleteAffichage = $deleteAffichage." and type='$presta' ";
                $deleteTraitement = $deleteTraitement." and type='$presta' ";
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
            if($presta!='non'){
                $deletePresta= $conn->query("DELETE from $presta where id != '' ".$deletePresta);
            }
            else{
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
        ]);
    }
}