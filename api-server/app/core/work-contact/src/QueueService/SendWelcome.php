<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\QueueService;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\App\Medium\Constants\Type as MediumType;
use MoChat\App\Utils\Media;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Logic\SendWelcomeLogic;

/**
 * 发送欢迎语.
 */
class SendWelcome
{
    /**
     * @AsyncQueueMessage(pool="welcome")
     *
     * @param int|string $corpId 企业id
     * @param array $contact 客户信息
     * @param string $welcomeCode 发送欢迎语的凭证
     * @param array $content 欢迎语内容
     */
    public function handle($corpId, array $contact, string $welcomeCode, array $content)
    {
        if (empty($content)) {
            return;
        }

        $logger = make(StdoutLoggerInterface::class);

        // 微信消息体
        $sendWelcomeData = $this->getSendWelcomeData($corpId, $contact, $content);

        $sendWelcomeRes = make(SendWelcomeLogic::class)->handle($corpId, $contact['welcomeCode'], $sendWelcomeData);
        if ($sendWelcomeRes['errcode'] != 0) {
            // 记录错误日志
            $logger->error(sprintf('%s 请求数据：%s 响应结果：%s', '请求微信发送欢迎语失败', json_encode(['welcomeCode' => $welcomeCode, 'sendWelcomeData' => $sendWelcomeData]), $sendWelcomeRes['errmsg']));
            return;
        }

        $logger->debug(sprintf('客户欢迎语发送成功，客户id: %s', (string) $contact['id']));
    }

    /**
     * 获取欢迎语结构体
     *
     * @param int $corpId
     * @param array $contact
     * @param array $content
     * @return array
     */
    private function getSendWelcomeData(int $corpId, array $contact, array $content): array
    {
        $sendWelcomeData = [];
        $content = $this->replaceContactName($content, $contact['name']);

        // 微信消息体 - 文本
        empty($content['text']) || $sendWelcomeData['text']['content'] = $content['text'];

        // 微信消息体 - 媒体文件
        if (! empty($content['medium'])) {
            $mediaUtil = make(Media::class);
            // 组织推送消息数据
            switch ($content['medium']['mediumType']) {
                case MediumType::PICTURE:
                    // 上传临时素材
                    $image['msgtype'] = 'image';
                    $image['image']['media_id'] = $mediaUtil->uploadImage($corpId, $content['medium']['mediumContent']['imagePath']);
                    $sendWelcomeData['attachments'][] = $image;
                    break;
                case MediumType::PICTURE_TEXT:
                    $link['msgtype'] = 'link';
                    $link['link'] = [
                        'title' => $content['medium']['mediumContent']['title'],
                        'url' => $content['medium']['mediumContent']['imageLink'],
                    ];
                    if (isset($content['medium']['mediumContent']['imagePath'])) {
                        $link['link']['picurl'] = file_full_url($content['medium']['mediumContent']['imagePath']);
                    }
                    if (isset($content['medium']['mediumContent']['description'])) {
                        $link['link']['desc'] = $content['medium']['mediumContent']['description'];
                    }
                    $sendWelcomeData['attachments'][] = $link;
                    break;
                case MediumType::MINI_PROGRAM:
                    // 上传临时素材
                    $mediaId = $mediaUtil->uploadImage($corpId, $content['medium']['mediumContent']['imagePath']);
                    $miniprogram['msgtype'] = 'miniprogram';
                    $miniprogram['miniprogram'] = [
                        'title' => $content['medium']['mediumContent']['title'],
                        'pic_media_id' => $mediaId,
                        'appid' => $content['medium']['mediumContent']['appid'],
                        'page' => $content['medium']['mediumContent']['page'],
                    ];
                    $sendWelcomeData['attachments'][] = $miniprogram;
                    break;
            }
        }

        return $sendWelcomeData;
    }

    /**
     * 替换内容中的客户名称.
     *
     * @return array
     */
    private function replaceContactName(array $content, string $contactName): array
    {
        if (isset($content['text']) && ! empty($content['text'])) {
            $content['text'] = str_replace('##客户名称##', $contactName, $content['text']);
        }

        return $content;
    }
}
