<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Anwesenheiten;
use App\Entity\Events;
use App\Entity\Stufen;
use App\Entity\Mitglieder;
use App\Entity\GlobaleVariabeln;
use App\Entity\Dokumente;
use App\Entity\SentMails;

class MyHasliceviController extends AbstractController {
    /**
     * @Route("/myhaslicevi", name="myhaslicevi")
     */
    public function index(): Response {
        $stufen = $this->getDoctrine()->getRepository(Stufen::class)->findAll();

        return $this->render('my_haslicevi/index.html.twig', [
            'stufen' => $stufen,
            'msg' => false,
        ]);
    }

    /**
     * @Route("/myhaslicevi/error1", name="myhaslicevi_errorLogin")
     */
    public function error1(): Response {
        $stufen = $this->getDoctrine()->getRepository(Stufen::class)->findAll();

        return $this->render('my_haslicevi/index.html.twig', [
            'stufen' => $stufen,
            'msg' => [
                'color' => 'danger',
                'text' => 'Dein Account wurde nicht gefunden. Wende dich bei Fragen an <a href="mailto:tux@haslicevi.ch">Tux</a>.'
            ]
        ]);
    }

    /**
     * @Route("/myhaslicevi/logout", name="myhaslicevi_logout")
     */
    public function logout(): Response {
        $stufen = $this->getDoctrine()->getRepository(Stufen::class)->findAll();

        return $this->render('my_haslicevi/index.html.twig', [
            'stufen' => $stufen,
            'msg' => [
                'color' => 'success',
                'text' => 'Du hast dich erfolgreich abgemeldet.'
            ]
        ]);
    }

