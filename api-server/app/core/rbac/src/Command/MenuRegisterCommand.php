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
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\HttpServer\Router\RouteCollector;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command
 */
class MenuRegisterCommand extends HyperfCommand
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
        parent::__construct('menu:register');
        $this->container = $container;
        $this->config = $config;
    }

    public function handle()
    {
        $path = $this->input->getOption('path');
        $server = $this->input->getOption('server');

        $factory = $this->container->get(DispatcherFactory::class);
        $router = $factory->getRouter($server);
        $this->show(
            $this->analyzeRouter($server, $router, $path),
            $this->output
        );
    }

    protected function configure()
    {
        $this->setDescription('Register the routes to menu.')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified route information by path')
            ->addOption('server', 'S', InputOption::VALUE_OPTIONAL, 'Which server you want to describe routes.', 'http');
    }

    protected function analyzeRouter(string $server, RouteCollector $router, ?string $path)
    {
        $data = [];
        [$staticRouters, $variableRouters] = $router->getData();
        foreach ($staticRouters as $method => $items) {
            foreach ($items as $handler) {
                $this->analyzeHandler($data, $server, $method, $path, $handler);
            }
        }
        foreach ($variableRouters as $method => $items) {
            foreach ($items as $item) {
                if (is_array($item['routeMap'] ?? false)) {
                    foreach ($item['routeMap'] as $routeMap) {
                        $this->analyzeHandler($data, $server, $method, $path, $routeMap[0]);
                    }
                }
            }
        }
        return $data;
    }

    protected function analyzeHandler(array &$data, string $serverName, string $method, ?string $path, Handler $handler)
    {
        $uri = $handler->route;
        if (! is_null($path) && ! Str::contains($uri, $path)) {
            return;
        }
        $unique = "{$serverName}|{$uri}";
        if (isset($data[$unique])) {
            $data[$unique]['method'][] = $method;
        } else {
            $data[$unique] = [
                'method' => [$method],
                'uri' => $uri,
            ];
        }
    }

    private function show(array $data, OutputInterface $output)
    {
        $data = array_values($data);
        $newData = [];
        foreach ($data as $val) {
            if (strpos($val['uri'], 'dashboard') === false) {
                continue;
            }
            $newData[] = sprintf('%s#%s', $val['uri'], strtolower($val['method'][0]));
        }

        sort($newData);
        foreach ($newData as $val) {
            echo $val . "\n";
        }
    }
}
