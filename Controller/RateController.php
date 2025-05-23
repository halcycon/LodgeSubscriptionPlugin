<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RateController
{
    protected EntityManagerInterface $entityManager;
    
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }
    
    /**
     * List all subscription rates
     */
    public function indexAction(Request $request, $page = 1): Response
    {
        $session = $request->getSession();
        
        $limit  = $session->get('mautic.lodge.subscription.rate.limit', 10);
        $start  = (1 === $page) ? 0 : (($page - 1) * $limit);
        $search = $request->get('search', $session->get('mautic.lodge.subscription.rate.search', ''));

        $session->set('mautic.lodge.subscription.rate.search', $search);
        $session->set('mautic.lodge.subscription.page', $page);

        $rates = $this->entityManager
            ->getRepository(SubscriptionRate::class)
            ->getRates($start, $limit);

        $count = $this->entityManager
            ->getRepository(SubscriptionRate::class)
            ->countRates();

        $content = json_encode([
            'items'       => $rates,
            'page'        => $page,
            'limit'       => $limit,
            'totalItems'  => $count,
            'searchValue' => $search,
        ]);
        
        return new Response($content, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Add new subscription rate
     */
    public function newAction(Request $request): Response
    {
        $rate = new SubscriptionRate();
        
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            
            if (isset($data['year']) && isset($data['amount'])) {
                $rate->setYear($data['year']);
                $rate->setAmount($data['amount']);
                
                if (isset($data['description'])) {
                    $rate->setDescription($data['description']);
                }
                
                $this->entityManager->persist($rate);
                $this->entityManager->flush();

                return new JsonResponse([
                    'success' => true,
                    'message' => 'Rate created successfully',
                    'id' => $rate->getId()
                ]);
            }
            
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing required fields: year and amount'
            ], 400);
        }

        return new JsonResponse([
            'message' => 'Please submit rate data via POST'
        ]);
    }

    /**
     * Edit subscription rate
     */
    public function editAction(Request $request, $id): Response
    {
        $rate = $this->entityManager->getRepository(SubscriptionRate::class)->find($id);

        if (!$rate) {
            return new JsonResponse(['error' => 'Rate not found'], 404);
        }

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            
            if (isset($data['year'])) {
                $rate->setYear($data['year']);
            }
            if (isset($data['amount'])) {
                $rate->setAmount($data['amount']);
            }
            if (isset($data['description'])) {
                $rate->setDescription($data['description']);
            }
            
            $rate->setDateModified(new \DateTime());
            
            $this->entityManager->persist($rate);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Rate updated successfully'
            ]);
        }

        return new JsonResponse([
            'id' => $rate->getId(),
            'year' => $rate->getYear(),
            'amount' => $rate->getAmount(),
            'description' => $rate->getDescription()
        ]);
    }

    /**
     * Delete subscription rate
     */
    public function deleteAction($id): Response
    {
        $rate = $this->entityManager->getRepository(SubscriptionRate::class)->find($id);

        if (!$rate) {
            return new JsonResponse(['error' => 'Rate not found'], 404);
        }

        $year = $rate->getYear();

        $this->entityManager->remove($rate);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => "Rate for year {$year} deleted successfully"
        ]);
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