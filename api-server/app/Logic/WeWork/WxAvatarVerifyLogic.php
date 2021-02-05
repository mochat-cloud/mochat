<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WeWork;

use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Work\Application;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;

class WxAvatarVerifyLogic
{
    /**
     * @Inject
     * @var WxApp
     */
    protected $wxAppClient;

    /**
     * @Inject
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $employeeClient;

    /**
     * @Inject
     * @var WorkContactServiceInterface
     */
    protected $contactClient;

    /**
     * @var Application
     */
    protected $wxApp;

    /**
     * 检测员工、客户坏头像 + 重传.
     * @param int $corpId 企业ID
     * @param string $type 类型 user-员工 contact-客户
     * @param int $perPage 每次批量处理多少数据
     */
    public function handle(int $corpId, string $type = 'user', $perPage = 100): void
    {
        $this->wxApp = $this->wxAppClient->wxApp($corpId, $type);
        $func        = $type === 'user' ? 'employeeVerify' : 'contactVerify';
        $page        = 1;
        $isDone      = false;
        while (! $isDone) {
            $isDone = $this->{$func}($corpId, $page, $perPage);
            ++$page;
        }
    }

    /**
     * 检测员工.
     * @param int $corpId 企业ID
     * @param int $page 第几页
     * @param int $perPage 每页条数
     * @throws FileExistsException|InvalidConfigException ...
     * @return bool 是否完成验证
     */
    protected function employeeVerify(int $corpId, int $page = 1, int $perPage = 100): bool
    {
        $employees = $this->employeeClient->getWorkEmployeeList(
            ['corp_id' => $corpId],
            ['id', 'wx_user_id', 'avatar', 'thumb_avatar'],
            ['perPage' => $perPage, 'page' => $page]
        );
        if (empty($employees['data'])) {
            return true;
        }

        $dbData = [];
        foreach ($employees['data'] as $k => $employee) {
            ## 检测头像
            if ($this->fileSystem->has($employee['avatar'])) {
                break;
            }

            ## 坏头像重传
            $remoteEmployee = $this->wxApp->user->get($employee['wxUserId']);
            if ($remoteEmployee['errcode'] !== 0) {
                continue;
            }
            $fileName = md5($employee['wxUserId']) . '.png';
            $dbData[] = [
                'id'           => $employee['id'],
                'avatar'       => $this->ossUp($remoteEmployee['avatar'], 'mochat/employee/avatar/' . $fileName),
                'thumb_avatar' => $this->ossUp($remoteEmployee['thumb_avatar'], 'mochat/employee/thumb_avatar/' . $fileName),
            ];
        }
        empty($dbData) || $this->employeeClient->updateWorkEmployeesCaseIds($dbData);
        return false;
    }

    /**
     * 检测客户头像.
     * @param int $corpId 企业ID
     * @param int $page 第几页
     * @param int $perPage 每页条数
     * @throws FileExistsException|InvalidConfigException ...
     * @return bool 是否完成验证
     */
    protected function contactVerify(int $corpId, int $page = 1, int $perPage = 100): bool
    {
        $contactList = $this->contactClient->getWorkContactList(
            ['corp_id' => $corpId],
            ['id', 'wx_external_userid', 'avatar'],
            ['perPage' => $perPage, 'page' => $page]
        );
        dump($contactList);
        if (empty($contactList['data'])) {
            return true;
        }

        $dbData = [];
        foreach ($contactList['data'] as $k => $contact) {
            ## 检测头像
            if ($this->fileSystem->has($contact['avatar'])) {
                break;
            }

            ## 坏头像重传
            $remoteContact = $this->wxApp->external_contact->get($contact['wxExternalUserid']);
            if ($remoteContact['errcode'] !== 0) {
                continue;
            }
            $fileName = md5($contact['wxExternalUserid']) . '.png';
            $dbData[] = [
                'id'     => $contact['id'],
                'avatar' => $this->ossUp($remoteContact['external_contact']['avatar'], 'mochat/contact/avatar/' . $fileName),
            ];
        }
        empty($dbData) || $this->contactClient->updateWorkContactsCaseIds($dbData);
        return false;
    }

    /**
     * 上传图片.
     * @param string $url 图片地址URL
     * @param string $path 文件存储目录
     * @throws FileExistsException ...
     * @return string 文件路径
     */
    protected function ossUp(string $url, string $path = ''): string
    {
        if (empty($url)) {
            return '';
        }
        $this->fileSystem->has($path) && $this->fileSystem->delete($path);

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 180,
            ],
        ]);
        $this->fileSystem->write($path, file_get_contents($url, false, $ctx));
        return $path;
    }
}
