<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Events;
use App\Entity\Stufen;
use App\Entity\Mitglieder;
use App\Entity\Anwesenheiten;

class HomeController extends AbstractController {
    /**
     * @Route("/", name="home_forward")
     */
    public function index_forward() {
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/home", name="home")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $progi = $this->getDoctrine()
                        ->getRepository(Events::class)->createQueryBuilder('p')
                        ->where('p.von >= \''.date("Y-m-d").'\'')
                        ->andWhere('p.typ = 1')
                        ->orderBy('p.von', 'ASC')
                        ->getQuery()->getResult();

        $grossprogi = $this->getDoctrine()
                            ->getRepository(Events::class)->createQueryBuilder('p')
                            ->where('p.von >= \''.date("Y-m-d").'\'')
                            ->andWhere('p.typ = 2')
                            ->orderBy('p.von', 'ASC')
                            ->getQuery()->getResult();

        $nextProgramm = $this->getDoctrine()
                            ->getRepository(Events::class)->createQueryBuilder('p')
                            ->where('p.von >= \''.date("Y-m-d").'\'')->andWhere('p.typ = 1')->orWhere('p.typ = 2')->andWhere('p.von >= \''.date("Y-m-d").'\'')
                            ->orderBy('p.von', 'ASC')
                            ->getQuery()->getResult();

        $stufen = $this->getDoctrine()->getRepository(Stufen::class)->createQueryBuilder('p')->where('p.jahrgaenge != \'-\'')->orderBy('p.jahrgaenge', 'ASC')->getQuery()->getResult();

        $anwesenheiten = array();

        foreach($stufen as $stufe) {
            $cevianer = $this->getDoctrine()->getRepository(Mitglieder::class)->createQueryBuilder('p')->where('p.stufe = '.$stufe->getId())->andWhere('p.funktion = 1')->andWhere('p.status = 1')->orderBy('p.ceviname', 'ASC')->addOrderBy('p.vorname', 'ASC')->getQuery()->getResult();
            $schnuppernde = $this->getDoctrine()->getRepository(Mitglieder::class)->createQueryBuilder('p')->where('p.stufe = '.$stufe->getId())->andWhere('p.status = 4')->orderBy('p.ceviname', 'ASC')->addOrderBy('p.vorname', 'ASC')->getQuery()->getResult();
            $abgemeldet = 0;
            foreach($cevianer as $tn) {
                $data = $this->getDoctrine()->getRepository(Anwesenheiten::class)->findOneBy(array('mitglied' => $tn->getId(), 'event' => $nextProgramm[0]->getId()));
                if($data) {
                    if($data->getStatus() == 2) {
                        $abgemeldet++;
                    }
                }
            }

            $cevianer = count($cevianer);
            $schnuppernde = count($schnuppernde);

            $anwesenheiten[$stufe->getStufenname()] = [
                'stufenname' => $stufe->getStufenname(),
                'anwesend' => $cevianer + $schnuppernde - $abgemeldet,
                'abgemeldet' => $abgemeldet,
                'schnuppernde' => $schnuppernde
            ];
        }

        return $this->render('home/index.html.twig', [
            'env' => getenv('APP_ENV'),
            'progi' => $progi,
            'grossprogi' => $grossprogi,
            'nextProgramm' => $nextProgramm,
            'anwesenheiten' => $anwesenheiten,
        ]);
    }
}
