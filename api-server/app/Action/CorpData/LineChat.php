<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\CorpData;

use App\Logic\CorpData\LineChatLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 首页数据统计折线图.
 *
 * Class LineChat.
 * @Controller
 */
class LineChat extends AbstractAction
{
    /**
     * @Inject
     * @var LineChatLogic
     */
    private $lineChatLogic;

    /**
     * @RequestMapping(path="/corpData/lineChat", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $corpId = user()['corpIds'];
        if (count($corpId) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        return $this->lineChatLogic->handle();
    }
}
