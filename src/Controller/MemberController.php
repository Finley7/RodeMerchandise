<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Order;
use App\Repository\MemberRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
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
    public function landing(Request $request, MemberRepository $memberRepository, OrderRepository $orderRepository): RedirectResponse|Response
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

            $orders = $orderRepository->findOrderByMember($member);
            $route = $orders == null ? 'member_merchandise' : 'order_complete';

            return $this->redirectToRoute($route);

        }

        return $this->render('landing.html.twig');
    }

    /**
     * @Route("/merchandise-shop", name="member_merchandise")
     */
    public function shop(Request $request, ProductRepository $productRepository, OrderRepository $orderRepository, MemberRepository $memberRepository): Response {

        $member = $this->_checkLogin($request);
        $member = $memberRepository->find($member->getId());

        if($orderRepository->findOrderByMember($member) != null) {
            return $this->redirectToRoute('order_complete');
        }

        $order = new Order();
        $order->setCustomer($member);

        if($request->getMethod() == "POST" && $this->isCsrfTokenValid('order_merchandise', $request->request->get('_token'))) {

            $product = $productRepository->find($request->request->get('item'));
            $order->setProduct($product);

            $orderRepository->save($order, true);

            $this->addFlash('success', 'De bestelling is geplaatst! Dankjewel!');
            return $this->redirectToRoute('order_complete');
        }

        return $this->render('shop.html.twig', ['member' => $member, 'products' => $productRepository->findAll()]);
    }

    /**
     * @Route("/complete", name="order_complete")
     * @param Request $request
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function complete(Request $request, OrderRepository $orderRepository) {

        $member = $this->_checkLogin($request);
        $order = $orderRepository->findOrderByMember($member);

        return $this->render('complete.html.twig', ['order' => $order, 'member' => $member]);
    }

    private function _checkLogin(Request $request) : RedirectResponse|Member {

        $session = $request->getSession();
        $member = $session->get('member');

        if($member == null) {
            $this->addFlash('danger', 'Je bent niet ingelogd. Log opnieuw in met je geboortedatum en lidnummer!');
            return $this->redirectToRoute('member_landing');
        }

        return $member;
    }
}
