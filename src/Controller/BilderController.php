<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\GlobaleVariabeln;

class BilderController extends AbstractController {
    /**
     * @Route("/bilder", name="bilder")
     */
    public function index(): Response {
		$this->denyAccessUnlessGranted('ROLE_USER');
		
        $url = $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'BILDERARCHIV_URL'))->getV();

        $loginData = array(
            'pw' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'BILDERARCHIV_PASSWORD'))->getV()
        );

        return $this->render('bilder/index.html.twig', [
            'loginData' => $loginData,
            'url' => $url,
        ]);
    }
}
