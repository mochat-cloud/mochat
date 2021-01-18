<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkMessage;

use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkMessageIndexServiceInterface;
use App\Logic\User\Traits\UserTrait;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;

/**
 * @Controller
 */
class FromUsers extends AbstractAction
{
    use UserTrait;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeClient;

    /**
     * @Inject
     * @var WorkMessageIndexServiceInterface
     */
    protected $workMsgIndexClient;

    /**
     * @RequestMapping(path="/workMessage/fromUsers", methods="GET")
     */
    public function handle(): array
    {
        ## 请求参数.验证
        $corpId = $this->corpId();
        $name   = $this->request->query('name', '');

        ## 已经存在的聊天员工
        $msgIndex = $this->workMsgIndexClient->getWorkMessageIndicesUniqueColumns($corpId, ['from_id']);
        if (empty($msgIndex)) {
            return [];
        }

        ## 模糊搜索员工
        $data = $this->workEmployeeClient->getWorkEmployeesByIdName(array_column($msgIndex, 'fromId'), $name, [
            'id', 'name', 'avatar',
        ]);

        return array_map(function ($item) {
            $item['avatar'] && $item['avatar'] = file_full_url($item['avatar']);
            return $item;
        }, $data);
    }
}
