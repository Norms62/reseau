<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class StatistiquesController extends AbstractController
{
    /**
     * @Route("/statistiques", name="statistiques")
     */
    public function index( EntityManagerInterface $manager): Response
    {
        $conn = $manager->getConnection();
        // Affichage tickets par prestataire selon résolution
        $selectDistinctResolution=$conn->query("SELECT distinct(resolution) from affichage where resolution != '' ")->fetchAll();
        $selectDistinctPrestataire = $conn->query("SELECT nom from prestataire")->fetchAll();
        $selectTicketParPresta = $conn->query("SELECT type , resolution ,  count(id) as nbTicket , sum(temps) as tempsTotal FROM affichage 
                                               group by type , resolution")->fetchAll();
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
        $selectTicketParMois =$conn->query("SELECT count(id) as nbTicket ,sum(temps) as tempsTotal, month(mise_a_jour) as mois FROM `affichage` 
                                            where month(mise_a_jour) is not null group by month(mise_a_jour)")->fetchAll();
        for ($i=1; $i < 13 ; $i++) { 
            $tabNbTicket[$i]=0;
            $tabTempsTotal[$i]=0;
        }        
        foreach($selectTicketParMois as $unMois){
            $tabNbTicket[$unMois['mois']]=$unMois['nbTicket'];
            $tabTempsTotal[$unMois['mois']]=$unMois['tempsTotal'];
        }

        //Affichage des tickets du jour , la veille , semaine et mois
        $selectTicketDuJour = $conn->query("SELECT count(id) as nbTicket FROM affichage WHERE mise_a_jour = date(now())")->fetchAll();
        $selectTicketHier = $conn->query("SELECT count(id) as nbTicket FROM affichage WHERE mise_a_jour = date(now()- INTERVAL 1 DAY)")->fetchAll();
        $selectTicketSemaine = $conn->query("SELECT count(id) as nbTicket from affichage where week(mise_a_jour) = week(now())")->fetchAll();
        $selectTicketMois = $conn->query("SELECT count(id) as nbTicket from affichage where month(mise_a_jour) = month(now())")->fetchAll();

        return $this->render('statistiques/stats.html.twig',[
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
            'ticketMois'=>$selectTicketMois
        ]);
    }
}
