<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Newsletter;
use App\Entity\Mailinglisten;
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

class NewsletterController extends AbstractController {
    private function getListen() {
        $listen = [
            'Aktivmitglieder (automatisch generiert)' => 1,
            'Gönner (automatisch generiert)' => 2,
            'Aktivmitglieder & Gönner (automatisch generiert)' => 3,
        ];

        foreach($this->getDoctrine()->getRepository(Mailinglisten::class)->findAll() as $k => $v) {
            $listen[$v->getName()] = 'm'.$v->getId();
        }

        return $listen;
    }

    private function getAdressen($doctrine, $liste) {
        if($liste == 1){
            $mails = $doctrine->getRepository(Mitglieder::class)->createQueryBuilder('p')
                                ->where('p.status = 1')
                                ->orWhere('p.status = 2')
                                ->getQuery()->getResult();
                                foreach($mails as $k => $v) {
                                    $adressen[] = $v->getMail();
                                }
        } elseif($liste == 2) {
            $mails = $doctrine->getRepository(Mitglieder::class)->createQueryBuilder('p')
                                ->where('p.status = 3')
                                ->getQuery()->getResult();
                                foreach($mails as $k => $v) {
                                    $adressen[] = $v->getMail();
                                }
        } elseif($liste == 3) {
            $mails = $doctrine->getRepository(Mitglieder::class)->createQueryBuilder('p')
                                ->where('p.status = 1')
                                ->orWhere('p.status = 2')
                                ->orWhere('p.status = 3')
                                ->getQuery()->getResult();
                                foreach($mails as $k => $v) {
                                    $adressen[] = $v->getMail();
                                }
        } else {
            $mails = $this->getDoctrine()->getRepository(Mailinglisten::class)->find(str_replace('m','',$liste))->getMail();
            $adressen = explode(",", $mails);
        }

        return $adressen;
    }

    private $status = [
        'In Bearbeitung' => 1,
        'Versendet' => 2,
        'In Bearbeitung (aus Vorlage)' => 3,
        'Vorlage' => 4,
    ];

    /**
     * @Route("/newsletter", name="newsletter")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $newsletter = $this->getDoctrine()->getRepository(Newsletter::class)->createQueryBuilder('p')
                            ->where('p.status < 4')
                            ->orderBy('p.datum', 'DESC')
                            ->getQuery()->getResult();

        $postausgang = $this->getDoctrine()->getRepository(MailPostausgang::class)->createQueryBuilder('p')
                            ->select('count(p.id)')
                            ->getQuery()
                            ->getSingleScalarResult();

        return $this->render('newsletter/index.html.twig', [
            'newsletter' => $newsletter,
            'postausgang' => $postausgang,
            'listen' => array_flip($this->getListen()),
            'status' => array_flip($this->status),
        ]);
    }

    /**
     * @Route("/newsletter/new", name="newsletter_new")
     */
    public function new(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $newsletter = new Newsletter();

        $form = $this->createFormBuilder($newsletter)
            ->add('liste', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->getListen(),
                'label' => 'Empfänger',
            ])
            ->add('betreff', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
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
            $data->setStatus(1);
            $data->setDatum(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'Der Newsletter wurde gespeichert. Um den Inhalt zu schreiben musst du zurück zur Liste gehen und dort auf "Inhalt bearbeiten" klicken.');
        }

        return $this->render('newsletter/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/vorlage", name="newsletter_vorlage")
     */
    public function vorlage(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $versendet = $this->getDoctrine()->getRepository(Newsletter::class)->createQueryBuilder('p')
                            ->where('p.status < 4')
                            ->orderBy('p.datum', 'DESC')
                            ->getQuery()->getResult();

        $vorlagen = $this->getDoctrine()->getRepository(Newsletter::class)->createQueryBuilder('p')
                            ->where('p.status = 4')
                            ->orderBy('p.datum', 'DESC')
                            ->getQuery()->getResult();

        // $vorlagen = $this->getDoctrine()->getRepository(Newsletter::class)->findBy(array(), array('datum' => 'DESC'));

        return $this->render('newsletter/vorlage.html.twig', [
            'versendet' => $versendet,
            'vorlagen' => $vorlagen,
            'status' => array_flip($this->status),
            'listen' => array_flip($this->getListen()),
        ]);
    }

    /**
     * @Route("/newsletter/vorlage/neu/{id}", name="newsletter_vorlage_neu")
     */
    public function vorlageNeu($id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $vorlage = $this->getDoctrine()->getRepository(Newsletter::class)->find($id);
        
        $newsletter = new Newsletter();

        $newsletter->setDatum(new \DateTime());
        $newsletter->setStatus(3);
        $newsletter->setListe($vorlage->getListe());
        $newsletter->setBetreff($vorlage->getBetreff());
        $newsletter->setTitel($vorlage->getTitel());
        $newsletter->setNachricht($vorlage->getNachricht());
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($newsletter);
        $entityManager->flush();

        return $this->redirectToRoute('newsletter');
    }

    /**
     * @Route("/newsletter/edit/{id}", name="newsletter_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $newsletter = $this->getDoctrine()->getRepository(Newsletter::class)->find($id);

        $form = $this->createFormBuilder($newsletter)
            ->add('liste', ChoiceType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->getListen(),
                'label' => 'Empfänger',
            ])
            ->add('betreff', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
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

            $this->addFlash('success', 'Der Newsletter wurde gespeichert. Um den Inhalt zu schreiben musst du zurück zur Liste gehen und dort auf "Inhalt bearbeiten" klicken.');
        }

        return $this->render('newsletter/edit.html.twig', [
            'form' => $form->createView(),
            'newsletter' => $newsletter,
        ]);
    }

    /**
     * @Route("/newsletter/write/{id}", name="newsletter_write")
     * Method({"GET", "POST"})
     */
    public function write(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $newsletter = $this->getDoctrine()->getRepository(Newsletter::class)->find($id);

        $form = $this->createFormBuilder($newsletter)
            ->add('titel', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Titel (Überschrift der Mail)',
                'required' => true,
            ])
            ->add('nachricht', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => true,
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

            $this->addFlash('success', 'Der Newsletter wurde gespeichert.');
        }

        return $this->render('newsletter/write.html.twig', [
            'form' => $form->createView(),
            'newsletter' => $newsletter,
        ]);
    }

