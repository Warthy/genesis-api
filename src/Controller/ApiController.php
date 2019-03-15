<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\EventRepository;
use App\Repository\PostRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/event", name="api_event", methods="GET")
     * @param EventRepository $eventRepository
     * @return JsonResponse
     */
    public function events(EventRepository $eventRepository): JsonResponse
    {
        return new JsonResponse($eventRepository->findAll());
    }

    /**
     * @Route("/post", name="api_post", methods="GET")
     * @param PostRepository $postRepository
     * @return JsonResponse
     */
    public function posts(PostRepository $postRepository): JsonResponse
    {
        return new JsonResponse($postRepository->findAll());
    }

    /**
     * @Route("/phone", name="api_post", methods="POST")
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function phone(Request $request, EntityManagerInterface $em){
        if(strpos($request->headers->get('Content-Type'), 'application/json') === 0){
            $data = json_decode($request->getContent(), true);
            if(isset($data['token']) || array_key_exists('token', $data)) {
                $phone = new Phone();
                $phone
                    ->setToken($data['token'])
                    ->setUsername($data['username'] ?? 'Genesis');

                try{
                    $em->persist($phone);
                    $em->flush();
                }catch (UniqueConstraintViolationException $e){
                    return $this->json(['error' => 'token_duplicate', 'message' => 'Token given already used, it must be unique'], Response::HTTP_BAD_REQUEST);
                }catch(Exception $e){
                    return $this->json(['error' => 'server_error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                return $this->json($phone, Response::HTTP_CREATED);
            }
            return $this->json(['error' => 'token_undefined', 'message' => 'Token must be defined in post parameters'], Response::HTTP_BAD_REQUEST);
        }
        return $this->json(['error' => 'wrong_content-type', 'message' => 'content-type must be application/json'], Response::HTTP_NOT_ACCEPTABLE);
    }

}
