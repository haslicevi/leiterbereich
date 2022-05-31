<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\GlobaleVariabeln;
use App\Entity\SentMails;
use App\Entity\MailserverStatus;
use App\Entity\MailPostausgang;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class MailserverController extends AbstractController {
    /**
     * @Route("/mailserver/mail-adressen", name="mailserver_mail_adressen")
     */
    public function mailAdressen(): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'MAILVERWALTER_USER'))->getV();
        $pw = $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findOneBy(array('k' => 'MAILVERWALTER_PASSWORD'))->getV();

        return $this->render('mailserver/mail-adressen.html.twig', [
            'user' => $user,
            'pw' => $pw,
        ]);
    }

    /**
     * @Route("/mailserver/sent", name="mailserver_sent")
     */
    public function sentMails(): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $mails = $this->getDoctrine()->getRepository(SentMails::class)->findAll();

        return $this->render('mailserver/sent.html.twig', [
            'mails' => $mails,
        ]);
    }

    /**
     * @Route("/mailserver/postausgang", name="mailserver_postausgang")
     */
    public function postausgang(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $mails = $this->getDoctrine()->getRepository(MailPostausgang::class)->findAll();

        return $this->render('mailserver/postausgang.html.twig', [
            'mails' => $mails,
        ]);
    }

    /**
     * @Route("/mailserver/sent/view/{id}", name="mailserver_view")
     */
    public function view($id): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $mail = $this->getDoctrine()->getRepository(SentMails::class)->find($id);

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
    }

    /**
     * @Route("/mailserver/api", name="mailserver_api")
     */
    public function api(): Response {
        $request = Request::createFromGlobals();

        $status = new MailserverStatus();
            $status->setDatum(date_create()->format('Y-m-d H:i:s'));
            $status->setContent(json_encode($request->request->all()));

        $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($status);
            $entityManager->flush();

        return new Response('ok.', 200, array('Content-Type' => 'text/html'));
    }

    /**
     * @Route("/mailserver/sendercron", name="mailserver_sendercron")
     */
    public function sendercron(MailerInterface $mailer): Response {
        $mails = $this->getDoctrine()->getRepository(MailPostausgang::class)->createQueryBuilder('p')
                        ->setMaxResults(16)
                        ->getQuery()->getResult();

        foreach($mails as $mail) {
            if(!empty($mail->getAn()) and $mail->getAn() !== null) {
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@haslicevimail.ch', 'CEVI Niederhasli Niederglatt'))
                    ->to($mail->getAn())
                    ->replyTo('info@haslicevi.ch')
                    ->subject($mail->getBetreff())
                    ->htmlTemplate('mail.html.twig')
                    ->context([
                        'titel' => $mail->getTitel(),
                        'nachricht' => $mail->getNachricht(),
                    ]);
                $mailer->send($email);

                $sentMail = new SentMails();
                    $sentMail->setMailFrom('no-reply@haslicevimail.ch');
                    $sentMail->setMailTo($mail->getAn());
                    $sentMail->setSubject($mail->getBetreff());
                    $sentMail->setTitel($mail->getTitel());
                    $sentMail->setNachricht($mail->getNachricht());
                    $sentMail->setDate(date_create()->format('Y-m-d H:i:s'));

                $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($sentMail);
                    // $entityManager->flush();

                    $entityManager->remove($mail);
                    $entityManager->flush();
            }
        }

        return new Response('ok.', 200, array('Content-Type' => 'text/html'));
    }

    /**
     * @Route("/x/test", name="test")
     */
    public function test(): Response {
        $request = Request::createFromGlobals();

        $status = new MailserverStatus();
            $status->setDatum(date_create()->format('Y-m-d H:i:s'));
            $status->setContent(json_encode($request->request->all()));

        // $entityManager = $this->getDoctrine()->getManager();
        //     $entityManager->persist($status);
        //     $entityManager->flush();

            return $this->render('test.html.twig', [
                'test' => json_encode($request->request->all()),
            ]);
    }
}
