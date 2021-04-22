<?php
/**
 * Created by PhpStorm.
 * User: starwox
 * Date: 20/04/2021
 * Time: 18:17
 */

namespace App\Controller\API;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/api", name="event_api")
 */
class EventApiController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/all-events", name="get_all_event", methods={"GET"})
     * @Assert\Json(
     *     message = "You've entered an invalid Json."
     * )
     */
    public function getAllEvents(): JsonResponse
    {
        $users = $this->em->getRepository(Event::class)->findAll();
        return $this->json([
            "status" => 200,
            "data" => $users
        ]);
    }

    /**
     * @Route("/add-event", name="add_event", methods={"POST"})
     * @Assert\Json(
     *     message = "You've entered an invalid Json."
     * )
     */
    public function addEvent(Request $request): JsonResponse
    {

        // DATA POST
        $title = $request->request->get('title');
        $startedAt = $request->request->get('startedAt');
        $endedAt = $request->request->get('endedAt');
        $description = $request->request->get('description');

        // CHECKER
        if (empty($title)) {
            $jsonId = json_decode(file_get_contents("php://input"), true);

            $title = $jsonId['title'];
        }

        if (empty($startedAt)) {
            $jsonId = json_decode(file_get_contents("php://input"), true);

            $startedAt = $jsonId['startedAt'];
        }

        if (empty($endedAt)) {
            $jsonId = json_decode(file_get_contents("php://input"), true);

            $endedAt = $jsonId['endedAt'];
        }

        if (empty($description)) {
            $jsonId = json_decode(file_get_contents("php://input"), true);

            $description = $jsonId['description'];
        }

        $event = new Event();
        $event->setTitle($title);
        $event->setStartedAt($startedAt);
        $event->setEndedAt($endedAt);
        $event->setDescription($description);

        $this->em->persist($event);
        $this->em->flush($event);

        $response = new JsonResponse([
            "status" => 200,
            "data" => 'success'
        ]);

        return $response;
    }

    /**
     * @Route("/delete-event/{id}", name="delete_event", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function removeEvent($id): JsonResponse
    {
        $repository = $this->getDoctrine()->getRepository(Event::class);
        $event = $repository->find($id);

        if (empty($event)) {
            return $this->json([
                "status" => 400,
                "operation" => "Not found"
            ]);
        }

        $this->em->remove($event);
        $this->em->flush();

        return $this->json([
            "status" => 200,
            "operation" => "Deleted"
        ]);
    }


    /**
     * @Route("/edit-event/{id}", name="edit_event", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function editEvent($id, Request $request): JsonResponse
    {
        $title = $request->request->get('title');
        $startedAt = $request->request->get('startedAt');
        $endedAt = $request->request->get('endedAt');
        $description = $request->request->get('description');

        $repository = $this->getDoctrine()->getRepository(Event::class);
        $event = $repository->find($id);

        if ($title !== $event->getTitle())
            $event->setTitle($title);

        if ($startedAt !== $event->getStartedAt())
            $event->setStartedAt($startedAt);

        if ($endedAt !== $event->getEndedAt())
            $event->setEndedAt($endedAt);

        if ($description !== $event->getDescription())
            $event->setDescription($description);

        $this->em->persist($event);
        $this->em->flush();

        return $this->json([
            "status" => 200,
            "operation" => "success"
        ]);
    }

}