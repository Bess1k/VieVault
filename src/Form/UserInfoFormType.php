<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;

// Formulaire de modification des informations de l'utilisateur
// Utilisé pour l'édition du profil (mot de passe facultatif)
class UserInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('birthPlace', TextType::class, [
                'label' => 'Lieu de naissance',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail de connexion',
            ])
            // Mot de passe avec confirmation — facultatif en modification
            ->add('plainPassword', RepeatedType::class, [
                'mapped'            => false,
                'type'              => PasswordType::class,
                'invalid_message'   => 'Les champs mot de passe doivent être identiques',
                'required'          => false,
                'first_options'     => ['label' => 'Nouveau mot de passe (facultatif)'],
                'second_options'    => ['label' => 'Confirmer le nouveau mot de passe'],
                'attr'              => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length(
                        min: 12,
                        minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        max: 4096,
                    ),
                    new PasswordStrength(),
                    new NotCompromisedPassword(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}