<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace HyperfTest\Logic\WeWork;

use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Logic\WeWork\WxApp;
use App\Logic\WeWork\WxAvatarVerifyLogic;
use Hyperf\Di\Container;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Stub\WeWork\ApplicationStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class WxAvatarVerifyLogicTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testAvatar(): void
    {
        $falseMsg = '';
        try {
            $this->getContainer();
            $logic = $this->getContainer()->get(WxAvatarVerifyLogic::class);
            $logic->handle(1);
            $logic->handle(1, 'contact');
            $res = true;
        } catch (\Exception $e) {
            $res      = false;
            $falseMsg = $e->getMessage();
        }

        self::assertTrue($res, $falseMsg);
    }

    /**
     * @return Container|ContainerInterface
     */
    protected function getContainer()
    {
        $container = ApplicationContext::getContainer();

        ## 员工服务替身
        $employeeServiceStub = $this->createMock(WorkEmployeeServiceInterface::class);
        $employeeServiceStub->method('getWorkEmployeeList')->willReturnCallback(function () {
            return self::fakeEmployeeList(func_get_args());
        });
        $employeeServiceStub->method('updateWorkEmployeesCaseIds')->willReturn(true);

        ## 客户服务替身
        $contactServiceStub = $this->createMock(WorkContactServiceInterface::class);
        $contactServiceStub->method('getWorkContactList')
            ->willReturnCallback(function () {
                                return self::fakeContactList(func_get_args());
                            });
        $contactServiceStub->method('updateWorkContactsCaseIds')->willReturn(true);

        ## wxApp替身
        $wxAppStub = $this->createPartialMock(WxApp::class, ['wxApp']);
        $wxAppStub->method('wxApp')->willReturn(new ApplicationStub());

        ## 替换
        $container->getDefinitionSource()
            ->addDefinition(WorkEmployeeServiceInterface::class, function () use ($employeeServiceStub) {
                    return $employeeServiceStub;
                })
            ->addDefinition(WorkContactServiceInterface::class, function () use ($contactServiceStub) {
                    return $contactServiceStub;
                })
            ->addDefinition(WxApp::class, function () use ($wxAppStub) {
                    return $wxAppStub;
                });
        return $container;
    }

    /**
     * 员工列表假数据.
     * @param array $args ...
     * @return array[]|\array[][] ...
     */
    protected static function fakeEmployeeList(array $args): array
    {
        $page                            = 1;
        isset($args[2]['page']) && $page = $args[2]['page'];
        if ($page === 1) {
            return [
                'data' => [
                    [
                        'id'          => 1,
                        'wxUserId'    => 'aDaYu',
                        'avatar'      => 'mochat/employee/xxx.png',
                        'thumbAvatar' => 'mochat/employee/xxx_thumb.png',
                    ],
                ],
            ];
        }
        if ($page === 2) {
            return [
                'data' => [
                    [
                        'id'          => 2,
                        'wxUserId'    => 'yyy',
                        'avatar'      => 'mochat/employee/yyy.png',
                        'thumbAvatar' => 'mochat/employee/yyy_thumb.png',
                    ],
                ],
            ];
        }

        return [
            'data' => [],
        ];
    }

    /**
     * 客户列表假数据.
     * @param array $args ...
     * @return array[]|\array[][] ...
     */
    protected static function fakeContactList(array $args): array
    {
        $page                            = 1;
        isset($args[2]['page']) && $page = $args[2]['page'];
        if ($page === 1) {
            return [
                'data' => [
                    ['id' => 1, 'wxExternalUserid' => 'wmay_0CAAAA0ySRFZ-M-urxwHXeSgGLQ', 'avatar' => 'mochat/contact/aaa.png'],
                    ['id' => 2, 'wxExternalUserid' => 'bbb', 'avatar' => 'mochat/contact/bbb.png'],
                ],
            ];
        }
        if ($page === 2) {
            return [
                'data' => [
                    ['id' => 3, 'wxExternalUserid' => 'ccc', 'avatar' => 'mochat/contact/ccc.png'],
                ],
            ];
        }

        return [
            'data' => [],
        ];
    }
}
