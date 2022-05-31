<?php

namespace App\Controller;

use App\Entity\UserLog;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController {
    /**
     * @Route("/log", name="log")
     */
    public function index(): Response {
		$this->denyAccessUnlessGranted('ROLE_ADMIN');

        $log = file_get_contents('../var/log/prod.log');
        $log = explode(PHP_EOL, $log);
        $log = array_filter($log);

        $logs = array(); $i = 0;
        foreach($log as $l) {
            $date = explode(']', (explode('[', $l)[1]))[0];
            $content = explode(']', $l);
            unset($content[0]);
            $content = implode(']', $content);
            $content = substr($content, 1);
            $type = explode(':', $content)[0];
            $content = explode(':', $content);
            unset($content[0]);
            $content = implode(':', $content);
            $content = substr($content, 1);

            $logs[$i]['date'] = date('d.m.Y H:i:s', strtotime($date));
            $logs[$i]['dateOrder'] = date('YmdHis', strtotime($date));
            $logs[$i]['type'] = $type;
            $logs[$i]['contentShort'] = substr($content, 0, 100).'...';
            $logs[$i]['content'] = $content;
            $i++;
        }

        $logs = array_reverse($logs);

        return $this->render('log/index.html.twig', [
            'log' => $logs,
        ]);
    }

    /**
     * @Route("/userlog/{csrf}/{user}/{path}/{controller}", name="userlog_api")
     */
    public function userlogApi($csrf, $user, $path, $controller): Response {
        $log = new UserLog();
        $log->setCsrf($csrf);
        $log->setUser($user);
        $log->setPath(urldecode($path));
        $log->setController(urldecode($controller));
        $log->setDatetime(date("Y-m-d H:i:s"));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($log);
        $entityManager->flush();
        
        return new Response('ok', 200, array('Content-Type' => 'text/html'));
    }

    /**
     * @Route("/userlog", name="userlog")
     */
    public function userlog(): Response {
		$this->denyAccessUnlessGranted('ROLE_ADMIN');

        $logs = $this->getDoctrine()
                    ->getRepository(UserLog::class)->createQueryBuilder('p')
                    ->groupby('p.csrf')
                    ->select('p.user, p.csrf, p.datetime, count(p.controller) as openedControllers')
                    ->having('count(p.csrf) >= 1')
                    ->orderBy('p.datetime', 'DESC')
                    ->getQuery()->getResult();

        return $this->render('log/userlog.html.twig', [
            'log' => $logs,
        ]);
    }

    /**
     * @Route("/userlog/details/{csrf}", name="userlog_details")
     */
    public function userlogDetails($csrf): Response {
		$this->denyAccessUnlessGranted('ROLE_ADMIN');

        $logs = $this->getDoctrine()
                    ->getRepository(UserLog::class)->createQueryBuilder('p')
                    ->where('p.csrf = \''.$csrf.'\'')
                    ->orderBy('p.datetime', 'DESC')
                    ->getQuery()->getResult();

        return $this->render('log/userlogDetails.html.twig', [
            'log' => $logs,
        ]);
    }
}
