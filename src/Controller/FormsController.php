<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormsController extends AbstractController {
    private $gesperrteSeiten = [
        'Agenda',
        'Bildergalerie',
        'CEVI Niederhasli Niederglatt',
        'Definitive Anmeldung',
        'Dokumente',
        'Error 404',
        'Gönner',
        'Infos',
        'Impressum',
        'KGH Raumbenutzung',
        'Kontakt',
        'MyHaslicevi',
        'News',
        'Newsletter Archiv',
        'Schnuppern',
        'Über uns',
    ];

    private function getSeiten() {
        $json = file_get_contents('https://haslicevi.ch/wp-json/wp/v2/pages?per_page=50');
        $json = json_decode($json);

        $seiten = array();

        foreach($json as $k => $v) {
            $seiten[$v->id] = $v->title->rendered;
        }

        foreach($this->gesperrteSeiten as $sperrseite) {
            unset($seiten[array_search($sperrseite, $seiten)]);
        }

        asort($seiten);
        
        return $seiten;
    }

    /**
     * @Route("/forms", name="forms")
     */
    public function index(): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('forms/index.html.twig', [
            'json' => $this->getSeiten(),
        ]);
    }
}
