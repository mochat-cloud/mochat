<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Corp;

use App\Contract\CorpServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 企业微信授权- 详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @RequestMapping(path="/corp/show", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $corpId = $this->request->input('corpId');
        ## 查询数据
        $data = $this->corpService->getCorpById((int) $corpId);
        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '当前企业信息不存在');
        }

        $data['corpId']   = $data['id'];
        $data['wxCorpId'] = $data['wxCorpid'];
        $data['corpName'] = $data['name'];
        $data['eventCallback'] .= '?cid=' . $data['id'];
        unset($data['id'], $data['name'], $data['createdAt'], $data['updatedAt'], $data['deletedAt']);

        return $data;
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'corpId' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'corpId.required' => '企业授信ID 必填',
            'corpId.integer'  => '企业授信ID 必需为整数',
            'corpId.min  '    => '企业授信ID 不可小于1',
        ];
    }
}
