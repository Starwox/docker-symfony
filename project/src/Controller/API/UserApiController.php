<?php
/**
 * Created by PhpStorm.
 * User: starwox
 * Date: 19/04/2021
 * Time: 17:22
 */

namespace App\Controller\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use MailchimpMarketing;


/**
 * @Route("/api", name="users_api")
 */
class UserApiController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/all-users", name="get_all_users", methods={"GET"})
     * @Assert\Json(
     *     message = "You've entered an invalid Json."
     * )
     */
    public function getAllUsers()
    {
        $users = $this->em->getRepository(User::class)->findAll();
        return $this->json([
            "status" => 200,
            "data" => $users
            ]);
    }

    /**
     * @Route("/add-user", name="add_users", methods={"POST"})
     * @Assert\Json(
     *     message = "You've entered an invalid Json."
     * )
     */
    public function addUser(Request $request)
    {

        // DATA POST
        $email = $request->request->get('email');
        $plainPassword = $request->request->get('password');
        $firstName = $request->request->get('firstname');
        $lastname = $request->request->get('lastname');
        $age = $request->request->get('age');
        $job = $request->request->get('job');

        // CHECKER
        if (empty($firstName)) {
            $jsonId = json_decode(file_get_contents("php://input"), true);

            $firstName = $jsonId['firstName'];
        }

        if (empty($lastname)) {
            $jsonId = json_decode(file_get_contents("php://input"), true);

            $lastname = $jsonId['lastname'];
        }

        if (empty($email)) {
            $jsonId = json_decode(file_get_contents("php://input"), true);

            $email = $jsonId['email'];
        }

        if (empty($plainPassword)) {
            $jsonId = json_decode(file_get_contents("php://input"), true);

            $plainPassword = $jsonId['password'];
        }

        if (empty($age)) {
            $jsonId = json_decode(file_get_contents("php://input"), true);

            $age = $jsonId['age'];
        }

        if (empty($job)) {
            $jsonId = json_decode(file_get_contents("php://input"), true);

            $job = $jsonId['job'];
        }

        $password = password_hash($plainPassword, PASSWORD_DEFAULT);
        $apiKey = uniqid(rand(), true);

        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $apiKeyFinder = $userRepo->findBy([
            "apiKey" => $apiKey
        ]);

        if (!empty($apiKeyFinder))
            $apiKey = uniqid(rand(), true);

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setFirstname($firstName);
        $user->setLastname($lastname);
        $user->setAge($age);
        $user->setJob($job);
        $user->setApiKey($apiKey);

        $this->em->persist($user);
        $this->em->flush($user);

        //MAIL CHIMP
        $mailchimp = new \MailchimpMarketing\ApiClient();

        $mailchimp->setConfig([
            'apiKey' => '1b9525b5613ad19677cac075aab0f369-us1',
            'server' => 'us1'
        ]);
        $list_id = "d466a0c733";
        try {
             $mailchimp->lists->addListMember($list_id, [
                "email_address" => $email,
                "status" => "pending",
                "merge_fields" => [
                    "FNAME" => $firstName,
                    "LNAME" => $lastname
                ]
            ]);
        } catch (\MailchimpMarketing\ApiException $e) {
            echo $e->getMessage();
        }

        //Segment
        class_alias('Segment', 'Analytics');
        \Segment::init("kUZfCSvT2EmIFP4Z5mqVfbPrPVPeqpWN");

        \Segment::identify(array(
            "userId" => $user->getId(),
            "traits" => array(
                "email" => $email,
                "name" => $firstName . " " . $lastname,
                "friends" => 25
            )
        ));

        $response = new JsonResponse([
            "status" => 200,
            "data" => 'success'
        ]);

        return $response;
    }

    /**
     * @Route("/delete-user/{id}", name="delete_user", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function removeUser($id)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($id);

        if (empty($user)) {
            return $this->json([
                "status" => 400,
                "data" => "Not found"
            ]);
        }

        $this->em->remove($user);
        $this->em->flush();

        return $this->json([
            "status" => 200,
            "data" => "Deleted"
        ]);
    }


    /**
     * @Route("/edit-user/{id}", name="edit_user", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function editUser($id, Request $request)
    {
        $email = $request->request->get('email');
        $plainPassword = $request->request->get('password');
        $job = $request->request->get('$job');
        $firstName = $request->request->get('firstName');
        $lastname = $request->request->get('lastname');
        $age = $request->request->get('age');

        $password = password_hash( $plainPassword, PASSWORD_DEFAULT);

        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($id);

        if ($email !== $user->getEmail())
            $user->setEmail($email);

        if ($password !== $user->getPassword())
            $user->setPassword($password);

        if ($job !== $user->getJob())
            $user->setJob($job);

        if ($firstName !== $user->getFirstname())
            $user->setFirstname($firstName);

        if ($lastname !== $user->getLastname())
            $user->setLastname($lastname);

        if ($age !== $user->getAge())
            $user->setAge($age);

        $this->em->persist($user);
        $this->em->flush();

        return $this->json([
            "status" => 200,
            "data" => "success"
        ]);
    }

    /**
     * @Route("/login", name="login_user", methods={"POST"})
     * @Assert\Json(
     *     message = "You've entered an invalid Json."
     * )
     */
    public function login(Request $request)
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $user = $this->em->getRepository(User::class)->findBy([
            "email" => $email
        ]);

        $encoded = password_verify($password, $user[0]->getPassword());

        if (empty($user) OR !$encoded) {
            return $this->json([
                "status" => 404,
                "data" => "User not found or incorrect Password"
            ]);
        }

        $user[0]->setlastLog(new \DateTime("now"));
        $this->em->persist($user[0]);
        $this->em->flush();

        return $this->json([
            "status" => 200,
            "data" => [
                "id" => $user[0]->getId(),
                "email" => $user[0]->getEmail(),
                "active" => $user[0]->getActive(),
                "startedAt" => $user[0]->getStartedAt()->format('Y-m-d H:i:s'),
                "firstname" => $user[0]->getFirstname(),
                "lastname" => $user[0]->getLastname(),
                "age" => $user[0]->getAge(),
                "job" => $user[0]->getJob(),
            ],
            "api_key" => $user[0]->getApiKey()
        ]);
    }
}