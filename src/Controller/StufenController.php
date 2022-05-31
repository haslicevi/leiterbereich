<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Stufen;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class StufenController extends AbstractController {
    /**
     * @Route("/stufen", name="stufen")
     */
    public function index(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stufen = $this->getDoctrine()->getRepository(Stufen::class)->findBy(array(), array('jahrgaenge' => 'ASC'));

        return $this->render('stufen/index.html.twig', [
            'stufen' => $stufen,
        ]);
    }

    /**
     * @Route("/stufen/new", name="stufen_new")
     */
    public function new(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stufe = new Stufen();
        
        $form = $this->createFormBuilder($stufe)
            ->add('stufenname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('jahrgaenge', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => "Jahrgänge (durch Komma getrennt)",
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

            $this->addFlash('success', 'Die Stufe wurde angelegt.');
        }

        return $this->render('stufen/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/stufen/edit/{id}", name="stufen_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stufe = $this->getDoctrine()->getRepository(Stufen::class)->find($id);
        
        $form = $this->createFormBuilder($stufe)
            ->add('stufenname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('jahrgaenge', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => "Jahrgänge (durch Komma getrennt)",
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

            $this->addFlash('success', 'Die Stufe wurde erfolgreich gespeichert.');
        }

        return $this->render('stufen/edit.html.twig', [
            'form' => $form->createView(),
            'stufe' => $stufe,
        ]);
    }
}
