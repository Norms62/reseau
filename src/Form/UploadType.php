<?php

namespace App\Form;

use App\Entity\Upload;
use App\Entity\Prestataire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', FileType::class , [
                'label' => 'Choisissez votre fichier'
            ])
            ->add('presta' , EntityType::class , [
                'label' => 'Prestataire',
                'class'=> Prestataire::class,
                'choice_label'=>'nom'
            ])
            ->add('Envoyer', SubmitType::class  , ['attr' => ['class' =>  'btn btn-info']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Upload::class,
        ]);
    }
}
