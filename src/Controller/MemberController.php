<?php

namespace App\Controller;

use App\Repository\MemberRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends AbstractController
{
    /**
     * @Route("/", name="member_landing")
     */
    public function landing(Request $request, MemberRepository $memberRepository): RedirectResponse|Response
    {

        if($request->getMethod() == "POST" && $this->isCsrfTokenValid('member_merchandise', $request->request->get('_token'))) {

            $number = $request->request->get('number');
            $birthday = new \DateTime($request->request->get('birthday'));

            $member = $memberRepository->findOneBy(['number' => $number, 'birthday' => $birthday]);

            if($member == null) {
                $this->addFlash('danger', 'Er kan geen lid worden gevonden met dit lidnummer of geboortedatum!');
                return $this->redirectToRoute('member_landing');
            }

            $session = $request->getSession();
            $session->set('member', $member);

            return $this->redirectToRoute('member_merchandise');

        }

        return $this->render('landing.html.twig');
    }

    /**
     * @Route("/merchandise-shop", name="member_merchandise")
     */
    public function shop(Request $request) {

        $session = $request->getSession();
        $member = $session->get('member');

        if($member == null) {
            $this->addFlash('danger', 'Je bent niet ingelogd. Log opnieuw in met je geboortedatum en lidnummer!');
            return $this->redirectToRoute('member_landing');
        }

        return $this->render('shop.html.twig');
    }
}
