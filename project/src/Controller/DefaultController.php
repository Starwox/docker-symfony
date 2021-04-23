<?php
/**
 * Created by PhpStorm.
 * User: starwox
 * Date: 15/03/2021
 * Time: 19:46
 */

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Charge;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;



class DefaultController extends AbstractController
{

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }

    /**
     * @Route("/ping/mailer", name="mailer")
     */
    public function mailChimp()
    {
        return new Response("Hello World, MailChimp work find");
    }

    /**
     * @Route("/stripe", name="stripe_billing")
     */
    public function Stripe() {
        /*Info Stripe:
            curl https://api.stripe.com/v1/billing_portal/sessions \
            -u sk_test_51IiMoIATaLjBTLoSd7xkbIiLv7TrDXNjMdiw6oMGmz5EAaEtQQk5puZkWIj29KYFxodkwpBSeNX7biX4oRsBMLB000Fic84Lt0: \
            -d customer=cus_JLX6tKkYb6hPKi \
            -d return_url="http://localhost:8741/stripe"
        End info */
        $url = "https://billing.stripe.com/session/_JLkZekrnN5uzCbjbHjBLM2HxMyvwT8G";

        return $this->render('bo/index.html.twig', ['link_stripe' => $url]);
    }
}