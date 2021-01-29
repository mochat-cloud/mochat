<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Agent;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;

/**
 * @Controller
 */
class TxtVerifyShow extends AbstractAction
{
    /**
     * @RequestMapping(path="/{wxVerifyTxt:WW_verify_[0-9a-zA-Z]{16}\.txt$}", methods="GET")
     * @return null|string ...
     */
    public function handle(): ?string
    {
        $code = null;
        preg_match(
            '/WW_verify_([0-9a-zA-Z]{16})\.txt/',
            $this->request->route('wxVerifyTxt'),
            $code
        );
        isset($code[1]) && $code = $code[1];

        return $code;
    }
}
