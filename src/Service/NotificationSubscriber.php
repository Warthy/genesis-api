<?php

namespace App\Service;

use App\Entity\Notification;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class NotificationSubscriber implements EventSubscriberInterface
{
    const EXPO_API_URL = 'https://exp.host/--/api/v2/push/send';

    /** @var EntityManagerInterface */
    private $em;

    /** @var PhoneRepository */
    private $phoneRepository;

    private $ch;

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

        $notification = $entity;

        $targets = $this->phoneRepository->findAll();
        $postData = [];

        if ($targets) {
            if ($notification->getRandom()) $targets = [$targets[array_rand($targets)]];

            $notificationRequest = [
                'title' => $notification->getTitle(),
                'body' => $notification->getBody() ??  '',
                'subtitle' => $notification->getSubtitle() ?? '',
                'ttl' => 10,
                'priority' => $notification->getPriority() ?? 'default',
                'sound' => 'default',
            ];

            foreach ($targets as $target) {
                $postData[] = $notificationRequest + ['to' => $target->getToken()];
            }

            $this->prepareCurl($postData);
            $res = $this->executeCurl();

            switch ($res['status_code']){
                case 400:
                    $notification->setResponse($res['body']['errors']);
                    $notification->setSuccess(false);
                break;
                case 200:
                    $notification->setResponse($res['body']);
                    $notification->setSuccess(true);
                    break;
                default:
                    $notification->setResponse($res['body']);
                    $notification->setSuccess(false);
                    break;
            }
            return;
        }
        
        $notification->setSuccess(false);
        $notification->setResponse(["error" => "Aucun destinaire en base de données"]);
        return;
    }

    private function prepareCurl($data){
        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_URL, self::EXPO_API_URL);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'content-type: application/json',
        ]);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    private function executeCurl(){
        $response = [
            'body' => curl_exec($this->ch),
            'status_code' => curl_getinfo($this->ch, CURLINFO_HTTP_CODE)
        ];
        return $response;
    }
}