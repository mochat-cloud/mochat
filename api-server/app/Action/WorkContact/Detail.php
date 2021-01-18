<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkContact;

use App\Contract\WorkContactServiceInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 客户详情.
 * @Controller
 */
class Detail extends AbstractAction
{
    /**
     * @RequestMapping(path="/workContact/detail", methods="GET")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $externalUserId = $this->request->query('wxExternalUserid');
        if (! $externalUserId) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '微信userId必须');
        }

        $res = $this->container->get(WorkContactServiceInterface::class)->getWorkContactByWxExternalUserId($externalUserId, [
            'id', 'name', 'avatar', 'corp_id',
        ]);
        if (empty($res)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '客户不存在');
        }
        $res['avatar'] && $res['avatar'] = file_full_url($res['avatar']);

        return $res;
    }
}