    /**
     * @Route("/myhaslicevi/login/{page}/{vorname}/{mail}/{stufe}/{nr}", name="myhaslicevi_login")
     */
    public function login($page, $vorname, $mail, $stufe, $nr): Response {
        $login = $this->getDoctrine()->getRepository(Mitglieder::class)->findOneBy(array('vorname' => $vorname, 'mail' => $mail, 'stufe' => $stufe, 'nr' => $nr, 'funktion' => 1));
        if($login === null) {
            return $this->redirectToRoute('myhaslicevi_errorLogin');
        }
        
        $punkte = $this->punkte($login->getStufe());
        $stufenleiter = $this->getDoctrine()
                                ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                                ->where('p.stufe = \''.$login->getStufe().'\'')
                                ->andWhere('p.funktion = 4')
                                ->getQuery()->getResult();
        $programme = $this->getDoctrine()
                                ->getRepository(Events::class)->createQueryBuilder('p')
                                ->where('p.von >= \''.date("Y").'-01-01\'')
                                ->andWhere('p.von <= \''.date("Y").'-'.date("m").'-'.date("d").'\'')
                                ->andWhere('p.typ < 3')
                                ->orderBy('p.von', 'ASC')
                                ->getQuery()->getResult();
        $nextProgramm = $this->getDoctrine()
                                ->getRepository(Events::class)->createQueryBuilder('p')
                                ->where('p.von >= \''.date("Y").'-'.date("m").'-'.date("d").'\'')
                                ->andWhere('p.typ < 3')
                                ->orderBy('p.von', 'ASC')
                                ->getQuery()->setMaxResults(1)->getResult()[0];
        $abteilungsleiter = $this->getDoctrine()
                                ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                                ->where('p.funktion = 5')
                                ->getQuery()->getResult();
        $bilderarchiv = $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'MYHASLICEVI_BILDERARCHIV'))->getV();
        $dokumente = $this->getDoctrine()->getRepository(Dokumente::class)->findBy(array('myhaslicevi' => true), array('datum' => 'DESC'));
        $mails = $this->getDoctrine()
                                ->getRepository(SentMails::class)->createQueryBuilder('p')
                                ->where('p.mailTo = \''.$mail.'\'')
                                ->orderBy('p.date', 'DESC')
                                ->setMaxResults(15)
                                ->getQuery()->getResult();
        $anwesenheiten = array();
        foreach($programme as $programm) {
            $anwesenheitprogramm = $this->getDoctrine()
                                        ->getRepository(Anwesenheiten::class)->createQueryBuilder('p')
                                        ->where('p.mitglied = \''.$login->getId().'\'')
                                        ->andWhere('p.event = \''.$programm->getId().'\'')
                                        ->getQuery()->getResult();

            if(count($anwesenheitprogramm) != 0) {
                if($anwesenheitprogramm[0]->getStatus() == 1) {
                    $status = '<span class="text-success">Anwesend</span>';
                } else {
                    if($anwesenheitprogramm[0]->getText() != '' or $anwesenheitprogramm[0]->getText() !== null) {
                        $grund = ' ('.$grund = $anwesenheitprogramm[0]->getText().')';
                    } else {
                        $grund = '';
                    }
                    $status = '<span class="text-secondary">Abgemeldet'.$grund.'</span>';
                }
            } else {
                $status = '<span class="text-danger">Nichts gehört</span>';
            }

            $anwesenheiten[$programm->getId()] = $status;
        }

        if($this->getDoctrine()->getRepository(Anwesenheiten::class)->findOneBy(array('mitglied' => $login->getId(), 'event' => $nextProgramm->getId())) === null) {
            $formStatus = true;
        } else {
            $formStatus = false;
        }
        
        if($login !== null) {
            return $this->render('my_haslicevi/login.html.twig', [
                'user' => $login,
                'punkte' => $punkte,
                'rang' => array_search($login->getId(), array_keys($punkte)) + 1,
                'stufenleiter' => $stufenleiter,
                'abteilungsleiter' => $abteilungsleiter,
                'programme' => $programme,
                'nextProgramm' => $nextProgramm,
                'formStatus' => $formStatus,
                'page' => $page,
                'url' => $userProfilePage = $this->generateUrl('myhaslicevi_login', [
                    'page' => 'xpagex',
                    'vorname' => $vorname,
                    'mail' => $mail,
                    'stufe' => $stufe,
                    'nr' => $nr,
                ]),
                'bilderarchiv' => $bilderarchiv,
                'dokumente' => $dokumente,
                'mails' => $mails,
                'mailHash' => hash('ripemd160', $mail),
                'anwesenheiten' => $anwesenheiten,
            ]);
        }
    }

    /**
     * @Route("/myhaslicevi/abmelden/{mitglied}/{event}/{text}", name="myhaslicevi_login_abmelden")
     */
    public function abmelden($mitglied, $event, $text): Response {
        $data = $this->getDoctrine()->getRepository(Anwesenheiten::class)->findOneBy(array('mitglied' => $mitglied, 'event' => $event));
        if($data != null) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($data);
            $entityManager->flush();
        }

        $anwesenheit = new Anwesenheiten();
        $anwesenheit->setMitglied($mitglied);
        $anwesenheit->setEvent($event);
        $anwesenheit->setStatus(2);
        $anwesenheit->setText($text);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($anwesenheit);
        $entityManager->flush();

        return new Response('ok', 200, array('Content-Type' => 'text/html'));
    }

    /**
     * @Route("/myhaslicevi/mail/{id}/{hash}", name="myhaslicevi_mail")
     */
    public function mail($id, $hash): Response {
        $mail = $this->getDoctrine()->getRepository(SentMails::class)->find($id);

        if(hash('ripemd160', $mail->getMailTo()) == $hash) {
            return $this->render('mail.html.twig', [
                'titel' => $mail->getTitel(),
                'nachricht' => $mail->getNachricht(),
                'email' => [
                    'to' => [
                        0 => [
                            'address' => $mail->getMailTo()
                        ]
                    ]
                ]
            ]);
        } else {
            return new Response('Dazu hast du keine Berechtigung.', 200, array('Content-Type' => 'text/html'));
        }
    }

    private function punkte($stufe) {
        $punkte = array();
        $mitglieder = $this->getDoctrine()
                            ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                            ->where('p.stufe = '.$stufe)
                            ->andWhere('p.funktion = 1')
                            ->andWhere('p.status = 1')
                            ->orderBy('p.ceviname', 'ASC')
                            ->addOrderBy('p.vorname', 'ASC')
                            ->getQuery()->getResult();
        foreach($mitglieder as $mitglied) {
            $data = array();
            $pt = 0;

            // Teilgenommen
            $events = $this->getDoctrine()
                            ->getRepository(Anwesenheiten::class)->createQueryBuilder('p')
                            ->where('p.mitglied = \''.$mitglied->getId().'\'')
                            ->andWhere('p.status = 1')
                            ->getQuery()->getResult();
            foreach($events as $event) {
                $data = $this->getDoctrine()->getRepository(Events::class)->find($event->getEvent());

                if($data->getVon()->format("Y") == date("Y")) {
                    if($data->getTyp() == 1) {
                        // Nachmittagsprogramm
                        $pt = $pt + 2;
                    }
                    if($data->getTyp() == 2) {
                        // Spezialprogramm
                        $pt = $pt + 3;
                    }
                    if($data->getTyp() == 4) {
                        // Lager
                        $pt = $pt + 5;
                    }
                }
            }

            // Abgemeldet
            $events = $this->getDoctrine()
                            ->getRepository(Anwesenheiten::class)->createQueryBuilder('p')
                            ->where('p.mitglied = \''.$mitglied->getId().'\'')
                            ->andWhere('p.status = 2')
                            ->getQuery()->getResult();
            foreach($events as $event) {
                $data = $this->getDoctrine()->getRepository(Events::class)->find($event->getEvent());

                if($data->getVon()->format("Y") == date("Y")) {
                    $pt = $pt + 1;
                }
            }

            $punkte[$mitglied->getId()] = $pt;
        }
        arsort($punkte);
        $newPunkte = array();
        foreach($punkte as $k => $v) {
            $mitglied = $this->getDoctrine()->getRepository(Mitglieder::class)->find($k);
            $newPunkte[$k] = array($v, $mitglied);
        }

        return $newPunkte;
    }
}
