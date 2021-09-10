<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Tenant\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\User\Contract\UserContract;
use Psr\Container\ContainerInterface;
use Qbhy\HyperfAuth\AuthManager;
use Swoole\Coroutine\System;
use Symfony\Component\Console\Question\Question;

/**
 * @Command
 */
class CreateCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var AuthManager
     */
    protected $authManager;

    /**
     * @Inject
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var UserContract
     */
    protected $userService;

    /** @var string */
    protected $phone;

    /** @var string */
    protected $password;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('tenant:create');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('创建新的租户');
    }

    /**
     * Prompt the user for input with validator.
     *
     * @param null|mixed $default
     * @return mixed
     */
    public function askWithValidator(string $question, callable $validator, $default = null)
    {
        $question = new Question($question, $default);
        $question->setValidator($validator);
        return $this->output->askQuestion($question);
    }

    public function handle(): void
    {
        $this->userService = $this->container->get(UserContract::class);
        $tenantId = $this->createTenant();
        if ($tenantId === 0) {
            return;
        }
        $this->createAdminUser($tenantId);

        $this->line("租户创建成功: \n账号：{$this->phone} \n密码：{$this->password}", 'info');
    }

    protected function createTenant(): int
    {
        try {
            $name = $this->askWithValidator('输入租户的名称', function ($value) {
                $value = trim($value);
                if ($value !== '') {
                    return $value;
                }
                throw new \Exception('租户的名称不能为空！');
            });
        } catch (\Exception $e) {
            $this->warn($e->getMessage());
            return 0;
        }

        ## 租户-服务器实例IP
        $curlIp = System::exec('curl cip.cc');
        $ipInfo = empty($curlIp['output']) ? '' : "当前IP:\n" . $curlIp['output'];
        $this->line($ipInfo, 'info');

        $ips = $this->ask('输入各个开发、生产阶段的服务器IP(用来设置微信白名单:以英文逗号隔开)', '');
        $ips = json_encode(explode(',', $ips));

        return Db::table('tenant')->insertGetId([
            'name' => $name,
            'server_ips' => $ips,
        ]);
    }

    /**
     * 管理员注册.
     * @return bool ...
     */
    protected function createAdminUser(int $tenantId): bool
    {
        ## 手机号
        try {
            $this->phone = $this->askWithValidator('输入租户管理员的手机号', function ($value) {
                $value = trim($value);
                if (is_numeric($value) && strlen($value) === 11) {
                    return $value;
                }
                throw new \Exception('手机号错误！');
            });
        } catch (\Exception $e) {
            $this->warn($e->getMessage());
            return true;
        }

        $user = $this->userService->getUserByPhone($this->phone, ['id']);
        if (! empty($user)) {
            $this->warn('已经存在该租户');
            return true;
        }

        ## 密码
        $this->password = trim($this->secret('输入租户管理员密码', false));
        $encrypted = $this->authManager->guard('jwt')->getJwtManager()->getEncrypter()->signature($this->password);
        return Db::table('user')->insert([
            'tenant_id' => $tenantId,
            'phone' => $this->phone,
            'password' => $encrypted,
            'status' => 1,
            'isSuperAdmin' => 1,
        ]);
    }
}
