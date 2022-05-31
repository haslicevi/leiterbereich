<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Dokumente;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DokumenteController extends AbstractController {
    /**
     * @Route("/dokumente", name="dokumente")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $dokumente = $this->getDoctrine()->getRepository(Dokumente::class)->findBy(array(), array('datum' => 'DESC'));

        return $this->render('dokumente/index.html.twig', [
            'dokumente' => $dokumente,
        ]);
    }

    /**
     * @Route("/dokumente/upload/1", name="dokumente_upload1")
     */
    public function upload1(): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('dokumente/upload1.html.twig', [
            'controller_name' => 'DokumenteController',
        ]);
    }

    /**
     * @Route("/dokumente/upload/2/{filename}/{newFilename}", name="dokumente_upload2")
     */
    public function upload2($filename, $newFilename): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $dokument = new Dokumente();

        $dokument->setDatum(new \DateTime());
        $dokument->setUrl($newFilename);
        $dokument->setName($filename);
        $dokument->setMyhaslicevi(false);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($dokument);
        $entityManager->flush();

        return $this->redirectToRoute('dokumente_upload3', array('file' => $newFilename));
    }

    /**
     * @Route("/dokumente/upload/3/{file}", name="dokumente_upload3")
     */
    public function upload3(Request $request, $file): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $dokument = $this->getDoctrine()->getRepository(Dokumente::class)->findBy(array('url' => $file))[0];

        $form = $this->createFormBuilder($dokument)
            ->add('url', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'disabled' => true,
                'label' => 'Dokument',
            ])
            ->add('datum', DateType::class, [
                'attr' => ['class' => 'form-control mb-3 js-datepicker'],
            ])
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => true,
            ])
            ->add('myhaslicevi', CheckboxType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label'    => 'Im MyHaslicevi unter "Dokumente" zeigen',
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

            return $this->redirectToRoute('dokumente');
        }

        return $this->render('dokumente/upload3.html.twig', [
            'form' => $form->createView(),
            'dokument' => $dokument,
        ]);
    }

    /**
     * @Route("/dokumente/edit/{id}", name="dokumente_edit")
     */
    public function edit(Request $request, $id): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $dokument = $this->getDoctrine()->getRepository(Dokumente::class)->find($id);

        $form = $this->createFormBuilder($dokument)
            ->add('url', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'disabled' => true,
                'label' => 'Dokument',
            ])
            ->add('datum', DateType::class, [
                'attr' => ['class' => 'form-control mb-3 js-datepicker'],
            ])
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => true,
            ])
            ->add('myhaslicevi', CheckboxType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label'    => 'Im MyHaslicevi unter "Dokumente" zeigen',
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

            $this->addFlash('success', 'Das Dokument wurde erfolgreich gespeichert.');
        }

        return $this->render('dokumente/edit.html.twig', [
            'form' => $form->createView(),
            'dokument' => $dokument,
        ]);
    }

    /**
     * @Route("/dokumente/delete/{id}", name="dokumente_delete")
     */
    public function delete($id): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $dokument = $this->getDoctrine()->getRepository(Dokumente::class)->find($id);

        file_get_contents('https://docs.haslicevi.ch/login_haslicevi_ch_file_delete.php?token=lUyANaQEdgFz68TvSo34&file='.$dokument->getUrl());
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($dokument);
        $entityManager->flush();

        return $this->redirectToRoute('dokumente');
    }
}
