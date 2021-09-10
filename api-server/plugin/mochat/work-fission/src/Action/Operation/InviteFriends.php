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

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;

/**
 * 任务宝H5 - 获取我的助力好友.
 *
 * Class Store.
 * @Controller
 */
class InviteFriends extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var \Laminas\Stdlib\RequestInterface
     */
    protected $request;

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
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * Statistics constructor.
     */
    public function __construct(RequestInterface $request, WorkFissionContactContract $workFissionContactService, WorkFissionContract $workFissionService, WorkContactContract $workContactService)
    {
        $this->request = $request;
        $this->workFissionContactService = $workFissionContactService;
        $this->workFissionService = $workFissionService;
        $this->workContactService = $workContactService;
    }

    /**
     * @RequestMapping(path="/operation/workFission/inviteFriends", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);

        return $this->getUserList($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return ['union_id' => 'required',
            'fission_id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'union_id.required' => '客户微信id 必填',
            'fission_id' => '活动id 必填',
        ];
    }

    /**
     * @param $params
     */
    private function getUserList($params): array
    {
        $columns = ['nickname', 'union_id', 'avatar', 'created_at', 'loss'];
        $options = [
            'orderByRaw' => 'id desc',
        ];
        $user = $this->workFissionContactService->getWorkFissionContactByUnionId($params['union_id'], ['id']);
        $list = $this->workFissionContactService->getWorkFissionContactList(['contact_superior_user_parent' => $user['id'], 'fission_id' => $params['fission_id']], $columns, $options);
        $fission = $this->workFissionService->getWorkFissionById((int) $params['fission_id']);

        foreach ($list['data'] as $key => $val) {
            $list['data'][$key]['fail'] = 0;
            if ($fission['deleteInvalid'] == 1 && $val['loss'] == 1) {
                $list['data'][$key]['fail'] = 1;
            }
            $url = $this->workContactService->getWorkContactByUnionId($val['unionId'], ['avatar']);
            $list['data'][$key]['avatar'] = file_full_url($url['avatar']);
        }
        return $list['data'];
    }
}
