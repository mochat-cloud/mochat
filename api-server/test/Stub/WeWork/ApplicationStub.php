<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace HyperfTest\Stub\WeWork;

use EasyWeChat\Work\Application as WxApplication;

class User
{
    public function get(string $userId): array
    {
        return [
            'errcode'           => 0,
            'errmsg'            => 'ok',
            'userid'            => 'aDaYu',
            'name'              => 'yyy',
            'department'        => [],
            'position'          => '',
            'mobile'            => '18888888',
            'gender'            => '2',
            'email'             => 'example@gmail.com',
            'avatar'            => 'https://www.baidu.com/img/flexible/logo/pc/result.png',
            'status'            => 1,
            'isleader'          => 0,
            'extattr'           => [],
            'telephone'         => '',
            'enable'            => 1,
            'hide_mobile'       => 0,
            'order'             => [],
            'external_profile'  => [],
            'main_department'   => 1,
            'qr_code'           => 'https://www.baidu.com/img/flexible/logo/pc/result.png',
            'alias'             => '',
            'is_leader_in_dept' => [],
            'address'           => '',
            'thumb_avatar'      => 'https://www.baidu.com/img/flexible/logo/pc/result.png',
        ];
    }
}

class ExternalContact
{
    public function get(string $externalUserId): array
    {
        return [
            'errcode'          => 0,
            'errmsg'           => 'ok',
            'external_contact' => [
                'external_userid' => 'xxx',
                'name'            => 'xx',
                'type'            => 1,
                'avatar'          => 'https://www.baidu.com/img/flexible/logo/pc/result.png',
                'gender'          => 1,
            ],
            'follow_user'    => [],
            'remark_mobiles' => [],
            'add_way'        => 1,
            'oper_userid'    => 'xxy',
        ];
    }
}

class ApplicationStub extends WxApplication
{
    public $user;

    public $external_contact;

    public function __construct(array $config = [], array $prepends = [], string $id = null)
    {
        parent::__construct($config, $prepends, $id);
        $this->user             = new User();
        $this->external_contact = new ExternalContact();
    }
}
