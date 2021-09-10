<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\File;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionInviteContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionPosterContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionPushContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionWelcomeContract;

/**
 * 任务宝-更新.
 *
 * Class StoreLogic
 */
class UpdateLogic
{
    use AppTrait;

    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * 海报.
     * @var WorkFissionPosterContract
     */
    protected $workFissionPosterService;

    /**
     * 欢迎语.
     * @var WorkFissionWelcomeContract
     */
    protected $workFissionWelcomeService;

    /**
     * 推送
     * @var WorkFissionPushContract
     */
    protected $workFissionPushService;

    /**
     * 邀请用户.
     * @var WorkFissionInviteContract
     */
    protected $workFissionInviteService;

    /**
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * UpdateLogic constructor.
     */
    public function __construct(
        StdoutLoggerInterface $logger,
        WorkFissionContract $workFissionService,
        WorkFissionPosterContract $fissionPosterService,
        WorkFissionWelcomeContract $fissionWelcomeService,
        WorkFissionPushContract $fissionPushService,
        WorkFissionInviteContract $workFissionInviteService,
        CorpContract $corpService,
        WorkEmployeeContract $workEmployeeService
    ) {
        $this->logger = $logger;
        $this->workFissionService = $workFissionService;
        $this->workFissionPosterService = $fissionPosterService;
        $this->workFissionWelcomeService = $fissionWelcomeService;
        $this->workFissionPushService = $fissionPushService;
        $this->workFissionInviteService = $workFissionInviteService;
        $this->corpService = $corpService;
        $this->workEmployeeService = $workEmployeeService;
    }

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @throws \JsonException
     * @throws \League\Flysystem\FileExistsException
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理参数
        $data = $this->handleParam($user, $params);
        ## 更新活动
        $this->updateWorkFission((int) $params['fission']['id'], $data);

        return [];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @throws \JsonException|\League\Flysystem\FileExistsException
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        $receiveQrcode = $params['fission']['receive_prize'] == 0 ? $this->createQRcode($user, $params['fission']['auto_pass'], $params['fission']['receive_prize_employees']) : [];
        $receiveQrcode = empty($receiveQrcode) ? [] : $receiveQrcode;
        $data['fission'] = [
            'corp_id' => $user['corpIds'][0],
            'active_name' => $params['fission']['active_name'],
            'service_employees' => json_encode($params['fission']['service_employees'], JSON_THROW_ON_ERROR),
            'auto_pass' => $params['fission']['auto_pass'] ? 1 : 0,
            'auto_add_tag' => $params['fission']['auto_add_tag'] ? 1 : 0,
            'contact_tags' => json_encode($params['fission']['contact_tags'], JSON_THROW_ON_ERROR),
            'end_time' => $params['fission']['end_time'],
            'qr_code_invalid' => $params['fission']['qr_code_invalid'],
            'tasks' => json_encode($params['fission']['tasks'], JSON_THROW_ON_ERROR),
            'receive_prize' => $params['fission']['receive_prize'],
            'receive_prize_employees' => json_encode($params['fission']['receive_prize_employees'], JSON_THROW_ON_ERROR),
            'receive_links' => json_encode($params['fission']['receive_links'], JSON_THROW_ON_ERROR),
            'receive_qrcode' => json_encode($receiveQrcode, JSON_THROW_ON_ERROR),
        ];
        ##欢迎语
        if (! empty($params['welcome']['link_cover_url'])) {
            $picUrl = File::uploadBase64Image($params['welcome']['link_cover_url'], 'image/fission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
            $params['welcome']['link_cover_url'] = $picUrl;
            $localFile = File::download(file_full_url($picUrl), $picUrl);
            $wxUrl = $this->wxApp($user['corpIds'][0], 'contact')->media->uploadImg($localFile);
        } else {
            $wxUrl = ['url' => ''];
        }

        $data['welcome'] = [
            'msg_text' => $params['welcome']['msg_text'],
            'link_title' => $params['welcome']['link_title'],
            'link_desc' => $params['welcome']['link_desc'],
            'link_cover_url' => $params['welcome']['link_cover_url'],
            'link_wx_url' => $wxUrl['url'],
        ];
        ##海报
        $data['poster'] = [
            'poster_type' => $params['poster']['poster_type'],
            'cover_pic' => empty($params['poster']['cover_pic']) ? '' : File::uploadBase64Image($params['poster']['cover_pic'], 'image/fission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg'),
            'foward_text' => $params['poster']['foward_text'],
            'avatar_show' => $params['poster']['avatar_show'] ? 1 : 0,
            'nickname_show' => $params['poster']['nickname_show'] ? 1 : 0,
            'nickname_color' => $params['poster']['nickname_color'],
            'card_corp_image_name' => $params['poster']['card_corp_image_name'],
            'card_corp_name' => $params['poster']['card_corp_name'],
            'card_corp_logo' => empty($params['poster']['card_corp_logo']) ? '' : File::uploadBase64Image($params['poster']['card_corp_logo'], 'image/fission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg'),
            'qrcode_w' => $params['poster']['qrcode_w'],
            'qrcode_h' => $params['poster']['qrcode_h'],
            'qrcode_x' => $params['poster']['qrcode_x'],
            'qrcode_y' => $params['poster']['qrcode_y'],
        ];
        ##推送
        $complex = $this->handleComplex($user, $params['push']['msg_complex'], $params['push']['msg_complex_type']);
        $data['push'] = [
            'push_employee' => $params['push']['push_employee'] ? 1 : 0,
            'push_contact' => $params['push']['push_contact'] ? 1 : 0,
            'msg_text' => $params['push']['msg_text'],
            'msg_complex' => json_encode($complex['complex'], JSON_THROW_ON_ERROR),
            'msg_complex_type' => $complex['msg_complex_type'],
        ];
        return $data;
    }

