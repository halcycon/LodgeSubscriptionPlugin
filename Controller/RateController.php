<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\CoreBundle\Controller\CommonController;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\CoreBundle\Service\FlashBag;
use MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate;
use MauticPlugin\LodgeSubscriptionBundle\Form\Type\SubscriptionRateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class RateController extends CommonController
{
    protected EntityManagerInterface $entityManager;
    protected FormFactoryInterface $formFactory;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        MauticFactory $factory,
        CoreParametersHelper $coreParametersHelper,
        CorePermissions $security,
        FlashBag $flashBag
    ) {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        
        // Call parent constructor for Mautic templating support
        parent::__construct($factory, $coreParametersHelper, $security, $flashBag);
    }

    /**
     * List all subscription rates
     */
    public function indexAction(Request $request, $page = 1): Response
    {
        // Check permissions
        if (!$this->security->isGranted('lodge:subscriptions:view')) {
            return $this->accessDenied();
        }

        $repository = $this->entityManager->getRepository(SubscriptionRate::class);
        
        // Get all rates ordered by year desc
        $rates = $repository->findBy([], ['year' => 'DESC']);
        
        return $this->delegateView([
            'viewParameters' => [
                'rates' => $rates,
                'permissions' => [
                    'view' => $this->security->isGranted('lodge:subscriptions:view'),
                    'create' => $this->security->isGranted('lodge:subscriptions:create'),
                    'edit' => $this->security->isGranted('lodge:subscriptions:edit'),
                    'delete' => $this->security->isGranted('lodge:subscriptions:delete'),
                ]
            ],
            'contentTemplate' => 'LodgeSubscriptionBundle:SubscriptionRate:index.html.php',
            'passthroughVars' => [
                'activeLink' => '#mautic_subscription_rates',
                'mauticContent' => 'lodge_subscription_rates',
                'route' => $this->generateUrl('mautic_subscription_rates')
            ]
        ]);
    }

    /**
     * Create new subscription rate
     */
    public function newAction(Request $request): Response
    {
        // Check permissions
        if (!$this->security->isGranted('lodge:subscriptions:create')) {
            return $this->accessDenied();
        }

        $rate = new SubscriptionRate();
        $rate->setDateAdded(new \DateTime());
        
        $form = $this->formFactory->create(SubscriptionRateType::class, $rate);
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                try {
                    $this->entityManager->persist($rate);
                    $this->entityManager->flush();
                    
                    $this->addFlash('mautic.core.notice.created', 'Subscription rate created successfully.');
                    
                    return $this->redirectToRoute('mautic_subscription_rates');
                } catch (\Exception $e) {
                    $this->addFlash('mautic.core.error.generic', 'Error creating subscription rate: ' . $e->getMessage());
                }
            }
        }
        
        return $this->delegateView([
            'viewParameters' => [
                'form' => $form->createView(),
                'rate' => $rate,
                'action' => 'new'
            ],
            'contentTemplate' => 'LodgeSubscriptionBundle:SubscriptionRate:form.html.php',
            'passthroughVars' => [
                'activeLink' => '#mautic_subscription_rates',
                'mauticContent' => 'lodge_subscription_rate_new',
                'route' => $this->generateUrl('mautic_subscription_rate_new')
            ]
        ]);
    }

    /**
     * Edit existing subscription rate
     */
    public function editAction(Request $request, $id): Response
    {
        // Check permissions
        if (!$this->security->isGranted('lodge:subscriptions:edit')) {
            return $this->accessDenied();
        }

        $repository = $this->entityManager->getRepository(SubscriptionRate::class);
        $rate = $repository->find($id);
        
        if (!$rate) {
            throw $this->createNotFoundException('Subscription rate not found');
        }
        
        $form = $this->formFactory->create(SubscriptionRateType::class, $rate);
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                try {
                    $rate->setDateModified(new \DateTime());
                    $this->entityManager->flush();
                    
                    $this->addFlash('mautic.core.notice.updated', 'Subscription rate updated successfully.');
                    
                    return $this->redirectToRoute('mautic_subscription_rates');
                } catch (\Exception $e) {
                    $this->addFlash('mautic.core.error.generic', 'Error updating subscription rate: ' . $e->getMessage());
                }
            }
        }
        
        return $this->delegateView([
            'viewParameters' => [
                'form' => $form->createView(),
                'rate' => $rate,
                'action' => 'edit'
            ],
            'contentTemplate' => 'LodgeSubscriptionBundle:SubscriptionRate:form.html.php',
            'passthroughVars' => [
                'activeLink' => '#mautic_subscription_rates',
                'mauticContent' => 'lodge_subscription_rate_edit',
                'route' => $this->generateUrl('mautic_subscription_rate_edit', ['id' => $id])
            ]
        ]);
    }

    /**
     * Delete subscription rate
     */
    public function deleteAction(Request $request, $id): Response
    {
        // Check permissions
        if (!$this->security->isGranted('lodge:subscriptions:delete')) {
            return $this->accessDenied();
        }

        $repository = $this->entityManager->getRepository(SubscriptionRate::class);
        $rate = $repository->find($id);
        
        if (!$rate) {
            throw $this->createNotFoundException('Subscription rate not found');
        }
        
        if ($request->isMethod('POST')) {
            try {
                $this->entityManager->remove($rate);
                $this->entityManager->flush();
                
                $this->addFlash('mautic.core.notice.deleted', 'Subscription rate deleted successfully.');
            } catch (\Exception $e) {
                $this->addFlash('mautic.core.error.generic', 'Error deleting subscription rate: ' . $e->getMessage());
            }
            
            return $this->redirectToRoute('mautic_subscription_rates');
        }
        
        return $this->delegateView([
            'viewParameters' => [
                'rate' => $rate
            ],
            'contentTemplate' => 'LodgeSubscriptionBundle:SubscriptionRate:delete.html.php',
            'passthroughVars' => [
                'activeLink' => '#mautic_subscription_rates',
                'mauticContent' => 'lodge_subscription_rate_delete',
                'route' => $this->generateUrl('mautic_subscription_rate_delete', ['id' => $id])
            ]
        ]);
    }

    /**
     * Get subscription rate for a specific year (API endpoint)
     */
    public function getRateAction(Request $request, $year): Response
    {
        // Check permissions
        if (!$this->security->isGranted('lodge:subscriptions:view')) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $repository = $this->entityManager->getRepository(SubscriptionRate::class);
        $rate = $repository->findOneBy(['year' => (int) $year]);
        
        if (!$rate) {
            return new JsonResponse(['error' => 'Rate not found for year ' . $year], 404);
        }
        
        return new JsonResponse([
            'id' => $rate->getId(),
            'year' => $rate->getYear(),
            'amount' => $rate->getAmount(),
            'description' => $rate->getDescription()
        ]);
    }
}