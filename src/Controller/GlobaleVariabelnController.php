<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\GlobaleVariabeln;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class GlobaleVariabelnController extends AbstractController {
    /**
     * @Route("/globvar", name="globale_variabeln")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_ROOT');

        $var = $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->findBy(array(), array('k' => 'ASC'));

        return $this->render('globale_variabeln/index.html.twig', [
            'var' => $var,
        ]);
    }

    /**
     * @Route("/globvar/edit/{id}", name="globale_variabeln_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_ROOT');

        $var = $this->getDoctrine()->getRepository(GlobaleVariabeln::class)->find($id);
        
        $form = $this->createFormBuilder($var)
            ->add('k', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Schlüssel',
                'disabled' => true,
            ])
            ->add('v', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Wert',
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

            $this->addFlash('success', 'Die Variable wurde erfolgreich gespeichert.');
        }

        return $this->render('globale_variabeln/edit.html.twig', [
            'form' => $form->createView(),
            'var' => $var,
        ]);
    }
}
