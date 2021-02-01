<?php

namespace App\Form;

use App\Entity\Traitement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraitementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date_soumission')
            ->add('mise_a_jour')
            ->add('rapporteur')
            ->add('resume')
            ->add('description')
            ->add('temps')
            ->add('priorite')
            ->add('impact')
            ->add('etat')
            ->add('resolution')
            ->add('categorie')
            ->add('commentaire')
            ->add('action')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Traitement::class,
        ]);
    }
}