    /**
     * @Route("/newsletter/vorschau/{id}", name="newsletter_vorschau")
     */
    public function vorschau($id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $newsletter = $this->getDoctrine()->getRepository(Newsletter::class)->find($id);

        return $this->render('newsletter/vorschau.html.twig', [
            'newsletter' => $newsletter,
        ]);
    }

    /**
     * @Route("/newsletter/preview/{id}/{leiter}", name="newsletter_preview")
     */
    public function preview($id, $leiter): Response {
        $newsletter = $this->getDoctrine()->getRepository(Newsletter::class)->find($id);

        return $this->render('mail.html.twig', [
            'titel' => $newsletter->getTitel(),
            'nachricht' => $newsletter->getNachricht(),
            'email' => [
                'to' => [
                    0 => [
                        'address' => $leiter.'@haslicevi.ch'
                    ]
                ]
            ]
        ]);
    }

    /**
     * @Route("/newsletter/delete/{id}", name="newsletter_delete")
     */
    public function delete($id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $newsletter = $this->getDoctrine()->getRepository(Newsletter::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($newsletter);
        $entityManager->flush();

        return $this->redirectToRoute('newsletter');
    }

    /**
     * @Route("/newsletter/send/{id}", name="newsletter_send")
     */
    public function send($id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $newsletter = $this->getDoctrine()->getRepository(Newsletter::class)->find($id);

        if($newsletter->getStatus() == 2 and !in_array('ROLE_ROOT', $this->getUser()->getRoles())) {
            $msg = array('danger', 'Achtung', 'Der Newsletter wurde bereits versendet. Dieser Vorgang kann nicht wiederholt werden.');
            $sendable = false;
        } elseif(!in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $msg = array('warning', 'Info', 'Newsletter können nur von der Abteilungsleitung oder dem Vorstand versendet werden. Bitte wende dich an sie.');
            $sendable = false;
        } else {
            $msg = array('warning', 'Info', 'Bist du dir sicher, dass du den folgenden Newsletter senden möchtest?');
            $sendable = true;
        }

        return $this->render('newsletter/send.html.twig', [
            'newsletter' => $newsletter,
            'msg' => $msg,
            'sendable' => $sendable,
            'an' => array_flip($this->getListen())[$newsletter->getListe()],
        ]);
    }

   /**
     * @Route("/newsletter/sendDefinitively/{id}", name="newsletter_send_definitively")
     */
    public function sendDefinitively($id, MailerInterface $mailer): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $newsletter = $this->getDoctrine()->getRepository(Newsletter::class)->find($id);

        $adressen = $this->getAdressen($this->getDoctrine(), $newsletter->getListe());

        foreach($adressen as $mail) {
            if(!empty($mail) and isset($mail)) {
                // $email = (new TemplatedEmail())
                //     ->from(new Address('no-reply@haslicevimail.ch', 'CEVI Niederhasli Niederglatt'))
                //     ->to($mail)
                //     ->subject($newsletter->getBetreff())
                //     ->htmlTemplate('mail.html.twig')
                //     ->context([
                //         'titel' => $newsletter->getTitel(),
                //         'nachricht' => $newsletter->getNachricht(),
                //     ]);
                // $mailer->send($email);

                // $sentMail = new SentMails();
                //     $sentMail->setMailFrom('no-reply@haslicevimail.ch');
                //     $sentMail->setMailTo($mail);
                //     $sentMail->setSubject($newsletter->getBetreff());
                //     $sentMail->setTitel($newsletter->getTitel());
                //     $sentMail->setNachricht($newsletter->getNachricht());
                //     $sentMail->setDate(date_create()->format('Y-m-d H:i:s'));

                // $entityManager = $this->getDoctrine()->getManager();
                //     $entityManager->persist($sentMail);
                //     $entityManager->flush();

                $email = new MailPostausgang();
                    $email->setAn($mail);
                    $email->setBetreff($newsletter->getBetreff());
                    $email->setTitel($newsletter->getTitel());
                    $email->setNachricht($newsletter->getNachricht());
                    $email->setTimestamp(date_create()->format('Y-m-d H:i:s'));

                $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($email);
                    $entityManager->flush();
            }
        }

        $newsletter->setDatum(new \DateTime());
        $newsletter->setStatus(2);
        $this->getDoctrine()->getManager()->flush();

        return $this->render('newsletter/sendDefinitively.html.twig', [
            'newsletter' => $newsletter,
            'adressen' => $adressen,
        ]);
    }

