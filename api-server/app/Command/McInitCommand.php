<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Command;

use App\Contract\UserServiceInterface;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Qbhy\HyperfAuth\AuthManager;
use Swoole\Coroutine\System;
use Symfony\Component\Console\Question\Question;

/**
 * @Command
 */
class McInitCommand extends HyperfCommand
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
     * @var UserServiceInterface
     */
    protected $userClient;

    /**
     * @var string sql安装文件
     */
    protected $sqlFile;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('mc:init');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('MoChat项目初始化');
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
        ## 创建.env
        if (! file_exists(BASE_PATH . '/.env')) {
            $content                                             = '';
            file_exists(BASE_PATH . '/.env.example') && $content = file_get_contents(BASE_PATH . '/.env.example');
            file_put_contents(BASE_PATH . '/.env', $content);
        }

        $this->setDomain();
        $this->mysqlInit();

        try {
            $this->mysqlDataInit();
        } catch (\Exception $e) {
            $this->warn('SQL文件初始化失败，请在继续下面配置前，手动执行SQL文件' . $this->sqlFile);
        }

        $this->redisInit();

        $this->userClient = $this->container->get(UserServiceInterface::class);
        $this->adminRegister();
        $this->tenant();

        $this->line('项目初始化完成，请登陆后，在', 'info');
    }

    /**
     * set value within a given file.
     *
     * @param string $key ...
     * @param null|string $val ...
     * @param bool $isNewLine ...
     * @param string $path ...
     */
    protected function setEnv(string $key, ?string $val = '', bool $isNewLine = false, string $path = ''): void
    {
        $path || $path = BASE_PATH . '/.env';
        $oldVal        = env($key, false);
        $newKeyVal     = $key . '=' . $val;
        ## 添加
        if ($oldVal === false) {
            $isNewLine && $newKeyVal = "\n" . $newKeyVal;
            file_put_contents($path, $newKeyVal, FILE_APPEND);
        } else {
            ## 修改
            $oldKeyVal = $key . '=' . $oldVal;
            file_put_contents($path, str_replace($oldKeyVal, $newKeyVal, file_get_contents($path)));
        }
    }

    /**
     * @param array $data ...
     * @param string $path ...
     */
    protected function setEnvs(array $data, string $path = ''): void
    {
        $rows = [];
        foreach ($data as $configKey => $item) {
            [$key, $val] = $item;
            $isNewLine   = $item[2] ?? false;

            $rows[] = [$key, $val];
            $this->setEnv($key, $val, $isNewLine, $path);
            if (! is_numeric($configKey)) {
                if ($configKey === 'redis.default.db' || $configKey === 'redis.default.port') {
                    $val = (int) $val;
                }
                $this->config->set($configKey, $val);
            }
        }

        $this->table(['属性名称', '属性值'], $rows);
        $this->info('重载配置成功');
    }

    /**
     * 设置前端、后端域名.
     */
    protected function setDomain(): void
    {
        appDomain:
        $appDomain = $this->ask('输入后端接口域名', env('APP_DOMAIN', 'http://api.mo.chat'));
        if (false === strpos($appDomain, 'http')) {
            $this->warn('后端接口域名请添加 http(s)://');
            goto appDomain;
        }
        $this->setEnvs([['APP_DOMAIN', $appDomain]]);

        jsDomain:
        $jsDomain  = $this->ask('输入H5侧边工具栏前端域名', env('JS_DOMAIN', 'http://h5.mo.chat'));
        if (false === strpos($jsDomain, 'http')) {
            $this->warn('H5侧边工具栏前端域名请添加 http(s)://');
            goto jsDomain;
        }
        $this->setEnvs([['JS_DOMAIN', $jsDomain]]);
    }

    /**
     * @deprecated
     * 设置阿里云OSS.
     */
    protected function ossInit(): void
    {
        $this->setEnvs([
            [
                'OSS_ACCESS_ID',
                $this->ask('输入阿里云oss:AccessKey ID', env('OSS_ACCESS_ID', '')),
            ],
            [
                'OSS_ACCESS_SECRET',
                $this->ask('输入阿里云oss:AccessKey Secret', env('OSS_ACCESS_SECRET', '')),
            ],
            [
                'OSS_BUCKET',
                $this->ask('输入阿里云oss:Bucket', env('OSS_BUCKET', 'mochat')),
            ],
            [
                'OSS_ENDPOINT',
                $this->ask('输入阿里云oss:endpoint', env('OSS_ENDPOINT', 'oss-cn-beijing.aliyuncs.com')),
            ],
        ]);
    }

    /**
     * 管理员注册.
     * @return bool ...
     */
    protected function adminRegister(): bool
    {
        ## 手机号
        $phone = $this->askWithValidator('输入管理员的手机号', function ($value) {
            if (is_numeric($value) && strlen($value) === 11) {
                return $value;
            }
            throw new \Exception('手机号错误');
        });

        $user = $this->userClient->getUserByPhone($phone, ['id']);
        if (! empty($user)) {
            $this->warn('已经存在该管理员');
            return true;
        }

        ## 密码
        $password  = $this->secret('输入管理员密码', false);
        $encrypted = $this->authManager->guard('jwt')->getJwtManager()->getEncrypter()->signature($password);
        return Db::table('user')->insert([
            'phone'        => $phone,
            'password'     => $encrypted,
            'status'       => 1,
            'isSuperAdmin' => 1,
        ]);
    }

    /**
     * 数据库初始化.
     */
    protected function mysqlInit(): void
    {
        $data = [
            'databases.default.host' => [
                'DB_HOST',
                $this->ask('输入mysql.host', env('DB_HOST', '127.0.0.1')),
            ],
            'databases.default.port' => [
                'DB_PORT',
                $this->ask('输入mysql.port', env('DB_PORT', '3306')),
            ],
            'databases.default.database' => [
                'DB_DATABASE',
                $this->ask('输入mysql.database', env('DB_DATABASE', 'mochat')),
            ],
            'databases.default.username' => [
                'DB_USERNAME',
                $this->ask('输入mysql.username', env('DB_USERNAME', 'root')),
            ],
            'databases.default.password' => [
                'DB_PASSWORD',
                $this->ask('输入mysql.password', env('DB_PASSWORD', '')),
            ],
        ];

        try {
            $this->setEnvs($data);
            Db::select('show databases like \'' . $data['databases.default.database'][1] . '\'');
            $this->mysqlDatabase = $data['databases.default.database'][1];
        } catch (\Exception $e) {
            $this->error(sprintf('mysql设置错误:%s,请重新填写', $e->getPrevious()->getMessage()));
            $this->mysqlInit();
        }
    }

    protected function redisInit(): void
    {
        $data = [
            'redis.default.host' => [
                'REDIS_HOST',
                $this->ask('输入redis.host', env('REDIS_HOST', '127.0.0.1')),
            ],
            'redis.default.port' => [
                'REDIS_PORT',
                $this->ask('输入redis.port', env('REDIS_PORT', '6379')),
            ],
            'redis.default.auth' => [
                'REDIS_AUTH',
                $this->ask('输入redis.auth', env('REDIS_AUTH', '')),
            ],
            'redis.default.db' => [
                'REDIS_DB',
                $this->ask('输入redis.db', env('REDIS_DB', '0')),
            ],
        ];
        try {
            $this->setEnvs($data);
            $this->container->get(Redis::class)->ping('demo');
        } catch (\Exception $e) {
            $falseMsg = $e->getPrevious() ? $e->getPrevious()->getMessage() : '';
            $this->error(sprintf('redis设置错误:%s,请重新填写', $falseMsg));
            $this->redisInit();
        }
    }

    protected function tenant(): void
    {
        ## 租户-服务器实例IP
        $curlIp = System::exec('curl cip.cc');
        $ipInfo = empty($curlIp['output']) ? '' : "当前IP:\n" . $curlIp['output'];
        $this->line($ipInfo, 'info');

        $ips = $this->ask('输入各个开发、生产阶段的服务器IP(用来设置微信白名单:以英文逗号隔开)', '');
        $ips = json_encode(explode(',', $ips));

        Db::table('tenant')->insert([
            'server_ips' => $ips,
        ]);
    }

    private function mysqlDataInit(): void
    {
        $version = Db::select('select VERSION() AS version;');
        $version = empty($version[0]->version) ? 5.6 : (float) $version[0]->version;
        if ($version < 5.7) {
            throw new \RuntimeException('mysql版本号错误，需要版本号 >= 5.7');
        }

        if (! empty(Db::select('SHOW TABLES LIKE "mc_user"'))) {
            $this->info('sql初始化已经完成，无需再重新初始化');
            return;
        }
        $this->sqlFile = BASE_PATH . '/storage/install/mochat.sql';
        if (file_exists($this->sqlFile)) {
            $sqlArr = explode(';', file_get_contents($this->sqlFile));
            foreach ($sqlArr as $item) {
                $item = trim($item);
                if (empty($item)) {
                    continue;
                }
                Db::statement((string) Db::raw($item . ';'));
            }
        } else {
            $this->error($this->sqlFile . '初始化SQL文件错误');
        }
    }
}
