<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Radar\Action\Operation;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkAgent\QueueService\MessageRemind;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\AutoTag\Action\Dashboard\Traits\AutoContactTag;
use MoChat\Plugin\Radar\Contract\RadarChannelContract;
use MoChat\Plugin\Radar\Contract\RadarChannelLinkContract;
use MoChat\Plugin\Radar\Contract\RadarContract;
use MoChat\Plugin\Radar\Contract\RadarRecordContract;

/**
 * 互动雷达-H5 获取雷达信息.
 *
 * Class Show.
 * @Controller
 */
class GetRadar extends AbstractAction
{
    use ValidateSceneTrait;
    use AutoContactTag;
    use AppTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var RadarContract
     */
    protected $radarService;

    /**
     * @Inject
     * @var RadarRecordContract
     */
    protected $radarRecordService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var RadarChannelContract
     */
    protected $radarChannelService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var RadarChannelLinkContract
     */
    protected $radarChannelLinkService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @Inject
     * @var MessageRemind
     */
    protected $messageRemind;

    /**
     * @Inject
     * @var WorkAgentContract
     */
    private $workAgentService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/operation/radar/getRadar", methods="get")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'union_id' => $this->request->input('union_id'),
            'nickname' => $this->request->input('nickname'),
            'avatar' => $this->request->input('avatar'),
            'type' => (int) $this->request->input('target_type'),
            'radar_id' => (int) $this->request->input('radar_id'),
            'link_id' => (int) $this->request->input('target_id'),
            'employee_id' => (int) $this->request->input('staff_id'),
        ];

        ## 数据统计
        return $this->handleData($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'union_id' => 'required',
            'nickname' => 'required',
            'avatar' => 'required',
            'target_type' => 'required',
            'radar_id' => 'required',
            'target_id' => 'required',
            'staff_id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'union_id.required' => 'union_id 必传',
            'nickname.required' => 'nickname 必传',
            'avatar.required' => 'avatar 必传',
            'target_type.required' => 'target_type 必传',
            'radar_id.required' => 'radar_id 必传',
            'target_id.required' => 'target_id 必传',
            'staff_id.required' => 'staff_id 必传',
        ];
    }

    /**
     * @param $params
     * @throws \JsonException
     */
    private function handleData($params): array
    {
        ## 雷达
        $radar = $this->radarService->getRadarById($params['radar_id'], ['type', 'title', 'link', 'pdf', 'article_type', 'article', 'action_notice', 'dynamic_notice', 'tag_status', 'contact_tags', 'employee_card', 'corp_id']);
        ## 链接
        $link = $this->radarChannelLinkService->getRadarChannelLinkById($params['link_id'], ['channel_id', 'link', 'employee_id', 'click_num', 'click_person_num']);
        ## 渠道
        $channel = $this->radarChannelService->getRadarChannelById($link['channelId'], ['id', 'name', 'corp_id']);
        ## 点击记录
        $info = $this->radarRecordService->getRadarRecordByUnionIdChannelIdRadarId($params['union_id'], $channel['id'], $params['radar_id'], ['contact_id']);
        $contactId = empty($info) ? 0 : $info[0]['contactId'];
        if ($contactId === 0) {
            $contact = $this->workContactService->getWorkContactByCorpIdUnionId($radar['corpId'], $params['union_id'], ['id']);
            $contactId = empty($contact) ? 0 : $contact['id'];
        }
        ## 员工
        $employee = $this->workEmployeeService->getWorkEmployeeById($params['employee_id'], ['name', 'wx_user_id']);
        $content = "【雷达文章】\n「{$params['nickname']}」打开了「{$employee['name']}」在「自建渠道-{$channel['name']}」里发送的互动雷达「{$radar['title']}」\n客户昵称:{$params['nickname']}\n客户类型:微信客户\n<a href='{$link['link']}'>点击查看链接</a>";

        $data = [
            'radar_id' => $params['radar_id'],
            'channel_id' => $channel['id'],
            'type' => 1,
            'union_id' => $params['union_id'],
            'nickname' => $params['nickname'],
            'avatar' => $params['avatar'],
            'contact_id' => $contactId,
            'employee_id' => $params['employee_id'],
            'content' => $content,
            'corp_id' => $radar['corpId'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
        ## 添加点击记录
        $this->radarRecordService->createRadarRecord($data);
        if (empty($info)) {//新客户
            $this->radarChannelLinkService->updateRadarChannelLinkById($params['link_id'], ['click_person_num' => $link['clickPersonNum'] + 1]);
            if ($contactId > 0 && $radar['tagStatus'] === 1) {
                $this->tag($contactId, json_decode($radar['contactTags'], true, 512, JSON_THROW_ON_ERROR), $radar['corpId']);
            }
        }
        $this->radarChannelLinkService->updateRadarChannelLinkById($params['link_id'], ['click_num' => $link['clickNum'] + 1]);
        ## 行为通知
        if ($radar['actionNotice'] === 1) {
            $this->actionNotice($radar['corpId'], $employee['wxUserId'], $content);
        }
        ## 返回数据处理
        if ($radar['type'] === 3) {
            $radar['article'] = json_decode($radar['article'], true, 512, JSON_THROW_ON_ERROR);
        }
        if ($radar['type'] === 3 && $radar['articleType'] === 2) {
            $radar['article']['cover_url'] = file_full_url($radar['article']['cover_url']);
        }
        $radar['employeeName'] = '';
        $radar['employeeAvatar'] = '';
        if ($radar['employeeCard'] === 1) {
            $employee = $this->workEmployeeService->getWorkEmployeeById($link['employeeId'], ['name', 'avatar']);
            $radar['employeeName'] = $employee['name'];
            $radar['employeeAvatar'] = file_full_url($employee['avatar']);
        }
        return $radar;
    }

    /**
     * @throws \JsonException
     */
    private function actionNotice(int $corpId, string $wxUserId, string $content): void
    {
        $this->messageRemind->sendToEmployee($corpId, $wxUserId, 'text', $content);
    }

    private function tag(int $contactId, array $tags, int $corpId): void
    {
        $data = ['contactId' => 0, 'employeeId' => 0, 'tagArr' => array_column($tags, 'tagid'), 'employeeWxUserId' => '', 'contactWxExternalUserid' => ''];
        ## 客户id
        $data['contactId'] = $contactId;
        ## 员工id
        $contactEmployee = $this->workContactEmployeeService->getWorkContactEmployeeByCorpIdContactId($contactId, $corpId, ['employee_id']);
        $data['employeeId'] = $contactEmployee['employeeId'];
        ## 客户
        $contact = $this->workContactService->getWorkContactById($contactId, ['wx_external_userid']);
        $data['contactWxExternalUserid'] = $contact['wxExternalUserid'];
        ## 员工
        $employee = $this->workEmployeeService->getWorkEmployeeById($contactEmployee['employeeId'], ['wx_user_id']);
        $data['employeeWxUserId'] = $employee['wxUserId'];
        $data['corpId'] = $corpId;
        $this->autoTag($data);
    }
}
