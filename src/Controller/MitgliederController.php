<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Stufen;
use App\Entity\Events;
use App\Entity\Mitglieder;
use App\Entity\SentMails;
use App\Entity\MailPostausgang;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class MitgliederController extends AbstractController {
    private $geschlecht = [
        '-' => 0,
        'Männlich' => 1,
        'Weiblich' => 2,
        'Andere' => 3,
    ];

    private $geschlechtIcon = [
        'mars' => 1,
        'venus' => 2,
        'star-of-life' => 3,
    ];

    private $funktion = [
        'Keine' => 0,
        'Cevianer' => 1,
        'Hilfsleiter' => 2,
        'Leiter' => 3,
        'Stufenleiter' => 4,
        'Abteilungsleiter' => 5,
        'Vorstand' => 6,
        'J+S Coach' => 7,
    ];

    private $status = [
        'Ausgetreten / Inaktiv' => 0,
        'Einfaches Mitglied' => 1,
        'Vollmitglied' => 2,
        'Gönner' => 3,
        'Schnuppernde(r)' => 4,
    ];

    private $krawatte = [
        'keine' => 0,
        'blau' => 1,
        'gelb/schwarz' => 2,
    ];

    private function stufen() {
        $stufen = $this->getDoctrine()->getRepository(Stufen::class)->findBy(array(), array('jahrgaenge' => 'ASC'));
        $array = Array();
        foreach ($stufen as $stufe) {
            $array[$stufe->getStufenname()] = $stufe->getId();
        }
        return $array;
    }

    /**
     * @Route("/mitglieder", name="mitglieder")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $mitglieder = $this->getDoctrine()->getRepository(Mitglieder::class)->findAll();

        $mitgliederArray = Array();
        foreach($mitglieder as $k => $v) {
            $a = json_encode(array_values((array) $v), JSON_UNESCAPED_UNICODE);
            $a = str_replace('"','',$a);
            $a = str_replace('{date:','',$a);
            $a = str_replace(' 00:00:00.000000,timezone_type:3,timezone:Europe\/Berlin}','',$a);
            $a = str_replace(',,,,,,,',',',$a);
            $a = str_replace(',,,,,,',',',$a);
            $a = str_replace(',,,,,',',',$a);
            $a = str_replace(',,,,',',',$a);
            $a = str_replace(',,,',',',$a);
            $a = str_replace(',,',',',$a);
            $mitgliederArray[] = $a;
        }

        return $this->render('mitglieder/index.html.twig', [
            'mitglieder' => $mitglieder,
            'mitgliederArray' => $mitgliederArray,
            'stufen' => array_flip($this->stufen()),
            'funktion' => array_flip($this->funktion),
            'status' => array_flip($this->status),
        ]);
    }

    /**
     * @Route("/mitglieder/ajaxSearchQuery/{query}", name="mitglieder_ajaxSearchQuery")
     */
    public function ajaxSearchQuery($query): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $query = urldecode(str_replace('_____', '%', str_replace('xxemptyxx', '', $query)));
        $this->get('session')->set('mitgliederSearchQuery', $query);

        return new Response('ok.', 200, array('Content-Type' => 'text/html'));
    }

    /**
     * @Route("/mitglieder/new", name="mitglieder_new")
     */
    public function new(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $mitglied = new Mitglieder();

        $form = $this->createFormBuilder($mitglied)
            ->add('vorname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('nachname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('ceviname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('strasse', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('nr', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'Hausnummer',
            ])
            ->add('plz', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'Postleitzahl',
            ])
            ->add('ort', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('geschlecht', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->geschlecht,
            ])
            ->add('geburtstag', BirthdayType::class, [
                'attr' => ['class' => 'form-control mb-3 js-datepicker'],
                'required' => false,
                'data' => new \DateTime(),
            ])
            ->add('telefon', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('handy', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('mail', EmailType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('funktion', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->funktion,
            ])
            ->add('status', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->status,
            ])
            ->add('stufe', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->stufen(),
            ])
            ->add('allergie', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('bemerkung', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('krawatte', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->krawatte,
            ])
            ->add('bankkonto', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('kurs', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('jsNummer', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'J&S Nummer',
            ])
            ->add('ahvNummer', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'AHV Nummer',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();

            // Standardwerte
            if($data->getStatus() == 4) {
                $data->setToken(strtoupper(bin2hex(random_bytes(3))));
            } else {
                $data->setToken('');
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'Das Mitglied wurde gespeichert.');
        }

        return $this->render('mitglieder/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mitglieder/edit/{id}", name="mitglieder_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $mitglied = $this->getDoctrine()->getRepository(Mitglieder::class)->find($id);
        
        $form = $this->createFormBuilder($mitglied)
            ->add('vorname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('nachname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('ceviname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('strasse', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('nr', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'Hausnummer',
            ])
            ->add('plz', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'Postleitzahl',
            ])
            ->add('ort', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('geschlecht', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->geschlecht,
            ])
            ->add('geburtstag', BirthdayType::class, [
                'attr' => ['class' => 'form-control mb-3 js-datepicker'],
                'required' => false,
            ])
            ->add('telefon', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('handy', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('mail', EmailType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('funktion', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->funktion,
            ])
            ->add('status', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->status,
            ])
            ->add('stufe', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->stufen(),
            ])
            ->add('allergie', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('bemerkung', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('krawatte', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->krawatte,
            ])
            ->add('bankkonto', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('kurs', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('jsNummer', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'J&S Nummer',
            ])
            ->add('ahvNummer', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'AHV Nummer',
            ])
            ->add('token', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'empty_data' => '',
                'label' => 'Token (löschen wenn definitiv angemeldet!)',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() &&  $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash('success', 'Das Mitglied wurde erfolgreich gespeichert.');
        }

        return $this->render('mitglieder/edit.html.twig', [
            'form' => $form->createView(),
            'mitglied' => $mitglied,
        ]);
    }

    /**
     * @Route("/mitglieder/delete/{id}", name="mitglieder_delete")
     */
    public function delete($id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $mitglied = $this->getDoctrine()->getRepository(Mitglieder::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($mitglied);
        $entityManager->flush();

        return $this->redirectToRoute('mitglieder');
    }

    /**
     * @Route("/mitglieder/info/{id}", name="mitglieder_info")
     * Method({"GET", "POST"})
     */
    public function info($id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $mitglied = $this->getDoctrine()->getRepository(Mitglieder::class)->find($id);

        return $this->render('mitglieder/info.html.twig', [
            'mitglied' => $mitglied,
            'geschlecht' => array_flip($this->geschlechtIcon),
            'funktion' => array_flip($this->funktion),
            'status' => array_flip($this->status),
            'krawatte' => array_flip($this->krawatte),
            'stufe' => array_flip($this->stufen()),
        ]);
    }

    /**
     * @Route("/public/schnuppern", name="public_schnuppern")
     * Method({"GET", "POST"})
     */
    public function schnuppern(Request $request, MailerInterface $mailer): Response {
        $mitglied = new Mitglieder();

        $form = $this->createFormBuilder($mitglied)
            ->add('vorname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('nachname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('geschlecht', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->geschlecht,
            ])
            ->add('geburtstag', BirthdayType::class, [
                'attr' => ['class' => 'form-control mb-3 js-datepicker'],
            ])
            ->add('handy', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('mail', EmailType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('bemerkung', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Anmelden',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();

            // Standardwerte
            $data->setToken(strtoupper(bin2hex(random_bytes(3))));
            $data->setFunktion(1);
            $data->setStatus(4);
            $data->setKrawatte(0);

            // Stufe ermitteln
            $jahrgang = $data->getGeburtstag()->format("Y");
            $stufe = $this->getDoctrine()->getRepository(Stufen::class)->createQueryBuilder('p')->where('p.jahrgaenge LIKE :word')->setParameter('word', '%'.$jahrgang.'%')->getQuery()->getResult();
            $data->setStufe($stufe[0]->getId());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            // Nächstes Programm
            $programm = $this->getDoctrine()
                        ->getRepository(Events::class)->createQueryBuilder('p')
                        ->where('p.von >= \''.date("Y").'-'.date("m").'-'.date("d").'\'')
                        ->andWhere('p.typ = 1')
                        ->orderBy('p.von', 'ASC')
                        ->getQuery()->setMaxResults(1)->getResult();

            // Bestätigungsmail
            // $email = (new TemplatedEmail())
            //     ->from(new Address('no-reply@haslicevimail.ch', 'CEVI Niederhasli Niederglatt'))
            //     ->to($data->getMail())
            //     ->subject('Anmeldung Schnupperprogramm')
            //     ->htmlTemplate('mail.html.twig')
            //     ->context([
            //         'titel' => 'Schnupperprogramm',
            //         'nachricht' => '<p>Hallo '.$data->getVorname().'</p><p>Du hast dich für ein Schnupperprogramm bei uns angemeldet. Das nächste Programm findet am '.$programm[0]->getVon()->format("d.m.Y").' statt. Der Treffpunkt ist um 14:00 Uhr vor dem <a href="https://goo.gl/maps/BtsqbNbN7dbMc5s99">Reformierten Kichgemeindehaus</a> in Niederhasli.</p><p>Den Nachmittag wirst du mit der Stufe '.$stufe[0]->getStufenname().' verbringen.</p><p>Bei weiteren Fragen stehen wir dir gerne zur Verfügung.</p><p>Bis dann!</p>',
            //     ]);
            // $mailer->send($email);

            // $sentMail = new SentMails();
            //     $sentMail->setMailFrom('no-reply@haslicevimail.ch');
            //     $sentMail->setMailTo($data->getMail());
            //     $sentMail->setSubject('Anmeldung Schnupperprogramm');
            //     $sentMail->setTitel('Schnupperprogramm');
            //     $sentMail->setNachricht('<p>Hallo '.$data->getVorname().'</p><p>Du hast dich für ein Schnupperprogramm bei uns angemeldet. Das nächste Programm findet am '.$programm[0]->getVon()->format("d.m.Y").' statt. Der Treffpunkt ist um 14:00 Uhr vor dem <a href="https://goo.gl/maps/BtsqbNbN7dbMc5s99">Reformierten Kichgemeindehaus</a> in Niederhasli.</p><p>Den Nachmittag wirst du mit der Stufe '.$stufe[0]->getStufenname().' verbringen.</p><p>Bei weiteren Fragen stehen wir dir gerne zur Verfügung.</p><p>Bis dann!</p>');
            //     $sentMail->setDate(date_create()->format('Y-m-d H:i:s'));

            // $entityManager = $this->getDoctrine()->getManager();
            //     $entityManager->persist($sentMail);
            //     $entityManager->flush();

            $email = new MailPostausgang();
                $email->setAn($data->getMail());
                $email->setBetreff('Anmeldung Schnupperprogramm');
                $email->setTitel('Schnupperprogramm');
                $email->setNachricht('<p>Hallo '.$data->getVorname().'</p><p>Du hast dich für ein Schnupperprogramm bei uns angemeldet. Das nächste Programm findet am '.$programm[0]->getVon()->format("d.m.Y").' statt. Der Treffpunkt ist um 14:00 Uhr vor dem <a href="https://goo.gl/maps/BtsqbNbN7dbMc5s99">Reformierten Kichgemeindehaus</a> in Niederhasli.</p><p>Den Nachmittag wirst du mit der Stufe '.$stufe[0]->getStufenname().' verbringen.</p><p>Bei weiteren Fragen stehen wir dir gerne zur Verfügung.</p><p>Bis dann!</p>');
                $email->setTimestamp(date_create()->format('Y-m-d H:i:s'));

                $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($email);
                    $entityManager->flush();

            $this->addFlash('success', 'Vielen Dank für deine Anmeldung. Weitere Infos bekommst du bald per Mail.');
        }

        return $this->render('mitglieder/schnuppern.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/public/anmeldung/", name="public_anmeldung")
     * Method({"GET", "POST"})
     */
    public function anmeldung(): Response {
        return $this->render('mitglieder/anmeldung.html.twig', [
            'msg' => false,
        ]);
    }

    /**
     * @Route("/public/anmeldung/error", name="public_anmeldungErrorLogin")
     * Method({"GET", "POST"})
     */
    public function anmeldungErrorLogin(): Response {
        return $this->render('mitglieder/anmeldung.html.twig', [
            'msg' => [
                'color' => 'danger',
                'text' => 'Der Token existiert nicht.'
            ]
        ]);
    }

    /**
     * @Route("/public/anmeldung/form/{token}", name="public_anmeldungIndex")
     * Method({"GET", "POST"})
     */
    public function anmeldungIndex($token, Request $request, MailerInterface $mailer): Response {
        $mitglied = $this->getDoctrine()->getRepository(Mitglieder::class)->findOneBy(array('token' => $token));
        if($mitglied === null or $token == '') {
            return $this->redirectToRoute('public_anmeldungErrorLogin');
        }
        
        $form = $this->createFormBuilder($mitglied)
            ->add('vorname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('nachname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('strasse', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('nr', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Hausnummer',
            ])
            ->add('plz', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Postleitzahl',
            ])
            ->add('ort', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('geschlecht', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->geschlecht,
            ])
            ->add('geburtstag', BirthdayType::class, [
                'attr' => ['class' => 'form-control mb-3 js-datepicker'],
            ])
            ->add('telefon', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('handy', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('mail', EmailType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('stufe', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->stufen(),
                'disabled' => true,
            ])
            ->add('allergie', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('bemerkung', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('ahvNummer', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'AHV Nummer (für J+S)',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Definitiv anmelden',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();

            // Standardwerte
            $data->setToken('');
            $data->setStatus(1);

            // Bestätigungsmail
            // $email = (new TemplatedEmail())
            //     ->from(new Address('no-reply@haslicevimail.ch', 'CEVI Niederhasli Niederglatt'))
            //     ->to($data->getMail())
            //     ->subject('Definitive Anmeldung')
            //     ->htmlTemplate('mail.html.twig')
            //     ->context([
            //         'titel' => 'Definitive Anmeldung',
            //         'nachricht' => '<p>Hallo '.$data->getVorname().'</p><p>Viele Dank für deine Anmeldung - du darfst dich nun offiziell Cevianerin oder Cevianer nennen.<br><a href="https://haslicevi.ch/dokumente">Hier</a> findest du einige wichtige Dokumente - bitte schaue dir diese gut an.</p><p>Die Infos fürs nächste Programm bekommst du via Mail.</p><p>Bis dann!</p>',
            //     ]);
            // $mailer->send($email);

            // $sentMail = new SentMails();
            //     $sentMail->setMailFrom('no-reply@haslicevimail.ch');
            //     $sentMail->setMailTo($data->getMail());
            //     $sentMail->setSubject('Definitive Anmeldung');
            //     $sentMail->setTitel('Definitive Anmeldung');
            //     $sentMail->setNachricht('<p>Hallo '.$data->getVorname().'</p><p>Viele Dank für deine Anmeldung - du darfst dich nun offiziell Cevianerin oder Cevianer nennen.<br><a href="https://haslicevi.ch/dokumente">Hier</a> findest du einige wichtige Dokumente - bitte schaue dir diese gut an.</p><p>Die Infos fürs nächste Programm bekommst du via Mail.</p><p>Bis dann!</p>');
            //     $sentMail->setDate(date_create()->format('Y-m-d H:i:s'));

            // $entityManager = $this->getDoctrine()->getManager();
            //     $entityManager->persist($sentMail); // gehört noch zum SentMails
            //     $entityManager->flush();

            $email = new MailPostausgang();
                $email->setAn($data->getMail());
                $email->setBetreff('Definitive Anmeldung');
                $email->setTitel('Definitive Anmeldung');
                $email->setNachricht('<p>Hallo '.$data->getVorname().'</p><p>Viele Dank für deine Anmeldung - du darfst dich nun offiziell Cevianerin oder Cevianer nennen.<br><a href="https://haslicevi.ch/dokumente">Hier</a> findest du einige wichtige Dokumente - bitte schaue dir diese gut an.</p><p>Die Infos fürs nächste Programm bekommst du via Mail.</p><p>Bis dann!</p>');
                $email->setTimestamp(date_create()->format('Y-m-d H:i:s'));

                $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($email);
                    $entityManager->flush();

            $this->addFlash('success', 'Vielen Dank für deine Anmeldung. Weitere Infos bekommst du bald per Mail.');
        }

        return $this->render('mitglieder/anmeldungIndex.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
