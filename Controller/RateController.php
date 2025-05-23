<?php
namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractFormController;
use Mautic\CoreBundle\Factory\PageHelperFactoryInterface;
use MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate;
use MauticPlugin\LodgeSubscriptionBundle\Form\Type\SubscriptionRateType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RateController extends AbstractFormController
{
    /**
     * List all subscription rates
     */
    public function indexAction($page = 1)
    {
        $session = $this->get('session');

        $limit  = $session->get('mautic.lodge.subscription.rate.limit', 10);
        $start  = (1 === $page) ? 0 : (($page - 1) * $limit);
        $search = $this->request->get('search', $session->get('mautic.lodge.subscription.rate.search', ''));

        $session->set('mautic.lodge.subscription.rate.search', $search);
        $session->set('mautic.lodge.subscription.page', $page);

        $rates = $this->getDoctrine()
            ->getRepository(SubscriptionRate::class)
            ->getRates($start, $limit);

        $count = $this->getDoctrine()
            ->getRepository(SubscriptionRate::class)
            ->countRates();

        $view = $this->renderView(
            'LodgeSubscriptionPlugin:SubscriptionRate:index.html.php',
            [
                'items'       => $rates,
                'page'        => $page,
                'limit'       => $limit,
                'totalItems'  => $count,
                'searchValue' => $search,
            ]
        );

        return new Response($view);
    }

    /**
     * Add new subscription rate
     */
    public function newAction()
    {
        $rate = new SubscriptionRate();
        $form = $this->createForm(SubscriptionRateType::class, $rate);

        if ($this->request->isMethod('POST')) {
            if (!$this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($rate);
                    $em->flush();

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
                'contentTemplate' => 'LodgeSubscriptionPlugin:SubscriptionRate:form.html.php',
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
    public function editAction($id)
    {
        $rate = $this->getDoctrine()->getRepository(SubscriptionRate::class)->find($id);

        if (!$rate) {
            return $this->notFound();
        }

        $form = $this->createForm(SubscriptionRateType::class, $rate);

        if ($this->request->isMethod('POST')) {
            if (!$this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    $rate->setDateModified(new \DateTime());
                    
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($rate);
                    $em->flush();

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
                'contentTemplate' => 'LodgeSubscriptionPlugin:SubscriptionRate:form.html.php',
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
    public function deleteAction($id)
    {
        $rate = $this->getDoctrine()->getRepository(SubscriptionRate::class)->find($id);

        if (!$rate) {
            return $this->notFound();
        }

        $year = $rate->getYear();

        $em = $this->getDoctrine()->getManager();
        $em->remove($rate);
        $em->flush();

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
    public function getRateAction($year)
    {
        $rate = $this->getDoctrine()->getRepository(SubscriptionRate::class)
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