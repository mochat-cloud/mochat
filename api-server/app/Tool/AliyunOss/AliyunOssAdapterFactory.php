<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Tool\AliyunOss;

use Hyperf\Filesystem\Contract\AdapterFactoryInterface;
use League\Flysystem\AdapterInterface;

class AliyunOssAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @throws \Exception
     */
    public function make(array $options): AdapterInterface
    {
        return new OssAdapter($options);
    }
}
