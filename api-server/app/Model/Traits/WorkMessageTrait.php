<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Model\Traits;

use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

trait WorkMessageTrait
{
    /**
     * @var int 分表总数
     */
    protected $totalTable = 10;

    /**
     * 分表初始化.
     * @param int $corpId ...
     * @return $this ...
     */
    public function initTable(int $corpId = 0): self
    {
        if ($corpId === 0) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '会话内容存档未指定分表ID');
        }
        $table = $this->getTable();
        $table .= '_' . $corpId % $this->totalTable;
        $this->setTable($table);

        return $this;
    }
}
