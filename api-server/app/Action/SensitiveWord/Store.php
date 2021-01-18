<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\SensitiveWord;

use App\Action\Corp\Traits\RequestTrait;
use App\Constants\BusinessLog\Event;
use App\Contract\BusinessLogServiceInterface;
use App\Contract\CorpServiceInterface;
use App\Contract\SensitiveWordServiceInterface;
use App\Logic\User\Traits\UserTrait;
use App\Middleware\PermissionMiddleware;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 敏感词词库 - 创建提交.
 *
 * Class Store.
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;
    //use RequestTrait;
    use UserTrait;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var SensitiveWordServiceInterface
     */
    protected $sensitiveWordService;

    /**
     * 企业ID.
     * @var int
     */
    protected $corpId;

    /**
     * @Inject
     * @var BusinessLogServiceInterface
     */
    private $businessLogService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/sensitiveWord/store", methods="post")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $this->corpId = (int) $user['corpIds'][0];
        $params       = $this->request->all();
        $this->validated($params, 'store');

        ## 创建
        $res = $this->createSensitiveWord($params, $user);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '添加失败');
        }

        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'name'    => 'required | string | min:1 | bail',
            'groupId' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'name.required'    => '敏感词分组名称 必填',
            'name.string'      => '敏感词分组名称 必须为字符串',
            'name.min'         => '敏感词分组名称 字符串长度不可小于1',
            'groupId.required' => '敏感词分组id 必填',
            'groupId.integer'  => '敏感词分组id 必须为整型',
        ];
    }

    /**
     * 创建敏感词.
     * @param array $params 参数
     * @param array $user 用户参数
     * @return bool|int
     */
    private function createSensitiveWord(array $params, array $user): bool
    {
        if (strpos($params['name'], ',')) {
            $name = explode(',', $params['name']);
        } else {
            $name = $params['name'];
        }

        if (is_array($name)) {
            ## 批量添加敏感词
            return $this->createSensitiveWords($name, $params, $user);
        }

        return $this->createOne((string) $name, $params, $user);
    }

    /**
     * 敏感词检测-是否存在.
     *
     * @param string $name 敏感词
     */
    private function nameIsUnique(string $name): bool
    {
        $client    = $this->container->get(SensitiveWordServiceInterface::class);
        $existData = $client->getSensitiveWordByNameCorpId($name, $this->corpId);
        if (! empty($existData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, $name . '-该敏感词已存在');
        }
        return true;
    }

    /**
     * 批量创建.
     *
     * @param array $name 敏感词名称
     * @param array $params 接收参数
     * @param array $user 用户参数
     */
    private function createSensitiveWords(array $name, array $params, array $user): bool
    {
        ## 开始
        DB::beginTransaction();
        try {
            foreach ($name as $key => $val) {
                $this->nameIsUnique($val);
                $data = [
                    'name'     => $val,
                    'group_id' => $params['groupId'],
                    'corp_id'  => $this->corpId,
                    'status'   => 1,
                ];
                ## 创建敏感词
                $sensitiveId = $this->sensitiveWordService->createSensitiveWord($data);
                if (! $sensitiveId) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '敏感词创建失败');
                }
                ## 记录业务日志
                $businessLog = [
                    'business_id'  => $sensitiveId,
                    'params'       => json_encode($data),
                    'event'        => Event::SENSITIVE_WORD_CREATE,
                    'operation_id' => $user['workEmployeeId'],
                    'created_at'   => date('Y-m-d H:i:s'),
                ];
                $businessId = $this->businessLogService->createBusinessLog($businessLog);
                if (! $businessId) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '敏感词创建失败');
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '敏感词创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage());
        }

        return true;
    }

    /**
     * 单条创建.
     *
     * @param string $name 敏感词名称
     * @param array $params 接受参数
     * @param array $user 用户参数
     */
    private function createOne(string $name, array $params, array $user): bool
    {
        $this->nameIsUnique((string) $name);
        $data = [
            'name'     => $name,
            'group_id' => $params['groupId'],
            'corp_id'  => $this->corpId,
            'status'   => 1,
        ];

        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建敏感词
            $sensitiveId = $this->sensitiveWordService->createSensitiveWord($data);
            ## 记录业务日志
            $businessLog = [
                'business_id'  => $sensitiveId,
                'params'       => json_encode($data),
                'event'        => Event::SENSITIVE_WORD_CREATE,
                'operation_id' => $user['workEmployeeId'],
                'created_at'   => date('Y-m-d H:i:s'),
            ];
            $this->businessLogService->createBusinessLog($businessLog);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '敏感词创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '敏感词创建失败');
        }

        return true;
    }
}
