<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FinanzenController extends AbstractController
{
    /**
     * @Route("/finanzen/spesen", name="spesen")
     */
    public function spesen(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('finanzen/spesen.html.twig', [
            'controller_name' => 'FinanzenController',
        ]);
    }

    /**
     * @Route("/finanzen/rechnungen", name="rechnungen")
     */
    public function rechnungen(): Response {
        if(!$this->isGranted('ROLE_FINANCE') && !$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SHOP')){ throw $this->createAccessDeniedException('not allowed'); }

        return $this->render('finanzen/index.html.twig', [
            'controller_name' => 'FinanzenController',
        ]);
    }
}
