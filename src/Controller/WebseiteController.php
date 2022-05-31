<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\GlobaleVariabeln;

class WebseiteController extends AbstractController
{
    /**
     * @Route("/webseite", name="webseite")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $url = 'https://haslicevi.ch/';

        if(in_array('ROLE_ADMIN', $this->getUser()->getRoles())){
            $loginData = array(
                'user' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'CMS_ADMIN_USER'))->getV(),
                'pw' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'CMS_ADMIN_PASSWORD'))->getV()
            ); 
            
            $bildergalerie = array(
                'user' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'BILDERGALERIE_USER'))->getV(),
                'pw' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'BILDERGALERIE_PW'))->getV()
            );

            $dokumente = array(
                'user' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'MYHASLICEVI_DOKUMENTE_USER'))->getV(),
                'pw' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'MYHASLICEVI_DOKUMENTE_PW'))->getV()
            );
        } else {
            $loginData = array(
                'user' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'CMS_LEITER_USER'))->getV(),
                'pw' => $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'CMS_LEITER_PASSWORD'))->getV()
            );

            $bildergalerie = array();

            $dokumente = array();
        }

        return $this->render('webseite/index.html.twig', [
            'loginData' => $loginData,
            'bildergalerie' => $bildergalerie,
            'dokumente' => $dokumente,
            'url' => $url,
        ]);
    }
}
