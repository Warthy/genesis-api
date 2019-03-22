<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\EventRepository;
use App\Repository\PostRepository;
use App\Repository\SponsorRepository;
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
     * @return Response
     */
    public function events(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        $serializer = $this->container->get('serializer');
        $events = $serializer->serialize($events, 'json');

        $response = new Response();
        $response->setContent($events);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/post", name="api_post", methods="GET")
     * @param PostRepository $postRepository
     * @return Response
     */
    public function post(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        $serializer = $this->container->get('serializer');
        $posts = $serializer->serialize($posts, 'json');

        $response = new Response();
        $response->setContent($posts);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/sponsor", name="api_sponsor", methods="GET")
     * @param SponsorRepository $sponsorRepository
     * @return Response
     */
    public function sponsor(SponsorRepository $sponsorRepository): Response
    {
        $sponsors = $sponsorRepository->findAll();
        $serializer = $this->container->get('serializer');
        $sponsors = $serializer->serialize($sponsors, 'json');

        $response = new Response();
        $response->setContent($sponsors);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    /**
     * @Route("/phone", name="api_phone", methods="POST")
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
                $phone->setToken($data['token']);

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
