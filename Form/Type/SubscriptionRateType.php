<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SubscriptionRateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('year', IntegerType::class, [
                'label' => 'Year',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Year is required.'
                    ]),
                    new Assert\Range([
                        'min' => 1900,
                        'max' => 2100,
                        'notInRangeMessage' => 'Year must be between {{ min }} and {{ max }}.'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1900,
                    'max' => 2100,
                    'step' => 1
                ]
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Amount (£)',
                'required' => true,
                'scale' => 2,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Amount is required.'
                    ]),
                    new Assert\Positive([
                        'message' => 'Amount must be greater than 0.'
                    ]),
                    new Assert\Range([
                        'min' => 0.01,
                        'max' => 9999.99,
                        'notInRangeMessage' => 'Amount must be between £{{ min }} and £{{ max }}.'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.01',
                    'min' => '0.01',
                    'max' => '9999.99'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 500,
                        'maxMessage' => 'Description cannot exceed {{ limit }} characters.'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'maxlength' => 500,
                    'placeholder' => 'Optional description for this subscription rate...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'subscription_rate'
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'lodge_subscription_rate';
    }
} 