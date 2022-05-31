<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\GlobaleVariabeln;

class KghController extends AbstractController {
    /**
     * @Route("/kgh", name="kgh")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $url = $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'KGH_URL'))->getV();

        $loginData = array(
            'user' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'KGH_USER'))->getV(),
            'pw' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'KGH_PASSWORD'))->getV()
        );

        return $this->render('kgh/index.html.twig', [
            'loginData' => $loginData,
            'url' => $url,
        ]);
    }
}