    /**
     * 创建活动.
     * @param int $id 活动id
     * @param array $params 参数
     * @return int 响应数值
     */
    private function updateWorkFission(int $id, array $params): int
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $this->workFissionService->updateWorkFissionById((int) $id, $params['fission']);
            $poster = $this->workFissionPosterService->getWorkFissionPosterByFissionId((int) $id);
            $this->workFissionPosterService->updateWorkFissionPosterById((int) $poster['id'], $params['poster']);
            $welcome = $this->workFissionWelcomeService->getWorkFissionWelcomeByFissionId((int) $id);
            $this->workFissionWelcomeService->updateWorkFissionById((int) $welcome['id'], $params['welcome']);
            $push = $this->workFissionPushService->getWorkFissionPushByFissionId((int) $id);
            $this->workFissionPushService->updateWorkFissionById((int) $push['id'], $params['push']);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '活动更新失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '活动更新失败'); //$e->getMessage()
        }
        return $id;
    }

    /**
     * 生成二维码
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @return array
     */
    private function createQRcode(array $user, bool $auto_pass, array $employees)
    {
        ##EasyWeChat配置客户联系「联系我」方式
        $res = $this->wxApp($user['corpIds'][0], 'contact')->contact_way->create(2, 2, [
            'skip_verify' => $auto_pass,
            'state' => '',
            'user' => array_column($employees, 'wxUserId'),
        ]);
        if ($res['errcode'] !== 0) {
            $this->logger->error(sprintf('生成二维码 失败::[%s]', json_encode($res, JSON_THROW_ON_ERROR)));
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请求失败，部分员工未进行实名认证，请实名后重试'); //$res['msg']"生成二维码失败"
        }
        return ['qrId' => $res['config_id'], 'url' => $res['qr_code']];
    }

    /**
     * @param $user
     * @param $complex
     * @param $msg_complex_type
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\Flysystem\FileExistsException
     */
    private function handleComplex($user, $complex, $msg_complex_type): array
    {
        $res = [];
        if (! empty($complex['image'])) {
            $pic = File::uploadBase64Image($complex['image'], 'image/fission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
            $localFile = File::download(file_full_url($pic), $pic);
            $wxPic = $this->wxApp($user['corpIds'][0], 'contact')->media->uploadImg($localFile);
            $res['image'] = $pic;
            $res['pic_url'] = $wxPic['url'];
        }
        if (! empty($complex['link']['image'])) {
            $pic = File::uploadBase64Image($complex['link']['image'], 'image/fission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
            $localFile = File::download(file_full_url($pic), $pic);
            $wxPic = $this->wxApp($user['corpIds'][0], 'contact')->media->uploadImg($localFile);
            $res['image'] = $pic;
            $res['pic_url'] = $wxPic['url'];
            $res['title'] = $complex['link']['title'];
            $res['url'] = $complex['link']['url'];
            $res['desc'] = $complex['link']['desc'];
        }
        if (! empty($complex['applets']['image'])) {
            $pic = File::uploadBase64Image($complex['applets']['image'], 'image/fission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
            $localFile = File::download(file_full_url($pic), $pic);
            $wxPic = $this->wxApp($user['corpIds'][0], 'contact')->media->uploadImg($localFile);
            $res['image'] = $pic;
            $res['pic_url'] = $wxPic['url'];
            $res['title'] = $complex['applets']['title'];
            $res['appid'] = $complex['applets']['appid'];
            $res['path'] = $complex['applets']['path'];
        }

        if (empty($complex['image']) && empty($complex['link']['url']) && empty($complex['applets']['title'])) {
            $msg_complex_type = '';
        }

        return ['msg_complex_type' => $msg_complex_type, 'complex' => $res];
    }
}
