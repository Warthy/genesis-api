<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/event", name="api_event")
     * @param EventRepository $eventRepository
     * @return JsonResponse
     */
    public function events(EventRepository $eventRepository): JsonResponse
    {
        return new JsonResponse($eventRepository->findAll());
    }

    /**
     * @Route("/post", name="api_post")
     * @param PostRepository $postRepository
     * @return JsonResponse
     */
    public function posts(PostRepository $postRepository): JsonResponse
    {
        return new JsonResponse($postRepository->findAll());
    }
}
