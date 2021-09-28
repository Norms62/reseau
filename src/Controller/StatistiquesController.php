<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use App\Form\BoutonType;
 
/**
 * @IsGranted("ROLE_USER")
 */
class StatistiquesController extends AbstractController
{
    /**
     * @Route("/statistique", name="statistique")
     */
    public function index( EntityManagerInterface $manager, Request $request): Response
    {
        // Formulaire des filtres
        $test = array();
        $form = $this -> createForm(BoutonType::class , $test);
        $form-> handleRequest($request);
        $date = date('Y');
        $tabDate=[];
        for ($i=$date; $i >=2017 ; $i--) { 
            $tabDate[] = $i;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $date=$_POST['date'];
        }

        $conn = $manager->getConnection();
        // Affichage tickets par prestataire selon résolution
        $selectDistinctResolution=$conn->query("SELECT distinct(resolution) from affichage where resolution != '' ")->fetchAll();
        $selectDistinctPrestataire = $conn->query("SELECT nom from prestataire")->fetchAll();
        $selectTicketParPresta = $conn->query("SELECT type , resolution ,  count(id) as nbTicket , sum(temps) as tempsTotal FROM affichage 
                                               where year(date_soumission)=$date group by type , resolution ")->fetchAll();
        foreach($selectDistinctPrestataire as $presta){
            foreach($selectDistinctResolution as $reso){
                $tabNbTicketPrestaResolution[$reso['resolution']][$presta['nom']]=0;
                $tabTempsTicketPrestaResolution[$reso['resolution']][$presta['nom']]=0;
            }
        }
        foreach($selectTicketParPresta as $ligne){
            $tabNbTicketPrestaResolution[$ligne['resolution']][$ligne['type']]=$ligne['nbTicket'];
            $tabTempsTicketPrestaResolution[$ligne['resolution']][$ligne['type']]=$ligne['tempsTotal'];
            if($ligne['resolution'] == '' ){
                $tabNbTicketPrestaResolution['résolu'][$ligne['type']]=$ligne['nbTicket'];
                $tabTempsTicketPrestaResolution['résolu'][$ligne['type']]=$ligne['tempsTotal'];
            }
        }
        // Nomnbre de ticket selon le mois
        $selectTicketParMois =$conn->query("SELECT count(id) as nbTicket ,sum(temps) as tempsTotal, month(date_soumission) as mois FROM `affichage` 
                                            where month(date_soumission) is not null and year(date_soumission)=$date group by month(date_soumission)")->fetchAll();
        for ($i=1; $i < 13 ; $i++) { 
            $tabNbTicket[$i]=0;
            $tabTempsTotal[$i]=0;
        }        
        foreach($selectTicketParMois as $unMois){
            $tabNbTicket[$unMois['mois']]=$unMois['nbTicket'];
            $tabTempsTotal[$unMois['mois']]=$unMois['tempsTotal'];
        }
        $selectMois = $conn->query(" SELECT month(now()) as 'mois'")->fetch();
        $mois=$selectMois['mois'];
        //Affichage des tickets du jour , la veille , semaine et mois
        $selectTicketDuJour = $conn->query("SELECT count(id) as nbTicket FROM affichage WHERE date_soumission = date(now())")->fetchAll();
        $selectTicketHier = $conn->query("SELECT count(id) as nbTicket FROM affichage WHERE date_soumission = date(now()- INTERVAL 1 DAY)")->fetchAll();
        $selectTicketSemaine = $conn->query("SELECT count(id) as nbTicket from affichage where week(date_soumission) = week(now())")->fetchAll();
        $selectTicketMois = $conn->query("SELECT count(id) as nbTicket from affichage where month(date_soumission) = month(now()) and year(date_soumission) = year(now())  ")->fetchAll();


        // Affichage du top 10 asso 
        $selectDistinctAsso = $conn->query("SELECT DISTINCT(nom_asso) , ANY_VALUE(id) as 'id' from associations where nom_asso is not null group by nom_asso order by nom_asso ")->fetchAll();
        foreach ($selectDistinctAsso as $s){
            $nom_asso = $s['nom_asso'];
            $selectNbTicketParAsso = $conn->query("SELECT nom , prenom from associations where nom_asso = \"$nom_asso\"");
            $cpTicket = 0;
            foreach($selectNbTicketParAsso as $a){
                $nomComplet = $a['nom'].' '.$a['prenom'];
                $nomComplet2 = $a['prenom'].' '.$a['nom'];
                $selectCountTicket = $conn->query("SELECT count(*)  as nbTicket from affichage where year(date_soumission)=$date and (rapporteur = \"$nomComplet\" or rapporteur = \"$nomComplet2\" ) ")->fetch();
                $cpTicket += $selectCountTicket['nbTicket'];
            }
            $tabNbTicketParAsso[$s['nom_asso']] =$cpTicket;
        }
        // Création d'un 2eme tableau pour récup le nom des assos
        $tabNbTicketParAsso2 = $tabNbTicketParAsso;
        arsort($tabNbTicketParAsso2);
        arsort($tabNbTicketParAsso);
        //dd($tabNbTicketParAsso);
        for ($i=0; $i < 10 ; $i++) { 
            $nomAsso[$i] = key($tabNbTicketParAsso2);
            unset($tabNbTicketParAsso2[$nomAsso[$i]]);
        }


        return $this->render('statistiques/stats.html.twig',[
            'form'=>$form->createView(),
            'presta'=>$selectDistinctPrestataire,
            'tabNbTicket' => $tabNbTicket,
            'tabTempsTotal' => $tabTempsTotal,
            'ticketParPresta'=>$selectTicketParPresta,
            'resolution'=>$selectDistinctResolution,
            'tabNbPrestaResolution' => $tabNbTicketPrestaResolution,
            'tabTempsPrestaResolution'=>$tabTempsTicketPrestaResolution,
            'ticketDuJour'=>$selectTicketDuJour,
            'ticketHier'=>$selectTicketHier,
            'ticketSemaine'=>$selectTicketSemaine,
            'ticketMois'=>$selectTicketMois,
            'tabDate'=>$tabDate,
            'date'=>$date,
            'mois'=>$mois,
            'nbTicket' => $tabNbTicketParAsso,
            'nomAsso'=>$nomAsso,
        ]);
    }
}
