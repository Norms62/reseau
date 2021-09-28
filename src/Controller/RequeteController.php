<?php

namespace App\Controller;

use App\Form\BoutonType;
use App\Form\NewBaseType;
use App\Form\SupprimerType;
use App\Controller\EntityManager;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class RequeteController extends AbstractController
{
    /**
     * @Route("/creerBase", name="newBase")
     */
    public function creerBase(Request $request, EntityManagerInterface $manager) {
        // Formulaire qui demande juste le nom de la new base
        $form = $this->createForm(NewBaseType::class);
        $form -> handleRequest($request);
        // Toutes les colonnes d'un presta
        $conn = $manager->getConnection();
        $colonne = $conn->query("SELECT COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'traitement' 
                                and COLUMN_NAME not in ('id','type','date_creation','date_soumission','mise_a_jour','nb_ticket_regroup','ref','action', 'commentaire')
                                and COLUMN_NAME not like ('ticket%')")->fetchAll();
        // Une fois validé, on créer cette base
        if($form->isSubmitted()){
            $data = $form->getData();
            $nomBase = $data['nomBase'];
            $newBase= $conn->query('CREATE TABLE '.$nomBase.'
            (
                id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
                date_creation VARCHAR(255),
                ref VARCHAR(255),
                date_soumission VARCHAR(255),
                mise_a_jour VARCHAR(255)
            )ENGINE = InnoDB');
            foreach($colonne as $c){
                $ajoutColonne = $conn->query("ALTER TABLE $nomBase ADD ".$c['COLUMN_NAME']. " varchar(255)");
            }
            // Ajout des clés étrangères dans traitement et tickets_regroup
            $newColonne = $conn ->query(
                'ALTER TABLE tickets_regroup ADD '.$nomBase.'_id INT ');
            $newColonne2 = $conn ->query(
                'ALTER TABLE traitement ADD ticket_'.$nomBase.' INT ');
            $cleEtrangere = $conn ->query(
                'ALTER TABLE traitement ADD CONSTRAINT FK_traitement_'.$nomBase.' FOREIGN KEY (ticket_'.$nomBase.') REFERENCES '.$nomBase.' (id) ');
            $index = $conn -> query(
                'CREATE UNIQUE INDEX UNIQ_traitement_'.$nomBase.' ON traitement (ticket_'.$nomBase.')');
            $cleEtrangere2 = $conn ->query(
                'ALTER TABLE tickets_regroup ADD CONSTRAINT FK_tickets_'.$nomBase.' FOREIGN KEY ('.$nomBase.'_id) REFERENCES '.$nomBase.' (id)');
            $index = $conn -> query(
                'CREATE UNIQUE INDEX UNIQ_tickets_'.$nomBase.' ON tickets_regroup ('.$nomBase.'_id)');
            $insertPresta = $conn -> query(
                "INSERT INTO prestataire(nom) values ('$nomBase')");

                return $this->redirectToRoute('admin');

        }

        return $this->render('requete/index.html.twig', [
            'controller_name' => 'RequeteController',
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/listeBase", name="listeBase")
     */

    public function listeBase(Request $request, EntityManagerInterface $manager) {
        // Affichage de toute les bases. Date_création permet de voir juste les tables du site
        $conn = $manager->getConnection();
        $select= $conn->query("SELECT nom as nomTable from prestataire ")->fetchAll();

        return $this->render('requete/listeBase.html.twig', [
            'controller_name' => 'RequeteController',
            'tables' => $select
        ]);
    }

    /**
     * @Route("/listeBase/{name}", name="listeUneBase")
     */
    public function listeUneBase($name , Request $request, EntityManagerInterface $manager ){
        //Affichage d'une base 
        $conn = $manager->getConnection();
        $select= $conn->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS as nomColonne WHERE TABLE_NAME = '$name' ")->fetchAll();

        $form = $this->createForm(SupprimerType::class);
        $form -> handleRequest($request);
        // Si on appuie sur le bouton supprimer, on supprime ce prestataire avec toutes ces clés étrangères
        if($form->isSubmitted()){
            $conn = $manager->getConnection();
            $suppCleEtrangere = $conn->query('ALTER TABLE traitement DROP FOREIGN KEY FK_traitement_'.$name.'');
            $suppIndex = $conn->query('ALTER TABLE traitement DROP INDEX UNIQ_traitement_'.$name.' '); 
            $suppColonne = $conn -> query('ALTER TABLE traitement DROP ticket_'.$name.'');
            $suppCleEtrangere2 = $conn->query('ALTER TABLE tickets_regroup DROP FOREIGN KEY FK_tickets_'.$name.'');
            $suppIndex2 = $conn->query('ALTER TABLE tickets_regroup DROP INDEX UNIQ_tickets_'.$name.' '); 
            $suppColonne2 = $conn -> query('ALTER TABLE tickets_regroup DROP '.$name.'_id');
            $suppBase= $conn->query('DROP TABLE '.$name.'');
            $suppPresta= $conn->query("DELETE FROM prestataire WHERE nom='$name'");

            return $this->redirectToRoute('admin');

            
        }
        return $this->render('requete/listeUneBase.html.twig', [
            'controller_name' => 'RequeteController',
            'colonnes' => $select,
            'form' => $form->createView(),
            'nomBase' => $name
        ]);
    }  

    /**
     * @Route("/ajoutColonne", name="ajoutColonne")
     */
    public function ajoutColonne(Request $request, EntityManagerInterface $manager){
        $test = array();
        $form = $this->createForm(BoutonType::class,$test);
        $form-> handleRequest($request);
        // Si formulaire validé, ajout de la colonne dans tout les prestas, traitement et affichage
        if($form->isSubmitted() && $form->isValid()){
            $conn = $manager->getConnection();
            $listePresta = $conn->query("SELECT * FROM prestataire")->fetchAll();
            foreach ($listePresta as $unPresta){
                $ajoutColonnePresta = $conn->query('ALTER TABLE '.$unPresta['nom'].' ADD '.$_POST['colonne'].' varchar(255)');
            }
            $ajoutColonneTraitement = $conn->query('ALTER TABLE traitement ADD '.$_POST['colonne'].' varchar(255)');
            $ajoutColonneAffichage = $conn->query('ALTER TABLE affichage ADD '.$_POST['colonne'].' varchar(255)');

            return $this->redirectToRoute('admin');
        }


        return $this->render('requete/ajoutColonne.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/suppColonne", name="suppColonne")
     */
    public function suppColonne(Request $request, EntityManagerInterface $manager){
        $test = array();
        $form = $this->createForm(BoutonType::class,$test);
        $form-> handleRequest($request);
        // affichage des colonnes
        $conn = $manager->getConnection();
        $colonne= $conn->query("SELECT COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'traitement' 
                                and COLUMN_NAME not in ('id','date_creation','nb_ticket_regroup','type','ref','date_soumission','mise_a_jour')
                                and COLUMN_NAME not like ('ticket%')")->fetchAll();
        // si c'est validé, on parcourt les colonnes cochées et on supprime dans tous les prestas , traitement et affichage
        if($form->isSubmitted() && $form->isValid()){
            if(isset($_POST['colonneASupp'])){
                foreach($_POST['colonneASupp'] as $c){
                    $conn = $manager->getConnection();
                    $listePresta = $conn->query("SELECT * FROM prestataire")->fetchAll();
                    foreach ($listePresta as $unPresta){
                        $dropColonePresta = $conn->query('ALTER TABLE '.$unPresta['nom'].' DROP COLUMN '.$c);
                    }
                    $dropColonneTraitement = $conn->query('ALTER TABLE traitement DROP COLUMN '.$c);
                    $dropColonneAffichage = $conn->query('ALTER TABLE affichage DROP COLUMN '.$c); 
                }
            }
            return $this->redirectToRoute('admin');
        }
        return $this->render('requete/suppColonne.html.twig', [
            'form' => $form->createView(),
            'colonne' => $colonne
        ]);
    }








    }
