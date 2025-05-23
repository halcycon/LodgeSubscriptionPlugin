<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractFormController;
use MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate;
use MauticPlugin\LodgeSubscriptionBundle\Form\Type\SubscriptionRateType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

class RateController extends AbstractFormController
{
    protected SessionInterface $session;
    protected EntityManagerInterface $entityManager;
    
    public function __construct(
        SessionInterface $session,
        EntityManagerInterface $entityManager
    ) {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }
    
    /**
     * List all subscription rates
     */
    public function indexAction(Request $request, $page = 1): Response
    {
        $limit  = $this->session->get('mautic.lodge.subscription.rate.limit', 10);
        $start  = (1 === $page) ? 0 : (($page - 1) * $limit);
        $search = $request->get('search', $this->session->get('mautic.lodge.subscription.rate.search', ''));

        $this->session->set('mautic.lodge.subscription.rate.search', $search);
        $this->session->set('mautic.lodge.subscription.page', $page);

        $rates = $this->entityManager
            ->getRepository(SubscriptionRate::class)
            ->getRates($start, $limit);

        $count = $this->entityManager
            ->getRepository(SubscriptionRate::class)
            ->countRates();

        return $this->delegateView([
            'viewParameters' => [
                'items'       => $rates,
                'page'        => $page,
                'limit'       => $limit,
                'totalItems'  => $count,
                'searchValue' => $search,
            ],
            'contentTemplate' => '@LodgeSubscriptionBundle/SubscriptionRate/index.html.php',
            'pagetitle' => 'Subscription Rates'
        ]);
    }

    /**
     * Add new subscription rate
     */
    public function newAction(Request $request): Response
    {
        $rate = new SubscriptionRate();
        $form = $this->createForm(SubscriptionRateType::class, $rate);

        if ($request->isMethod('POST')) {
            if (!$this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    $this->entityManager->persist($rate);
                    $this->entityManager->flush();

                    $this->addFlash(
                        'mautic.core.notice.created',
                        [
                            '%name%'      => $rate->getYear(),
                            '%menu_link%' => 'mautic_subscription_rates',
                            '%url%'       => $this->generateUrl(
                                'mautic_subscription_rate_edit',
                                ['id' => $rate->getId()]
                            ),
                        ]
                    );

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        return $this->redirectToRoute('mautic_subscription_rates');
                    }

                    return $this->redirectToRoute('mautic_subscription_rate_edit', ['id' => $rate->getId()]);
                }
            } else {
                return $this->redirectToRoute('mautic_subscription_rates');
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $form->createView(),
                ],
                'contentTemplate' => '@LodgeSubscriptionBundle/SubscriptionRate/form.html.php',
                'passthroughVars' => [
                    'mauticContent' => 'subscriptionRate',
                    'activeLink'    => '#mautic_subscription_rates',
                    'route'         => $this->generateUrl('mautic_subscription_rate_new')
                ],
            ]
        );
    }

    /**
     * Edit subscription rate
     */
    public function editAction(Request $request, $id): Response
    {
        $rate = $this->entityManager->getRepository(SubscriptionRate::class)->find($id);

        if (!$rate) {
            return $this->notFound();
        }

        $form = $this->createForm(SubscriptionRateType::class, $rate);

        if ($request->isMethod('POST')) {
            if (!$this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    $rate->setDateModified(new \DateTime());
                    
                    $this->entityManager->persist($rate);
                    $this->entityManager->flush();

                    $this->addFlash(
                        'mautic.core.notice.updated',
                        [
                            '%name%'      => $rate->getYear(),
                            '%menu_link%' => 'mautic_subscription_rates',
                            '%url%'       => $this->generateUrl(
                                'mautic_subscription_rate_edit',
                                ['id' => $rate->getId()]
                            ),
                        ]
                    );

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        return $this->redirectToRoute('mautic_subscription_rates');
                    }

                    return $this->redirectToRoute('mautic_subscription_rate_edit', ['id' => $rate->getId()]);
                }
            } else {
                return $this->redirectToRoute('mautic_subscription_rates');
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $form->createView(),
                    'rate' => $rate,
                ],
                'contentTemplate' => '@LodgeSubscriptionBundle/SubscriptionRate/form.html.php',
                'passthroughVars' => [
                    'mauticContent' => 'subscriptionRate',
                    'activeLink'    => '#mautic_subscription_rates',
                    'route'         => $this->generateUrl('mautic_subscription_rate_edit', ['id' => $rate->getId()])
                ],
            ]
        );
    }

    /**
     * Delete subscription rate
     */
    public function deleteAction($id): Response
    {
        $rate = $this->entityManager->getRepository(SubscriptionRate::class)->find($id);

        if (!$rate) {
            return $this->notFound();
        }

        $year = $rate->getYear();

        $this->entityManager->remove($rate);
        $this->entityManager->flush();

        $this->addFlash(
            'mautic.core.notice.deleted',
            [
                '%name%' => $year,
                '%menu_link%' => 'mautic_subscription_rates',
            ]
        );

        return $this->redirectToRoute('mautic_subscription_rates');
    }

    /**
     * Get subscription rate for a specific year
     */
    public function getRateAction($year): JsonResponse
    {
        $rate = $this->entityManager->getRepository(SubscriptionRate::class)
            ->findOneBy(['year' => $year]);

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