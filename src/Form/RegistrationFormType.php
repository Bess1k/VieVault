<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Construction du formulaire d'inscription
        // Chaque ->add() ajoute un champ au formulaire
        $builder
            ->add('lastname', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom'
            ])
            // Champ date de naissance : widget 'single_text' génère un input type="date" HTML5
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => true,
            ])

            ->add('birthPlace', TextType::class, [
                'label' => 'Lieu de naissance',
            ])
            
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail de connexion'
            ])


            // Case à cocher "J'accepte les conditions" 
            // 'mapped' => false : ce champ n'existe pas dans l'entité User,
            // il sert uniquement à la validation du formulaire
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'Vous devez accepter nos conditions.',
                    ),
                ],
            ])

            // Champ mot de passe en clair
            // 'mapped' => false : le mot de passe n'est pas stocké directement dans User,
            // il sera hashé par le controller avant d'être enregistré dans la base
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label'         => "Mot de passe",
                'constraints' => [
                    // Le mot de passe ne peut pas être vide
                    new NotBlank(
                        message: 'Veuillez entrer un mot de passe',
                    ),
                    // Longueur minimum 6 caractères, maximum 4096 (limite Symfony pour sécurité)
                    new Length(
                        min: 6,
                        minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        max: 4096,
                    ),
                ],
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
