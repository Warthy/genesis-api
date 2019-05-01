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

    private $mh;

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
        $res = [];
        if (!($entity instanceof Notification)) {
            return;
        }

        $notification = $entity;
        $notificationRequest = [
            'title' => $notification->getTitle(),
            'body' => $notification->getBody() ?? '',
            'subtitle' => $notification->getSubtitle() ?? '',
            'ttl' => 10,
            'priority' => $notification->getPriority() ?? 'default',
            'sound' => 'default',
        ];

        $targets = $this->phoneRepository->findAll();
        if ($targets) {
            $chs = [];
            if ($notification->getRandom()) {
                do {
                    $this->mh = curl_multi_init();
                    $ch = curl_init();
                    $target = $targets[array_rand($targets)];
                    $postData[] = $notificationRequest + ['to' => $target->getToken()];

                    $this->prepareCurl($ch, $postData);

                    $this->executeCurl([$ch]);
                    $res = json_decode(curl_multi_getcontent($ch));
                } while ($res->data[0]->status == 'error');

                $notification->setSuccess(true);
                $notification->setResponse($res);
                return;
            }else {
                $this->mh = curl_multi_init();
                foreach ($targets as $target){
                    $postData = [];
                    $postData[] = $notificationRequest + ['to' => $target->getToken()];
                    $ch = curl_init();
                    $this->prepareCurl($ch , $postData);

                    $chs[] = $ch;
                }
                $this->executeCurl($chs);
                $res = [];
                foreach ($chs as $ch){
                    $res[] = json_decode(curl_multi_getcontent($ch));
                }
                $notification->setSuccess(true);
                $notification->setResponse($res);
                return;
            }
        }


        $notification->setSuccess(false);
        $notification->setResponse(["error" => "Aucun destinaire en base de données"]);

        return;
    }

    private function prepareCurl($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL, self::EXPO_API_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'content-type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_multi_add_handle($this->mh, $ch);
    }

    private function executeCurl(array $chs)
    {
        //LAUNCH cURL
        do {
            $status = curl_multi_exec($this->mh, $active);
            if ($active) {
                curl_multi_select($this->mh);
            }
        } while ($active && $status == CURLM_OK);

        //CLOSE ALL cURL
        foreach ($chs as $ch) {
            curl_multi_remove_handle($this->mh, $ch);
        }
        curl_multi_close($this->mh);
        return;
    }
}