<?php

namespace App\Form;

use App\Entity\Beneficiary;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BeneficiaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Nom du bénéficiaire
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
            ])
            // Prénom du bénéficiaire
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
            ])
            // Email pour les notifications (jour 90)
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])
            // Date de naissance (vérification identité par le Notaire)
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => false,
            ])
            // Lieu de naissance (vérification identité par le Notaire)
            ->add('birthPlace', TextType::class, [
                'label' => 'Lieu de naissance',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Beneficiary::class,
        ]);
    }
}