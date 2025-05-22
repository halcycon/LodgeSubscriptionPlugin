<?php
namespace MauticPlugin\LodgeSubscriptionPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MauticPlugin\LodgeSubscriptionPlugin\Entity\SubscriptionRate;

class SubscriptionRateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber());

        $builder->add(
            'year',
            IntegerType::class,
            [
                'label' => 'Year',
                'attr' => [
                    'class' => 'form-control',
                    'min' => date('Y'),
                ],
                'required' => true,
            ]
        );

        $builder->add(
            'amount',
            NumberType::class,
            [
                'label' => 'Amount',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => 0.01,
                ],
                'scale' => 2,
                'required' => true,
            ]
        );

        $builder->add(
            'description',
            TextType::class,
            [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'buttons',
            \Mautic\CoreBundle\Form\Type\FormButtonsType::class
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => SubscriptionRate::class,
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'subscription_rate';
    }
} 