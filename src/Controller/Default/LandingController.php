<?php

namespace App\Controller\Default;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
class LandingController extends AbstractController
{
    /**
     * @Route("/", name="landing")
     * @return Response
     */
    public function landing(): Response
    {
        return $this->render('landing.html.twig');
    }
}