    /**
     * @Route("/mail/{mail}/{titel}/{msg}", name="mail_view")
     */
    public function mailView($mail,$titel,$msg): Response {
        return $this->render('mail.html.twig', [
            'titel' => $titel,
            'nachricht' => $msg,
            'email' => [
                'to' => [
                    0 => [
                        'address' => $mail
                    ]
                ]
            ]
        ]);
    }

    /**
     * @Route("/newsletter/mailingliste", name="mailingliste")
     */
    public function mailingliste(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $liste = $this->getDoctrine()->getRepository(Mailinglisten::class)->createQueryBuilder('p')
                            ->orderBy('p.name', 'ASC')
                            ->getQuery()->getResult();

        return $this->render('newsletter/mailingliste.html.twig', [
            'listen' => $liste
        ]);
    }

    /**
     * @Route("/newsletter/mailingliste/new", name="mailingliste_new")
     */
    public function mailinglisteNew(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $newsletter = new Mailinglisten();

        $form = $this->createFormBuilder($newsletter)
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('mail', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Mail-Adressen (NUR durch Komma getrennt)',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'Die Mailingliste wurde gespeichert.');
        }

        return $this->render('newsletter/mailingliste_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newsletter/mailingliste/edit/{id}", name="mailingliste_edit")
     * Method({"GET", "POST"})
     */
    public function mailinglisteEdit(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $mailingliste = $this->getDoctrine()->getRepository(Mailinglisten::class)->find($id);

        $form = $this->createFormBuilder($mailingliste)
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('mail', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Mail-Adressen (NUR durch Komma getrennt)',
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

            $this->addFlash('success', 'Die Mailingliste wurde gespeichert.');
        }

        return $this->render('newsletter/mailingliste_edit.html.twig', [
            'form' => $form->createView(),
            'mailingliste' => $mailingliste,
        ]);
    }

    /**
     * @Route("newsletter/api", name="newsletter_api")
     */
    public function api(Request $request) {
        $newsletter = $this->getDoctrine()
                        ->getRepository(Newsletter::class)->createQueryBuilder('p')
                        ->where('p.liste = 1')->andWhere('p.status = 2')
                        ->orWhere('p.liste = 3')->andWhere('p.status = 2')
                        ->orderBy('p.datum', 'DESC')
                        ->getQuery()->getResult();

        return $this->render('newsletter/api.html.twig', [
            'newsletter' => $newsletter,
        ]);
    }

    /**
     * @Route("newsletter/api/latest", name="newsletter_api_latest")
     */
    public function apiLatest(Request $request) {
        $newsletter = $this->getDoctrine()
                        ->getRepository(Newsletter::class)->createQueryBuilder('p')
                        ->where('p.liste = 1')->andWhere('p.status = 2')
                        ->orWhere('p.liste = 3')->andWhere('p.status = 2')
                        ->orderBy('p.datum', 'DESC')
                        ->setMaxResults(2)
                        ->getQuery()->getResult();

        return $this->render('newsletter/apiLatest.html.twig', [
            'newsletter' => $newsletter,
        ]);
    }

    /**
     * @Route("public/newsletter/{id}", name="newsletter_public")
     */
    public function public($id): Response {
        // $newsletter = $this->getDoctrine()->getRepository(Newsletter::class)->find($id);
        $newsletter = $this->getDoctrine()
                        ->getRepository(Newsletter::class)->createQueryBuilder('p')
                        ->where('p.liste = 1')->andWhere('p.id = '.$id)->andWhere('p.status = 2')
                        ->orWhere('p.liste = 3')->andWhere('p.id = '.$id)->andWhere('p.status = 2')
                        ->getQuery()->getResult()[0];

        return $this->render('mail.html.twig', [
            'titel' => $newsletter->getTitel(),
            'nachricht' => $newsletter->getNachricht(),
            'email' => [
                'to' => [
                    0 => [
                        'address' => 'info@haslicevi.ch'
                    ]
                ]
            ]
        ]);
    }
}
