<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Anwesenheiten;
use App\Entity\Events;
use App\Entity\Stufen;
use App\Entity\Mitglieder;
use App\Entity\SentMails;
use App\Entity\MailPostausgang;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class AnwesenheitenController extends AbstractController {
    /**
     * @Route("/anwesenheiten", name="anwesenheiten")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $stufen = $this->getDoctrine()->getRepository(Stufen::class)->createQueryBuilder('p')->where('p.jahrgaenge != \'-\'')->orderBy('p.jahrgaenge', 'DESC')->getQuery()->getResult();
        foreach($stufen as $stufe) {
            $punkte = array();
            $mitglieder = $this->getDoctrine()
                                ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                                ->where('p.stufe = '.$stufe->getId())
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
            $personen[$stufe->getId()] = $newPunkte;
        }

        return $this->render('anwesenheiten/index.html.twig', [
            'stufen' => $stufen,
            'personen' => $personen,
        ]);
    }

    /**
     * @Route("/anwesenheiten/verteilen", name="anwesenheiten_verteilen")
     */
    public function verteilen(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $events = $this->getDoctrine()->getRepository(Events::class)->createQueryBuilder('p')->where('p.von >= \''.date("Y").'-01-01\'')->andWhere('p.typ != 3')->andWhere('p.typ != 5')->orderBy('p.von', 'ASC')->getQuery()->getResult();
        $nextEvent = $this->getDoctrine()->getRepository(Events::class)->createQueryBuilder('p')->where('p.von >= \''.date("Y").'-'.date("m").'-'.date("d").'\'')->andWhere('p.typ != 3')->andWhere('p.typ != 5')->orderBy('p.von', 'ASC')->getQuery()->setMaxResults(1)->getResult()[0];

        return $this->render('anwesenheiten/verteilen.html.twig', [
            'events' => $events,
            'nextEvent' => $nextEvent,
        ]);
    }

    /**
     * @Route("/anwesenheiten/qs/{id}", name="anwesenheiten_qs")
     */
    public function qs($id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $event = $this->getDoctrine()->getRepository(Events::class)->find($id);
        $stufen = $this->getDoctrine()->getRepository(Stufen::class)->createQueryBuilder('p')->where('p.jahrgaenge != \'-\'')->orderBy('p.jahrgaenge', 'DESC')->getQuery()->getResult();

        foreach($stufen as $stufe) {
            $mitglieder = $this->getDoctrine()
                                ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                                ->where('p.stufe = '.$stufe->getId())
                                ->andWhere('p.funktion = 1')
                                ->andWhere('p.status = 1')
                                ->orderBy('p.ceviname', 'ASC')
                                ->addOrderBy('p.vorname', 'ASC')
                                ->getQuery()->getResult();
            $personen[$stufe->getId()] = $mitglieder;
        }

        // Leiter
        $leiter = $this->getDoctrine()
                        ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                        ->andWhere('p.funktion > 1')
                        ->andWhere('p.status > 0')
                        ->andWhere('p.status < 3')
                        ->addOrderBy('p.ceviname', 'ASC')
                        ->addOrderBy('p.vorname', 'ASC')
                        ->getQuery()->getResult();

        $stufen[] = array("stufenname" => "Leiter", "id" => "leiter");
        $personen['leiter'] = $leiter;

        return $this->render('anwesenheiten/qs.html.twig', [
            'event' => $event,
            'stufen' => $stufen,
            'personen' => $personen,
        ]);
    }

    /**
     * @Route("/anwesenheiten/ajax/{event}/{status}/{mitglied}", name="anwesenheiten_ajax")
     */
    public function ajax($event, $status, $mitglied): Response {
        $data = $this->getDoctrine()->getRepository(Anwesenheiten::class)->findOneBy(array('mitglied' => $mitglied, 'event' => $event));
        if($data != null) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($data);
            $entityManager->flush();
        }

        if($status == 1) {
            $anwesenheit = new Anwesenheiten();
            $anwesenheit->setMitglied($mitglied);
            $anwesenheit->setEvent($event);
            $anwesenheit->setStatus(1);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($anwesenheit);
            $entityManager->flush();
        }

        if($status == 2) {
            $anwesenheit = new Anwesenheiten();
            $anwesenheit->setMitglied($mitglied);
            $anwesenheit->setEvent($event);
            $anwesenheit->setStatus(2);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($anwesenheit);
            $entityManager->flush();
        }

        return new Response('Ok.', 200, array('Content-Type' => 'text/html'));
    }

    /**
     * @Route("/anwesenheiten/ajaxGet/{event}/{mitglied}", name="anwesenheiten_ajaxGet")
     */
    public function ajaxGet($event, $mitglied): Response {
        $data = $this->getDoctrine()->getRepository(Anwesenheiten::class)->findOneBy(array('mitglied' => $mitglied, 'event' => $event));

        if($data === null) {
            $return = 3;
            $grund = 'null';
        } else {
            if($data->getStatus() == 1) {
                $return = 1;
                $grund = 'null';
        } else if($data->getStatus() == 2) {
                $return = 2;
                $grund = $data->getText();
            }
        }

        return new Response($mitglied.'---'.$return.'---'.$grund, 200, array('Content-Type' => 'text/html'));
    }

    /**
     * @Route("cronjob/anwesenheit/{token}", name="cronjobs_anwesenheit")
     */
    public function cronjobAnwesenheit($token, MailerInterface $mailer) {
        $programm = $this->getDoctrine()
                            ->getRepository(Events::class)->createQueryBuilder('p')
                            ->where('p.von = \''.date("Y").'-'.date("m").'-'.date("d").'\'')->andWhere('p.typ = 1')
                            // ->orWhere('p.von = \''.date("Y").'-'.date("m").'-'.date("d").'\'')->andWhere('p.typ = 2')
                            ->orderBy('p.von', 'ASC')
                            ->getQuery()->setMaxResults(1)->getResult()[0];

        if($programm == null) {
            return new Response('Kein Programm gefunden.', 200, array('Content-Type' => 'text/html'));
        }

        $stufen = $this->getDoctrine()
                        ->getRepository(Stufen::class)->createQueryBuilder('p')
                        ->where('p.stufenname != \'Keine\'')
                        ->orderBy('p.jahrgaenge', 'ASC')
                        ->getQuery()->getResult();

        $text = '';

        // foreach($programme as $programm) {
            foreach($stufen as $stufe) {
                $schnuppernde = $this->getDoctrine()
                                        ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                                        ->where('p.stufe = '.$stufe->getId())->andWhere('p.status = 4')
                                        ->orderBy('p.vorname', 'ASC')
                                        ->getQuery()->getResult();

                $tn = $this->getDoctrine()
                            ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                            ->where('p.funktion = 1')->andWhere('p.stufe = '.$stufe->getId())->andWhere('p.status = 1')
                            ->orderBy('p.ceviname', 'ASC')->addOrderBy('p.vorname', 'ASC')
                            ->getQuery()->getResult();

                $text .= '<h3 style="margin-top: 50px;">Stufe '.$stufe->getStufenname().'</h3>';
                $text .= '<table width="100%" border="1">';
                foreach($schnuppernde as $s) {
                    $text .= '<tr><td style="background-color: #ffff00;">'.$s->getVorname().' '.$s->getNachname().'</td><td style="background-color: #ffff00;">Schnuppernde(r)<br>Token: '.$s->getToken().'</td></tr>';
                }
                foreach($tn as $t) {
                    $abgemeldet = $this->getDoctrine()
                                        ->getRepository(Anwesenheiten::class)->createQueryBuilder('p')
                                        ->where('p.mitglied = \''.$t->getId().'\'')
                                        ->andWhere('p.event = \''.$programm->getId().'\'')
                                        ->andWhere('p.status = 2')
                                        ->getQuery()->getResult();

                    if(isset($abgemeldet[0])) { 
                        if($abgemeldet[0]->getText() == '') { $grund = ''; } else { $grund = '(Grund: '.$abgemeldet[0]->getText().')'; }
                        $status = 'Abgemeldet '.$grund; 
                    } else { 
                        $status = ''; 
                    } 

                    if($t->getCeviname() == '') {
                        $name = $t->getVorname().' '.$t->getNachname();
                    } else {
                        $name = $t->getCeviname();
                    }

                    if($status == '') {
                        $text .= '<tr><td>'.$name.'</td><td>'.$status.'</td></tr>';
                    } else {
                        $text .= '<tr><td style="background-color: #ff0000; color: #fff;">'.$name.'</td><td style="background-color: #ff0000; color: #fff;">'.$status.'</td></tr>';
                    }
                }
                $text .= '</table>';
            }

            if($programm->getTyp() == 1) { $programmname = 'Nachmittagsprogramm'; } else { $programmname = $programm->getName(); }

            if($token == '9MFGSkh6Ux9B' and $programm->getVon()->format("Y-m-d") == date("Y").'-'.date("m").'-'.date("d")) {
                $mitglieder = $this->getDoctrine()
                                    ->getRepository(Mitglieder::class)->createQueryBuilder('p')
                                    ->where('p.status > 0')->andWhere('p.status < 3')
                                    ->andWhere('p.funktion > 1')->andWhere('p.funktion < 6')
                                    ->getQuery()->getResult();

                foreach($mitglieder as $leiter) {
                    // $email = (new TemplatedEmail())
                    //     ->from(new Address('no-reply@haslicevimail.ch', 'CEVI Niederhasli Niederglatt'))
                    //     ->to($leiter->getMail())
                    //     ->subject('Anwesenheiten')
                    //     ->htmlTemplate('mail.html.twig')
                    //     ->context([
                    //         'titel' => $programm->getVon()->format("d.m.Y").' '.$programmname,
                    //         'nachricht' => '<p>Hallo '.$leiter->getCeviname().'</p><p>Folgende TN sind an- oder abgemeldet:</p>'.$text,
                    //     ]);
                    // $mailer->send($email);

                    // $sentMail = new SentMails();
                    //     $sentMail->setMailFrom('no-reply@haslicevimail.ch');
                    //     $sentMail->setMailTo($leiter->getMail());
                    //     $sentMail->setSubject('Anwesenheiten');
                    //     $sentMail->setTitel($programm->getVon()->format("d.m.Y").' '.$programmname);
                    //     $sentMail->setNachricht('<p>Hallo '.$leiter->getCeviname().'</p><p>Folgende TN sind an- oder abgemeldet:</p>'.$text);
                    //     $sentMail->setDate(date_create()->format('Y-m-d H:i:s'));

                    // $entityManager = $this->getDoctrine()->getManager();
                    //     $entityManager->persist($sentMail);
                    //     $entityManager->flush();

                    $email = new MailPostausgang();
                    $email->setAn($leiter->getMail());
                    $email->setBetreff('Anwesenheiten');
                    $email->setTitel($programm->getVon()->format("d.m.Y").' '.$programmname);
                    $email->setNachricht('<p>Hallo '.$leiter->getCeviname().'</p><p>Folgende TN sind an- oder abgemeldet:</p>'.$text);
                    $email->setTimestamp(date_create()->format('Y-m-d H:i:s'));

                    $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($email);
                        $entityManager->flush();
                }
    
                return new Response('mails versendet. (Programm: '.$programm->getVon()->format("Y-m-d").' '.$programm->getName().')',200);
            } else {
                return new Response('nichts versendet. (Programm: '.$programm->getVon()->format("Y-m-d").' '.$programm->getName().')',200);
            }
        // }
    }
}
