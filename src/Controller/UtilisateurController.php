<?php

namespace App\Controller;

use App\Form\BoutonType;
use App\Form\SupprimerType;
use App\Entity\Utilisateurs;
use App\Form\InscriptionType;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

Class UtilisateurController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request , EntityManagerInterface $manager , UserPasswordEncoderInterface $encoder) {
        $user = new Utilisateurs();
        $form = $this->createForm(InscriptionType::class , $user);
        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $hash = $encoder->encodePassword($user, $user->getMdp());
            $user -> setDateCreation(new \DateTime);
            $user -> setMdp($hash);
            $user -> setNom($_POST['role']);
            $user -> setPrenom($_POST['nomComplet']);
            $manager -> persist($user);
            $manager-> flush();

            return $this->redirectToRoute('listeUtilisateurs');
            
        }

        return $this->render('utilisateur/inscription.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="connexion")
     */
    public function connexion() {

        return $this->render('utilisateur/connexion.html.twig');
    }

    /**
     * @Route("/deconnexion" , name="deconnexion")
     */
    public function deconnexion(){}

    /**
     * @Route("/listeUtilisateurs" , name="listeUtilisateurs")
     */
    public function listeUtilisateurs(EntityManagerInterface $manager, Request $request  ){
        $form = $this->createForm(SupprimerType::class);
        $form-> handleRequest($request);
        $conn = $manager->getConnection(); 
        $listeUser = $conn->query("SELECT * FROM utilisateurs")->fetchAll();

        if($form->isSubmitted() && $form->isValid()){
            if(isset($_POST['UserSupp'])){
                foreach($_POST['UserSupp'] as $c){
                    $conn = $manager->getConnection();
                    $dropUtilisateur = $conn->query('DELETE from utilisateurs where email="'.$c.'"');
                }
            }
            return $this->redirectToRoute('admin');
        }

        return $this->render('utilisateur/listeUtilisateur.html.twig',[
            'liste' => $listeUser,
            'form' => $form->createView()
        ]);
    }

}
