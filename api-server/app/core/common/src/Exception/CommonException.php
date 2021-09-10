<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Common\Exception;

use Hyperf\Server\Exception\ServerException;
use MoChat\App\Common\Constants\AppErrCode;

class CommonException extends ServerException
{
    public function __construct(int $code = 0, string $message = null, \Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = AppErrCode::getMessage($code);
            if (! $message && class_exists(AppErrCode::class)) {
                $message = AppErrCode::getMessage($code);
            }
        }
        parent::__construct($message, $code, $previous);
    }
}
