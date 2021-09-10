<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Action\Operation;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\File;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionPosterContract;

/**
 * 任务宝H5 - 海报.
 *
 * Class Poster.
 * @Controller
 */
class Poster extends AbstractAction
{
    use ValidateSceneTrait;
    use AppTrait;

    /**
     * @var \Laminas\Stdlib\RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var WorkFissionPosterContract
     */
    protected $workFissionPosterService;

    /**
     * @Inject
     * @var WorkFissionContactContract
     */
    protected $workFissionContactService;

    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * Poster constructor.
     */
    public function __construct(RequestInterface $request, WorkFissionPosterContract $workFissionPosterService, WorkFissionContactContract $workFissionContactService, WorkFissionContract $workFissionService, CorpContract $corpService, WorkContactContract $workContactService, WorkContactEmployeeContract $workContactEmployeeService, WorkEmployeeContract $workEmployeeService)
    {
        $this->request = $request;
        $this->workFissionPosterService = $workFissionPosterService;
        $this->workFissionContactService = $workFissionContactService;
        $this->workFissionService = $workFissionService;
        $this->corpService = $corpService;
        $this->workContactService = $workContactService;
        $this->workContactEmployeeService = $workContactEmployeeService;
        $this->workEmployeeService = $workEmployeeService;
    }

    /**
     * @RequestMapping(path="/operation/workFission/poster", methods="get")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);
        return $this->getPoster($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'fission_id' => 'required',
            'union_id' => 'required',
            'nickname' => 'required',
            'avatar' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'fission_id.required' => '活动id 必填',
            'union_id.required' => 'union_id必填',
            'nickname.required' => 'nickname必填',
            'avatar.required' => 'avatar必填',
        ];
    }

    /**
     * @param $params
     * @throws \JsonException
     */
    private function getPoster($params): array
    {
        ## 海报
        $poster = $this->workFissionPosterService->getWorkFissionPosterByFissionId((int) $params['fission_id']);
        $qrcode = $this->handleQrcode($params);
        $poster['qrcodeUrl'] = file_full_url($qrcode[0]);
        unset($poster['id'], $poster['fissionId'], $poster['wxCoverPic'], $poster['createdAt'], $poster['updatedAt'], $poster['deletedAt']);
        if (! empty($poster['coverPic'])) {
            $poster['coverPic'] = file_full_url($poster['coverPic']);
        }
        if (! empty($poster['cardCorpLogo'])) {
            $poster['cardCorpLogo'] = file_full_url($poster['cardCorpLogo']);
        }
        return $poster;
    }

    /**
     * 生成二维码 二维码本地保存.
     * @param $params
     * @throws \JsonException
     */
    private function handleQrcode($params): array
    {
        $fissionContact = $this->workFissionContactService->getWorkFissionContactByFissionIDUnionId((int) $params['fission_id'], (string) $params['union_id'], ['id', 'qrcode_url']);
        if (empty($fissionContact)) {//无参与记录
            $contact = $this->workContactService->getWorkContactByUnionId((string) $params['union_id'], ['id', 'wx_external_userid']);
            $data = [
                'fission_id' => (int) $params['fission_id'],
                'union_id' => $params['union_id'],
                'nickname' => $params['nickname'],
                'avatar' => $params['avatar'],
                'level' => empty($contact) ? 0 : 1,
                'external_user_id' => empty($contact) ? '' : $contact['wxExternalUserid'],
            ];
            $id = $this->workFissionContactService->createWorkFissionContact($data);
            return [$this->getQRcode($id, $params)];
        }
        if (empty($fissionContact['qrcodeUrl'])) {//有参与记录，无二维码
            return [$this->getQRcode((int) $fissionContact['id'], $params)];
        }
        return [$fissionContact['qrcodeUrl']];
    }

    /**
     * 生成二维码
     * @throws \JsonException
     */
    private function getQRcode(int $id, array $params): string
    {
        $fission = $this->workFissionService->getWorkFissionById((int) $params['fission_id']);
        $employees = json_decode($fission['serviceEmployees'], true, 512, JSON_THROW_ON_ERROR);
        $autoPass = $fission['autoPass'] == 1 ? true : false;

        ##EasyWeChat配置客户联系「联系我」方式
        $res = $this->wxApp((int) $fission['corpId'], 'contact')->contact_way->create(2, 2, [
            'skip_verify' => $autoPass,
            'state' => 'fission-' . $id,
            'user' => array_column($employees, 'wxUserId'),
        ]);
        if ($res['errcode'] !== 0) {
            $this->logger->error(sprintf('生成二维码 失败::[%s]', json_encode($res, JSON_THROW_ON_ERROR)));
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请求失败，部分员工未进行实名认证，请实名后重试'); //$res['msg']"生成二维码失败"
        }

        ## 二维码本地保存
        $pic = File::uploadUrlImage($res['qr_code'], 'image/fission/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
        $this->workFissionContactService->updateWorkFissionContactById($id, ['qrcode_id' => $res['qr_code'], 'qrcode_url' => $pic]);
        return $pic;
    }
}
