<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkMessageConfig;

use App\Action\WorkMessageConfig\Traits\RequestTrait;
use App\Contract\CorpServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkMessageConfigServiceInterface;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 添加 - 动作.
 * @Controller
 */
class CorpStore extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @RequestMapping(path="/workMessageConfig/corpStore", methods="POST")
     */
    public function handle(): array
    {
        ## 请求参数
        $params = $this->request->inputs(
            ['socialCode', 'chatAdmin', 'chatAdminPhone', 'chatAdminIdcard', 'chatStatus', 'chatApplyStatus', 'corpId'],
            ['chatStatus' => 0, 'chatApplyStatus' => 1, 'corpId' => 0]
        );
        ++$params['chatApplyStatus'];

        ## 类型验证
        $this->validated($params, 'store');

        ## 业务验证
        if ($params['corpId']) {
            $employeeClient = $this->container->get(WorkEmployeeServiceInterface::class);
            $employeeData   = $employeeClient->getWorkEmployeeByLogUserId((int) user('id'), ['id', 'corp_id']);
            $corpIds        = array_column($employeeData, 'id', 'corpId');
            if (! isset($corpIds[$params['corpId']])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '当前管理员不属于此企业，无权限操作');
            }
        } else {
            $corpIds = user('corpIds');
            if (count($corpIds) !== 1) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '请选择一个企业，再进行操作');
            }
            $params['corpId'] = user('corpIds')[0];
        }
        $configClient = $this->container->get(WorkMessageConfigServiceInterface::class);
        $corpClient   = $this->container->get(CorpServiceInterface::class);
        $existData    = $configClient->getWorkMessageConfigByCorpId($params['corpId'], ['id']);
        if ($existData) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该企业已经完成会话内容基本配置');
        }

        ## 入库
        Db::beginTransaction();
        try {
            $corpClient->updateCorpById($params['corpId'], ['social_code' => $params['socialCode']]);
            $insertId = $configClient->createWorkMessageConfig($params);

            Db::commit();
        } catch (\Throwable $ex) {
            Db::rollBack();
            throw new CommonException(ErrorCode::SERVER_ERROR, '添加失败');
        }

        return ['id' => $insertId];
    }
}
