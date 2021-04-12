<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\BoutonType;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function index(EntityManagerInterface $manager, Request $request , \Swift_Mailer $mailer): Response
    {
        $form = $this -> createForm(BoutonType::class);
        $form-> handleRequest($request);

        if ($form->isSubmitted()) {
            $email = $_POST['email'];
            $nom = $_POST['nom'];
            $sujet = $_POST['sujet'];
            $message = $_POST['message'];

            // méthode symfony 
            // On crée le message
            /*$envoiEmail = (new \Swift_Message('Nouveau contact'))
                // On attribue l'expéditeur
                ->setFrom($email)
                // On attribue le destinataire
                ->setTo('pierre.normand62138@gmail.com')
                // On crée le texte avec la vue
                ->setBody($this->renderView(
                    'contact/contact.html.twig',['form'=>$form->createView()]),
                'text/html')
            ;
            $mailer->send($envoiEmail);*/

            //méthode php
            //mail('pierre.normand62138@gmail.com',$sujet,$message);
        }

        return $this->render('contact/contact.html.twig', [
            'form'=>$form->createView()
            ,
        ]);
    }
}
