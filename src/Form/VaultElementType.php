<?php

namespace App\Form;

use App\Entity\VaultElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Beneficiary;
use App\Repository\BeneficiaryRepository;

class VaultElementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Titre de l'élément (ex: "Code coffre banque", "Wallet Crypto")
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            // Type de l'élément : menu déroulant avec les catégories définies
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Code' => 'CODE',
                    'Document' => 'DOCUMENT',
                    'Cryptomonnaie' => 'CRYPTO',
                    'Mot de passe' => 'MOT_DE_PASSE',
                ],
            ])
            // Contenu de l'élément : zone de texte large
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
            ])
            // Cocher si cet élément doit être transmis aux bénéficiaires
            ->add('isHeritage', CheckboxType::class, [
                'label' => 'Transmissible aux bénéficiaires',
                'required' => false,
            ])
            ->add('beneficiary', EntityType::class, [
                'class' => Beneficiary::class,
                'choice_label' => function (Beneficiary $b) {
                    return $b->getFirstname() . ' ' . $b->getLastname();
                },
                'label' => 'Bénéficiaire désigné',
                'required' => false,
                'placeholder' => '-- Aucun (élément privé) --',
                'query_builder' => function (BeneficiaryRepository $repo) use ($options) {
                    return $repo->createQueryBuilder('b')
                        ->where('b.createdBy = :user')
                        ->setParameter('user', $options['user']);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VaultElement::class,
            'user' => null,
        ]);
    }
}