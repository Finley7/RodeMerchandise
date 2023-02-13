<?php

namespace App\Controller\Member;

use App\Entity\Member;
use App\Entity\Order;
use App\Repository\MemberRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberController extends AbstractController
{
    /**
     * @var MemberRepository
     */
    private MemberRepository $memberRepository;
    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @param MemberRepository $memberRepository
     * @param OrderRepository $orderRepository
     * @param ProductRepository $productRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(MemberRepository $memberRepository, OrderRepository $orderRepository, ProductRepository $productRepository, TranslatorInterface $translator)
    {
        $this->memberRepository = $memberRepository;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="member_landing")
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function landing(Request $request): RedirectResponse|Response
    {

        if($request->getMethod() == "POST" && $this->isCsrfTokenValid('member_merchandise', $request->request->get('_token'))) {

            $number = $request->request->get('number');
            $birthday = new DateTime($request->request->get('birthday'));

            $member = $this->memberRepository->findOneBy(['number' => $number, 'birthday' => $birthday]);

            if($member == null) {
                $this->addFlash('danger', $this->translator->trans('landing.flash.member_not_found'));
                return $this->redirectToRoute('member_landing');
            }

            $session = $request->getSession();
            $session->set('member', $member);

            $orders = $this->orderRepository->findOrderByMember($member);
            $route = $orders == null ? 'member_merchandise' : 'order_complete';

            return $this->redirectToRoute($route);

        }

        return $this->render('member/landing.html.twig');
    }

    /**
     * @Route("/merchandise-shop", name="member_merchandise")
     * @throws NonUniqueResultException
     */
    public function shop(Request $request): Response {

        $member = $this->_checkLogin($request);
        $member = $this->memberRepository->find($member->getId());

        if($this->orderRepository->findOrderByMember($member) != null) {
            return $this->redirectToRoute('order_complete');
        }

        $order = new Order();
        $order->setCustomer($member);

        if($request->getMethod() == "POST" && $this->isCsrfTokenValid('order_merchandise', $request->request->get('_token'))) {

            $product = $this->productRepository->find($request->request->get('item'));
            $order->setProduct($product);

            $this->orderRepository->save($order, true);

            $this->addFlash('success', $this->translator->trans('shop.flash.success'));
            return $this->redirectToRoute('order_complete');
        }

        return $this->render('member/shop.html.twig', ['member' => $member, 'products' => $this->productRepository->findAll()]);
    }

    /**
     * @Route("/complete", name="order_complete")
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function complete(Request $request): Response
    {

        $member = $this->_checkLogin($request);
        $order = $this->orderRepository->findOrderByMember($member);

        return $this->render('member/complete.html.twig', ['order' => $order, 'member' => $member]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Member
     */
    private function _checkLogin(Request $request) : RedirectResponse|Member {

        $session = $request->getSession();
        $member = $session->get('member');

        if($member == null) {
            $this->addFlash('danger', $this->translator->trans('general.flash.not_logged_in'));
            return $this->redirectToRoute('member_landing');
        }

        return $member;
    }
}
