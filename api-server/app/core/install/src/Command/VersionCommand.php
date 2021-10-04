<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Install\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;

/**
 * @Command
 */
class VersionCommand extends HyperfCommand
{
    /**
     * MoChat 版本号.
     *
     * @var string
     */
    private const VERSION = '1.1.5';

    public function __construct()
    {
        parent::__construct('mochat:version');
    }

    public function handle()
    {
        $this->info(sprintf("MoChat Version: %s\n", self::VERSION));
        $this->info(sprintf('MoChat Github: %s', 'https://github.com/mochat-cloud/mochat'));
    }

    protected function configure()
    {
        $this->setDescription('Display MoChat version!');
    }
}
