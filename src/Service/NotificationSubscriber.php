<?php

namespace App\Service;

use App\Entity\Notification;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use ExponentPhpSDK\Exceptions\ExpoException;
use ExponentPhpSDK\Expo;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class NotificationSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var PhoneRepository */
    private $phoneRepository;

    public function __construct(EntityManagerInterface $em, PhoneRepository $phoneRepository)
    {
        $this->em = $em;
        $this->phoneRepository = $phoneRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            'easy_admin.pre_persist' => array('send'),
        ];
    }

    public function send(GenericEvent $event)
    {
        $entity = $event->getSubject();
        if (!($entity instanceof Notification)) {
            return;
        }
        $expo = Expo::normalSetup();

        $notification = $entity;

        $targets = $this->phoneRepository->findAll();
        $ids = [];

        if($notification->getRandom()) $targets = [array_rand($targets)];
        foreach ($targets as $target)
        {
            $expo->subscribe($target->getToken(), $target->getToken());
            $ids[] = $target->getId();
        }

        $notificationRequest = [
            'title' => $notification->getTitle(),
            'body' => $notification->getBody(),
            'subtitle' => $notification->getSubtitle(),
            'ttl' => 10,
            'priority' => $notification->getPriority() ?? 'default',
            'sound' => 'default',
        ];

        try {
            $response = $expo->notify($ids, $notification);
            $notification->setResponse($response);
        } catch (ExpoException $e) {

        }
    }
}