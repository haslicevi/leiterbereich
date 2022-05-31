<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Anwesenheiten;
use App\Entity\Events;
use App\Entity\Mitglieder;

class LeiterAnwesenheitenController extends AbstractController
{
    /**
     * @Route("/anwesenheiten/leiter", name="leiter_anwesenheiten")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $leiter = $this->getDoctrine()
                        ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                        ->andWhere('p.funktion > 1')
                        ->andWhere('p.status > 0')
                        ->andWhere('p.status < 3')
                        ->addOrderBy('p.ceviname', 'ASC')
                        ->addOrderBy('p.vorname', 'ASC')
                        ->getQuery()->getResult();

        $events1 = $this->getDoctrine()->getRepository(Events::class)->createQueryBuilder('p')->where('p.von >= \''.date("Y", strtotime('-1 year')).'-01-01\'')->andWhere('p.von <= \''.date("Y").'-01-01\'')->andWhere('p.typ != 3')->orderBy('p.von', 'ASC')->getQuery()->getResult();
        $events2 = $this->getDoctrine()->getRepository(Events::class)->createQueryBuilder('p')->where('p.von >= \''.date("Y").'-01-01\'')->andWhere('p.von <= \''.date("Y", strtotime('+1 year')).'-01-01\'')->andWhere('p.typ != 3')->orderBy('p.von', 'ASC')->getQuery()->getResult();
        $jahre = array(date('Y', strtotime('-1 year')), date("Y"));

        $lt = array();

        foreach($leiter as $l) {
            if($l->getCeviname() == '') {
                $name = $l->getVorname().' '.$l->getNachname();
            } else {
                $name = $l->getCeviname();
            }

            $anz1 = 0;

            foreach($events1 as $event) {
                $dabei = $this->getDoctrine()
                                ->getRepository(Anwesenheiten::class)->createQueryBuilder('p')
                                ->where('p.mitglied = \''.$l->getId().'\'')
                                ->andWhere('p.status = 1')
                                ->andWhere('p.event = \''.$event->getId().'\'')
                                ->getQuery()->getResult();
                if(!empty($dabei)) {
                    $anz1 = $anz1 + 1;
                }
            }

            $lt[$name][] = $anz1;

            $anz2 = 0;

            foreach($events2 as $event) {
                $dabei = $this->getDoctrine()
                                ->getRepository(Anwesenheiten::class)->createQueryBuilder('p')
                                ->where('p.mitglied = \''.$l->getId().'\'')
                                ->andWhere('p.status = 1')
                                ->andWhere('p.event = \''.$event->getId().'\'')
                                ->getQuery()->getResult();
                if(!empty($dabei)) {
                    $anz2 = $anz2 + 1;
                }
            }

            $lt[$name][] = $anz2;
        }

        return $this->render('leiter_anwesenheiten/index.html.twig', [
            'leiter' => $lt,
            'jahre' => $jahre,
        ]);
    }

    /**
     * @Route("/anwesenheiten/leiter/verteilen", name="anwesenheiten_leiter_verteilen")
     */
    public function verteilen(): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $events = $this->getDoctrine()->getRepository(Events::class)->createQueryBuilder('p')->where('p.von >= \''.date("Y").'-01-01\'')->andWhere('p.typ != 3')->orderBy('p.von', 'ASC')->getQuery()->getResult();
        $nextEvent = $this->getDoctrine()->getRepository(Events::class)->createQueryBuilder('p')->where('p.von >= \''.date("Y").'-'.date("m").'-'.date("d").'\'')->andWhere('p.typ != 3')->orderBy('p.von', 'ASC')->getQuery()->setMaxResults(1)->getResult()[0];

        return $this->render('leiter_anwesenheiten/verteilen.html.twig', [
            'events' => $events,
            'nextEvent' => $nextEvent,
        ]);
    }

    /**
     * @Route("/anwesenheiten/leiter/qs/{id}", name="anwesenheiten_leiter_qs")
     */
    public function qs($id): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $event = $this->getDoctrine()->getRepository(Events::class)->find($id);
        
        $leiter = $this->getDoctrine()
                        ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                        ->andWhere('p.funktion > 1')
                        ->andWhere('p.status > 0')
                        ->andWhere('p.status < 3')
                        ->addOrderBy('p.ceviname', 'ASC')
                        ->addOrderBy('p.vorname', 'ASC')
                        ->getQuery()->getResult();

        return $this->render('leiter_anwesenheiten/qs.html.twig', [
            'event' => $event,
            'leiter' => $leiter,
        ]);
    }
}
