<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Events;
use App\Entity\Mitglieder;
use App\Entity\SentMails;
use App\Entity\MailPostausgang;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class EventsController extends AbstractController {
    private $typ = [
        'Nachmittagsprogramm' => 1,
        'Spezialprogramm' => 2,
        'Ferien' => 3,
        'Lager' => 4,
        'Leiterevent' => 5,
    ];

    private $typFarben = [
        1 => 'primary',
        2 => 'info',
        3 => 'warning',
        4 => 'success',
        5 => 'danger',
    ];

    /**
     * @Route("/events", name="events")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $events = $this->getDoctrine()
                        ->getRepository(Events::class)->createQueryBuilder('p')
                        ->where('p.von >= \''.date("Y").'-'.date("m").'-'.date("d").'\'')
                        ->orderBy('p.von', 'ASC')
                        ->getQuery()->getResult();

        return $this->render('events/index.html.twig', [
            'events' => $events,
            'typ' => array_flip($this->typ),
            'typFarben' => $this->typFarben,
        ]);
    }

    /**
     * @Route("/events/all", name="events_all")
     */
    public function indexAll(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $events = $this->getDoctrine()->getRepository(Events::class)->findBy(array(), array('von' => 'ASC'));

        return $this->render('events/index.html.twig', [
            'events' => $events,
            'typ' => array_flip($this->typ),
            'typFarben' => $this->typFarben,
        ]);
    }

    /**
     * @Route("/events/new", name="events_new")
     */
    public function new(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $event = new Events();

        $form = $this->createFormBuilder($event)
            ->add('von', DateType::class, [
                'attr' => ['class' => 'form-control mb-3 js-datepicker'],
                'data' => new \DateTime(),
            ])
            ->add('bis', DateType::class, [
                'attr' => ['class' => 'form-control mb-3 js-datepicker'],
                'data' => new \DateTime(),
                // 'label' => 'Bis (kann bei eintägigen Events übersprungen werden)',
                'label' => 'Bis',
            ])
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'Name (nicht benötigt bei Nachmittagsprogrammen)',
            ])
            ->add('typ', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->typ,
            ])
            ->add('link', TextType::class, [
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'https://xyz.haslicevi.ch/',
                ],
                'required' => false,
                'label' => 'Link (ganz ausgeschrieben, sonst leer lassen)',
            ])
            ->add('bemerkungen', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
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
            $data->setActive(1);
            if(strtotime($data->getBis()->format("Y-m-d")) == strtotime(date('Y-m-d'))) {
                $data->setBis( $data->getVon());
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'Der Event wurde gespeichert.');
        }

        return $this->render('events/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/events/edit/{id}", name="events_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $event = $this->getDoctrine()->getRepository(Events::class)->find($id);

        $form = $this->createFormBuilder($event)
            ->add('von', DateType::class, [
                'attr' => ['class' => 'form-control mb-3 js-datepicker'],
            ])
            ->add('bis', DateType::class, [
                'attr' => ['class' => 'form-control mb-3 js-datepicker'],
            ])
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'label' => 'Name (nicht benötigt bei Nachmittagsprogrammen)',
            ])
            ->add('typ', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->typ,
            ])
            ->add('active', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => [
                    'Aktiv' => 1,
                    'Inaktiv' => 0
                ],
                'label' => 'Status',
            ])
            ->add('link', TextType::class, [
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'https://xyz.haslicevi.ch/',
                ],
                'required' => false,
                'label' => 'Link (ganz ausgeschrieben, sonst leer lassen)',
            ])
            ->add('bemerkungen', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
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

            $this->addFlash('success', 'Der Event wurde erfolgreich gespeichert.');
        }

        return $this->render('events/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    /**
     * @Route("/events/delete/{id}", name="events_delete")
     */
    public function delete($id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $event = $this->getDoctrine()->getRepository(Events::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('events');
    }

    /**
     * @Route("events/api", name="events_api")
     */
    public function api(Request $request) {
        $events = $this->getDoctrine()
                        ->getRepository(Events::class)->createQueryBuilder('p')
                        ->where('p.von >= \''.date("Y").'-01-01\'')
                        ->andWhere('p.active = 1')
                        ->andWhere('p.typ != 5')
                        ->orderBy('p.von', 'ASC')
                        ->getQuery()->getResult();

        return $this->render('events/api.html.twig', [
            'events' => $events,
            'typ' => array_flip($this->typ),
            'typFarben' => $this->typFarben,
        ]);
    }

    /**
     * @Route("/public/events/print", name="events_print")
     */
    public function print(Request $request) {
        $events = $this->getDoctrine()
                        ->getRepository(Events::class)->createQueryBuilder('p')
                        ->where('p.von >= \''.date("Y").'-01-01\'')
                        ->andWhere('p.von <= \''.date("Y", strtotime('+1 year')).'-01-01\'')
                        ->andWhere('p.active = 1')
                        ->andWhere('p.typ != 5')
                        ->orderBy('p.von', 'ASC')
                        ->getQuery()->getResult();

        return $this->render('events/print.html.twig', [
            'events' => $events,
            'typ' => array_flip($this->typ),
            'typFarben' => $this->typFarben,
        ]);
    }

    /**
     * @Route("events/api/widget", name="events_api_widget")
     */
    public function apiWidget(Request $request) {
        $events = $this->getDoctrine()
                        ->getRepository(Events::class)->createQueryBuilder('p')
                        ->where('p.von >= \''.date("Y-m-d").'\'')
                        ->andWhere('p.active = 1')
                        ->andWhere('p.typ != 3')
                        ->andWhere('p.typ != 5')
                        ->orderBy('p.von', 'ASC')
                        ->setMaxResults(3)
                        ->getQuery()->getResult();

        return $this->render('events/widget.html.twig', [
            'events' => $events,
            'typ' => array_flip($this->typ),
        ]);
    }

    /**
     * @Route("cronjob/eventreminder/{token}", name="cronjobs_eventreminder")
     */
    public function cronjobEventreminder($token, MailerInterface $mailer) {
        $programm = $this->getDoctrine()
                            ->getRepository(Events::class)->createQueryBuilder('p')
                            ->where('p.von >= \''.date("Y").'-'.date("m").'-'.date("d").'\'')
                            ->andWhere('p.typ < 3')
                            ->orderBy('p.von', 'ASC')
                            ->getQuery()->setMaxResults(1)->getResult();

        if($programm[0]->getTyp() == 1) { $programmname = 'Nachmittagsprogramm'; } else { $programmname = $programm[0]->getName(); }

        if(time() + (7 * 24 * 60 * 60) > strtotime($programm[0]->getVon()->format("d.m.Y")) and $token == 'x4L3nGzBXrpm') {
            $mitglieder = $this->getDoctrine()
                                ->getRepository(Mitglieder::class)->createQueryBuilder('p')
								->where('p.status = 1')->orWhere('p.status = 2')
                                ->getQuery()->getResult();
            
            foreach($mitglieder as $mitglied) {
                if($mitglied->getCeviname() == '') {
                    $name = $mitglied->getVorname();
                } else {
                    $name = $mitglied->getCeviname();
                }
                
                // $email = (new TemplatedEmail())
                //     ->from(new Address('no-reply@haslicevimail.ch', 'CEVI Niederhasli Niederglatt'))
                //     ->to($mitglied->getMail())
                //     ->subject('CEVI Reminder')
                //     ->htmlTemplate('mail.html.twig')
                //     ->context([
                //         'titel' => $programm[0]->getVon()->format("d.m.Y").' '.$programmname,
                //         'nachricht' => '<p>Hallo '.$name.'</p><p>Demnächst findet ein weiteres CEVI-Programm statt: '.$programm[0]->getVon()->format("d.m.Y").' ('.$programmname.')<br>Weitere Informationen dazu findest du <a href="https://haslicevi.ch/agenda">hier</a>.<br>Falls du dann verhindert sein solltest, kannst du dich <a href="https://haslicevi.ch/my">hier</a> abmelden.</p><p>Bis bald!</p>',
                //     ]);
                //  $mailer->send($email);

                //  $sentMail = new SentMails();
                //         $sentMail->setMailFrom('no-reply@haslicevimail.ch');
                //         $sentMail->setMailTo($mitglied->getMail());
                //         $sentMail->setSubject('CEVI Reminder');
                //         $sentMail->setTitel($programm[0]->getVon()->format("d.m.Y").' '.$programmname);
                //         $sentMail->setNachricht('<p>Hallo '.$name.'</p><p>Demnächst findet ein weiteres CEVI-Programm statt: '.$programm[0]->getVon()->format("d.m.Y").' ('.$programmname.')<br>Weitere Informationen dazu findest du <a href="https://haslicevi.ch/agenda">hier</a>.<br>Falls du dann verhindert sein solltest, kannst du dich <a href="https://haslicevi.ch/my">hier</a> abmelden.</p><p>Bis bald!</p>');
                //         $sentMail->setDate(date_create()->format('Y-m-d H:i:s'));

                //     $entityManager = $this->getDoctrine()->getManager();
                //         $entityManager->persist($sentMail);
                //         $entityManager->flush();

                $email = new MailPostausgang();
                    $email->setAn($mitglied->getMail());
                    $email->setBetreff('CEVI Reminder');
                    $email->setTitel($programm[0]->getVon()->format("d.m.Y").' '.$programmname);
                    $email->setNachricht('<p>Hallo '.$name.'</p><p>Demnächst findet ein weiteres CEVI-Programm statt: '.$programm[0]->getVon()->format("d.m.Y").' ('.$programmname.')<br>Weitere Informationen dazu findest du <a href="https://haslicevi.ch/agenda">hier</a>.<br>Falls du dann verhindert sein solltest, kannst du dich <a href="https://haslicevi.ch/my">hier</a> abmelden.</p><p>Bis bald!</p>');
                    $email->setTimestamp(date_create()->format('Y-m-d H:i:s'));

                    $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($email);
                        $entityManager->flush();
            }

            return new Response('mails versendet.',200);
        } else {
            return new Response('nichts versendet.',200);
        }
    }
}
