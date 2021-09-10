<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\AutoTag\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\AutoTag\Contract\AutoTagContract;

/**
 * 自动打标签-增加.
 *
 * Class StoreLogic
 */
class StoreLogic
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Inject
     * @var AutoTagContract
     */
    protected $autoTagService;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理参数
        $params = $this->handleParam($user, $params);

        ## 创建标签
        return $this->createAutoTag($params);
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        $tagRule = array_column($params['tag_rule'], 'tags');
        $tags = array_column($tagRule[0], 'tagid');
        ## 基本信息
        return [
            'type' => $params['type'],
            'name' => $params['name'],
            'employees' => isset($params['employees']) ? implode(',', $params['employees']) : '',
            'fuzzy_match_keyword' => isset($params['fuzzy_match_keyword']) ? implode(',', $params['fuzzy_match_keyword']) : '',
            'exact_match_keyword' => isset($params['exact_match_keyword']) ? implode(',', $params['exact_match_keyword']) : '',
            'tag_rule' => json_encode($params['tag_rule']),
            'tags' => implode(',', $tags),
            'on_off' => 1,
            'tenant_id' => isset($params['tenant_id']) ? $params['tenant_id'] : 0,
            'corp_id' => $user['corpIds'][0],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 创建活动.
     * @param array $params 参数
     * @return array 响应数值
     */
    private function createAutoTag(array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $id = $this->autoTagService->createAutoTag($params);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '标签创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [$id];
    }
}
