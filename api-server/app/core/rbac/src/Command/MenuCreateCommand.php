<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Rbac\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
class MenuCreateCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        parent::__construct('menu:create');
        $this->container = $container;
        $this->config = $config;
    }

    public function handle()
    {
        $menuData = $this->getMenuData();

        if (empty($menuData)) {
            $this->error('菜单配置为空');
            return;
        }

        foreach ($menuData as $menu) {
            $menu['operate_name'] = '系统';
            $menu['created_at'] = date('Y-m-d H:i:s');
            $table = Db::table('rbac_menu');
            $lastId = $table->insertGetId($menu);
            $path = $menu['path'] . '-#' . $lastId . '#';
            $table->where('id', $lastId)->update(['path' => $path]);
            $this->info(sprintf("菜单创建成功：%d, name: %s, url: %s\n", $lastId, $menu['name'], $menu['link_url']));
        }
    }

    protected function configure()
    {
        $this->setDescription('Menu create');
    }

    protected function getMenuData()
    {
        return $this->config->get('menu', []);
    }
}
