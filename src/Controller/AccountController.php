<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController {
    private $rollen = [
        'Normaler Benutzer (standard)' => 'ROLE_USER',
        'Lädeli-Verwalter' => 'ROLE_SHOP',
        'Finanzverwalter' => 'ROLE_FINANCE',
        'Administrator' => 'ROLE_ADMIN',
        'Hacker (Achtung !!)' => 'ROLE_ROOT',
    ];

    private $rollenKurz = [
        'Benutzer' => 'ROLE_USER',
        'Lädeli-Verwalter' => 'ROLE_SHOP',
        'Finanzverwalter' => 'ROLE_FINANCE',
        'Administrator' => 'ROLE_ADMIN',
        'Hacker' => 'ROLE_ROOT',
    ];

    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder) {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/account", name="account")
     */
    public function index(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $id = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        
        $form = $this->createFormBuilder($user)
            ->add('user', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'disabled' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Neues Passwort',
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
            $data->setPassword($this->passwordEncoder->encodePassword(
                $data,$data->getPassword()
            ));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash('success', 'Das Passwort wurde erfolgreich geändert.');
        }

        return $this->render('account/pw.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/account/new", name="account_new")
     */
    public function new(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        
        $form = $this->createFormBuilder($user)
            ->add('user', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => "Name",
            ])
            ->add('password', PasswordType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => "Passwort",
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();

            // Rolle (Standardbenutzer) eintragen
            $data->setRoles(array("ROLE_USER"));

            // Passwort verschlüsseln
            $data->setPassword($this->passwordEncoder->encodePassword(
                $data,$data->getPassword()
            ));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'Der Nutzer wurde angelegt.');
        }

        return $this->render('account/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/account/list", name="account_list")
     */
    public function list(): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $this->getDoctrine()->getRepository(User::class)->findBy(array(), array('user' => 'ASC'));

        return $this->render('account/index.html.twig', [
            'users' => $users,
            'rollen' => array_flip($this->rollenKurz),
        ]);
    }

    /**
     * @Route("/account/setpw/{id}", name="account_setpw")
     * Method({"GET", "POST"})
     */
    public function setpw(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        
        $form = $this->createFormBuilder($user)
            ->add('user', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'disabled' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Neues Passwort',
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

            $data->setPassword($this->passwordEncoder->encodePassword(
                $data,$data->getPassword()
            ));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash('success', 'Das Passwort wurde erfolgreich gesetzt.');
        }

        return $this->render('account/setpw.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/account/edit/{id}", name="account_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        
        $form = $this->createFormBuilder($user)
            ->add('user', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rolle',
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->rollen,
                'multiple' => true,
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

            $this->addFlash('success', 'Das Profil wurde erfolgreich gespeichert.');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/account/delete/{id}", name="account_delete")
     */
    public function delete($id): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('account_list');
    }
}
