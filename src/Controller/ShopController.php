<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Shop;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ShopController extends AbstractController {
    /**
     * @Route("/shop", name="shop")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $produkte = $this->getDoctrine()->getRepository(Shop::class)->findAll();

        return $this->render('shop/index.html.twig', [
            'produkte' => $produkte,
        ]);
    }

    /**
     * @Route("/shop/new", name="shop_new")
     */
    public function new(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $produkt = new Shop();

        $form = $this->createFormBuilder($produkt)
            ->add('idParent', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Parent-ID (eigene ID falls kein Parent)',
                'required' => false,
            ])
            ->add('artikelNr', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Artikelnummer',
                'required' => true,
            ])
            ->add('config', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Spezifikation',
                'required' => false,
            ])
            ->add('bezeichnungKurz', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Kurze Bezeichnung',
                'required' => true,
            ])
            ->add('bezeichnungLang', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Lange Bezeichnung',
                'required' => false,
            ])
            ->add('lager', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Lagerbestand',
                'required' => true,
            ])
            ->add('preis', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Verkaufspreis',
                'required' => true,
            ])
            ->add('verkauf', CheckboxType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Wird verkauft',
                'required' => false,
            ])
            ->add('einkaufspreis', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Einkaufspreis',
                'required' => false,
            ])
            ->add('lieferant', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Lieferant',
                'required' => false,
            ])
            ->add('geschlecht', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Geschlecht',
                'required' => true,
            ])
            ->add('reorder', CheckboxType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Wird neu bestellt',
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

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'Das Produkt wurde gespeichert.');
        }

        return $this->render('shop/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/shop/edit/{id}", name="shop_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $produkt = $this->getDoctrine()->getRepository(Shop::class)->find($id);

        $form = $this->createFormBuilder($produkt)
            ->add('id', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'ID',
                'disabled' => true,
            ])
            ->add('idParent', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Parent-ID (eigene ID falls kein Parent)',
                'required' => false,
            ])
            ->add('artikelNr', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Artikelnummer',
                'required' => true,
            ])
            ->add('config', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Spezifikation',
                'required' => false,
            ])
            ->add('bezeichnungKurz', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Kurze Bezeichnung',
                'required' => true,
            ])
            ->add('bezeichnungLang', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Lange Bezeichnung',
                'required' => false,
            ])
            ->add('lager', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Lagerbestand',
                'required' => true,
            ])
            ->add('preis', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Verkaufspreis',
                'required' => true,
            ])
            ->add('verkauf', CheckboxType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Wird verkauft',
                'required' => false,
            ])
            ->add('einkaufspreis', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Einkaufspreis',
                'required' => false,
            ])
            ->add('lieferant', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Lieferant',
                'required' => false,
            ])
            ->add('geschlecht', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Geschlecht',
                'required' => true,
            ])
            ->add('reorder', CheckboxType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => 'Wird neu bestellt',
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

            $this->addFlash('success', 'Das Produkt wurde erfolgreich gespeichert.');
        }

        return $this->render('shop/edit.html.twig', [
            'form' => $form->createView(),
            'produkt' => $produkt,
        ]);
    }

    /**
     * @Route("/shop/delete/{id}", name="shop_delete")
     */
    public function delete($id): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $mitglied = $this->getDoctrine()->getRepository(Shop::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($mitglied);
        $entityManager->flush();

        return $this->redirectToRoute('shop');
    }
}
