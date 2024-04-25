<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Logic;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Utils\WeWorkFactory;

class SendWelcomeLogic
{
    /**
     * Required attributes.
     *
     * @var array
     */
    protected $required = ['content', 'title', 'url', 'pic_media_id', 'appid', 'page'];

    protected $attachmentRule = [
        'text' => [
            'content' => '',
        ],
        'image' => [],
        'link' =>  [
            'title' => '',
            'picurl' => '',
            'desc' => '',
            'url' => '',
        ],
        'miniprogram' => [
            'title' => '',
            'pic_media_id' => '',
            'appid' => '',
            'page' => '',
        ],
        'video' => [
            'media_id' => '',
        ],
        'file' => [
            'media_id' => '',
        ],
    ];

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @param $corpId
     * @param string $welcomeCode
     * @param array $msg
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($corpId, string $welcomeCode, array $msg)
    {
        $this->logger->debug(sprintf('欢迎语参数, [%s] [%s] %s', $corpId, $welcomeCode, json_encode($msg)));
        $weWorkContactApp = make(WeWorkFactory::class)->getContactApp($corpId);
        $formattedMsg = $this->formatMessage($msg);

        $params = array_merge($formattedMsg, [
            'welcome_code' => $welcomeCode,
        ]);
        return $weWorkContactApp->external_contact_message->httpPostJson('cgi-bin/externalcontact/send_welcome_msg', $params);
    }

    /**
     * @param array $data
     * @return array
     * @throws InvalidArgumentException
     */
    private function formatMessage(array $data = [])
    {
        $params = [];
        if (!empty($data['text'])) {
            $params['text'] = $this->formatFields($data['text'], $this->attachmentRule['text']);
        }
        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                $msgType = $attachment['msgtype'];
                $params['attachments'][] = [
                    'msgtype' => $msgType,
                    $msgType => $this->formatFields($attachment[$msgType], $this->attachmentRule[$msgType]),
                ];
            }
        }
        $this->logger->debug(sprintf('格式化欢迎语参数: %s', json_encode($params)));
        if (!empty($params['attachments']) && count($params['attachments']) > 9) {
            throw new InvalidArgumentException('最多可添加9个附件');
        }
        return $params;
    }

    /**
     * @param array $data
     * @param array $default
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    protected function formatFields(array $data = [], array $default = [])
    {
        $params = array_merge($default, $data);
        foreach ($params as $key => $value) {
            if (in_array($key, $this->required, true) && empty($value) && empty($default[$key])) {
                throw new InvalidArgumentException(sprintf('Attribute "%s" can not be empty!', $key));
            }

            $params[$key] = empty($value) ? $default[$key] : $value;
        }

        return $params;
    }
}