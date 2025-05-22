<?php
namespace MauticPlugin\LodgeSubscriptionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'contactId',
            HiddenType::class
        );

        $builder->add(
            'year',
            HiddenType::class
        );

        $builder->add(
            'amount',
            NumberType::class,
            [
                'label' => 'Payment Amount',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => 0.01
                ],
                'required' => true,
                'scale' => 2
            ]
        );

        $builder->add(
            'paymentMethod',
            ChoiceType::class,
            [
                'label' => 'Payment Method',
                'choices' => [
                    'Cash' => 'cash',
                    'Cheque' => 'cheque',
                    'Bank Transfer' => 'bank_transfer',
                    'Other' => 'other'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true
            ]
        );

        $builder->add(
            'notes',
            TextareaType::class,
            [
                'label' => 'Notes',
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ]
        );

        $builder->add(
            'buttons',
            \Mautic\CoreBundle\Form\Type\FormButtonsType::class,
            [
                'apply_text' => false,
                'save_text' => 'Record Payment'
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'payment';
    }
} 