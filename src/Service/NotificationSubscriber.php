<?php

namespace App\Service;

use App\Entity\Notification;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $target = $this->phoneRepository->findAll();
        if($notification->getRandom()) $target = [array_rand($target)];

        $notificationRequest = [
            'title' => $notification->getTitle(),
            'body' => $notification->getBody(),
            'subtitle' => $notification->getSubtitle(),
            'ttl' => 10,
            'priority' => $notification->getPriority() ?? 'default',
            'sound' => 'default',

        ];

    }
}