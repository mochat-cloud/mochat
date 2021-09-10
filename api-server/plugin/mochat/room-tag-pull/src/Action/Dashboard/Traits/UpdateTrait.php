<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomTagPull\Action\Dashboard\Traits;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use Psr\Container\ContainerInterface;

trait UpdateTrait
{
    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * 过滤客户.
     */
    protected function filterContact(int $filter_contact, array $user, array $rooms, array $employees, array $choose_contact): int
    {
        $count = 0;
//        foreach ($rooms as $room) {
//            $roomContact = $this->workContactRoomService->getWorkContactRoomsByRoomIdContact((int)$room['id'], ['contact_id']);
//            $contact = $this->workContactService->getWorkContactsBySearch($user['corpIds'][0], $employees, $choose_contact);
//            foreach ($contact as $k => $v) {
//                if ($filter_contact ===1 &&in_array($v['contactId'], array_column($roomContact, 'contactId'), true)) unset($contact[$k]);
//            }
//            $count += count($contact);
//        }
        ## 所有客户
        $contact = $this->workContactService->getWorkContactsBySearch($user['corpIds'][0], $employees, $choose_contact);
        if (empty($contact)) {
            return $count;
        }
        ## 不过滤
        if ($filter_contact === 0) {
            return count($contact);
        }
        $start = 0;
        for ($i = 0; $i <= 100; ++$i) {
            if (isset($rooms[$i])) {
                $sendContact = array_slice($contact, $start, $rooms[$i]['num']);
                if (empty($sendContact)) {
                    break;
                }
                $roomContact = $this->workContactRoomService->getWorkContactRoomsByRoomIdContact((int) $rooms[$i]['id'], ['contact_id']);
                foreach ($sendContact as $k => $v) {
                    if ($filter_contact === 1 && in_array($v['contactId'], array_column($roomContact, 'contactId'), true)) {
                        unset($sendContact[$k]);
                    }
                }
                $start += $rooms[$i]['num'];
                $count += count($roomContact);
            }
        }

        return $count;
    }
}
