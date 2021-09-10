<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

if (! function_exists('di_get')) {
    /**
     * 获取容器对象
     * @return null|mixed|\Psr\Container\ContainerInterface
     */
    function di_get(?string $id = null)
    {
        if (! class_exists(\Hyperf\Utils\ApplicationContext::class)) {
            return null;
        }
        $container = \Hyperf\Utils\ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }
        return $container;
    }
}

if (! function_exists('user')) {
    /**
     * 获取后台用户信息 example如下
     * {
     *     "id": "用户ID",
     *     "phone": "手机号",
     *     "password": "密码",
     *     "name": "昵称",
     *     "gender": "性别",
     *     "department": "部门",
     *     "position": "职位",
     *     "loginTime": "登陆时间",
     *     "status": "状态 0未启用 1正常 2禁用",
     *     "corpIds": "企业表ID",
     *     "workEmployeeId": "当前企业对应的员工ID",
     *     "tenantId": "租户ID",
     *     "roleId": "角色id",
     *     "isSuperAdmin": "是否为超级管理员", 0否1是
     *     "dataPermission": "(当前路由)数据权限 0全企业 1本部门 2本人",  # 权限中间件才会有此字段
     *     "deptEmployeeIds": ["本人部门下所有员工id"], # 权限中间件才会有此字段
     *     "createdAt": "创建时间",
     *     "updatedAt": "修改时间",
     *     "deletedAt": "删除时间"
     * }.
     *
     * @param string $field 字段名称
     * @return array|string 用户信息|用户字段信息
     */
    function user(?string $field = null)
    {
//        /** @var \Qbhy\HyperfAuth\AuthManager $auth */
//        $auth = \Hyperf\Utils\ApplicationContext::getContainer()->get(\Qbhy\HyperfAuth\AuthManager::class);
//
//        /** @var \App\Model\User $userModel */
//        $userModel = $auth->guard('jwt')->user();
//
//        if (! $userModel) {
//            return [];
//        }
//        $resData  = $userModel->toArray();
        $request = \Hyperf\Utils\Context::get(\Psr\Http\Message\ServerRequestInterface::class);
        $resData = $request->getAttribute('user');

        if (! $resData) {
            return [];
        }

        $userData = $request->getAttributes();
        foreach ($userData as $key => $val) {
            if (strpos($key, '_user_') === 0) {
                $resData[substr($key, 6)] = $val;
            }
        }
        $field && $resData = $resData[$field];

        return $resData;
    }
}

if (! function_exists('rsa_keys')) {
    /**
     * @return array 生成RSA公私钥
     */
    function rsa_keys(): array
    {
        $ssl = openssl_pkey_new([
            'digest_alg' => 'sha256', //可以用openssl_get_md_methods() 查看支持的加密方法
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        ## 私钥
        openssl_pkey_export($ssl, $rsaPrivateKey);

        ## 公钥
        $rsaPublicKey = openssl_pkey_get_details($ssl)['key'];

        return [$rsaPrivateKey, $rsaPublicKey];
    }
}

if (! function_exists('file_full_url')) {
    /**
     * 获取文件完整URL.
     * @param string $path 文件的路径
     * @return string 上传文件的url
     */
    function file_full_url(string $path, string $fileDriver = ''): string
    {
        return \Hyperf\Utils\ApplicationContext::getContainer()->get(\MoChat\App\Utils\FilesystemExt::class)->getFullUrl($path);
    }
}

if (! function_exists('file_upload_queue')) {
    /**
     * OSS队列上传.
     * @param array $files 例子
     *                     [
     *                     ["URL或本地文件路径", "oss存储路径", "本地路径时，是否删除本地文件，0否1是"],
     *                     ['https://example.com/path/demo.jpg' ,'path/demo.jpg'],
     *                     ['/tmp/path/demo.jpg' ,'path/demo.jpg',],
     *                     ].
     */
    function file_upload_queue(array $files): void
    {
        $asyncFileUpload = make(\MoChat\App\Common\QueueService\AsyncFileUpload::class);
        if (count($files) <= 10) {
            $asyncFileUpload->upload($files);
            return;
        }

        $newFiles = array_chunk($files, 10);
        foreach ($newFiles as $newFile) {
            $asyncFileUpload->upload($newFile);
        }
    }
}

if (! function_exists('service_map')) {
    /**
     * 模型服务与契约的依赖配置.
     * @param string $path 契约与服务的相对路径
     * @return array 依赖数据
     */
    function service_map(string $path, string $namespacePrefix): array
    {
        $namespacePrefix = ltrim($namespacePrefix, '\\');
        $services = readFileName($path . '/Service');

        $dependencies = [];
        foreach ($services as $service) {
            $contractFilename = str_replace('Service', 'Contract', $service);
            $dependencies[$namespacePrefix . '\\Contract\\' . $contractFilename] = $namespacePrefix . '\\Service\\' . $service;
        }

        return $dependencies;
    }
}

if (! function_exists('register_service_map')) {
    /**
     * 模型服务与契约的依赖配置.
     * @param string $path 契约与服务的相对路径
     * @return array 依赖数据
     */
    function register_service_map(string $path, string $namespacePrefix = 'MoChat\\App\\'): array
    {
        $dependencies = [];
        if (! is_dir($path)) {
            return $dependencies;
        }

        $files = scandir($path);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..', '.DS_Store'])) {
                continue;
            }

            if (! is_dir($path . '/' . $file . '/src')) {
                continue;
            }
            $arr = explode('-', $file);
            array_walk($arr, function (&$val) {
                $val = ucfirst($val);
            });
            $namespace = $namespacePrefix . implode('', $arr);
            $dependencies = array_merge(service_map($path . '/' . $file . '/src', $namespace), $dependencies);
        }
        return $dependencies;
    }
}

if (! function_exists('rebind_app')) {
    /**
     * 重置APP.
     *
     * @param $app
     * @return mixed
     */
    function rebind_app($app, RequestInterface $request)
    {
        $get = $request->getQueryParams();
        $post = $request->getParsedBody();
        $cookie = $request->getCookieParams();
        $uploadFiles = $request->getUploadedFiles() ?? [];
        $server = $request->getServerParams();
        $xml = $request->getBody()->getContents();
        $files = [];
        /** @var \Hyperf\HttpMessage\Upload\UploadedFile $v */
        foreach ($uploadFiles as $k => $v) {
            $files[$k] = $v->toArray();
        }
        $newRequest = new Request($get, $post, [], $cookie, $files, $server, $xml);
        $newRequest->headers = new HeaderBag($request->getHeaders());
        $app->rebind('request', $newRequest);
        $app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);

        $handler = new CoroutineHandler();

        // 设置 HttpClient，部分接口直接使用了 http_client。
        $config = $app['config']->get('http', []);
        $config['handler'] = $stack = HandlerStack::create($handler);
        $app->rebind('http_client', new Client($config));

        // 部分接口在请求数据时，会根据 guzzle_handler 重置 Handler
        $app['guzzle_handler'] = $handler;

        if ($app instanceof \EasyWeChat\OfficialAccount\Application) {
            // 如果使用的是 OfficialAccount，则还需要设置以下参数
            $app->oauth->setGuzzleOptions([
                'http_errors' => false,
                'handler' => $stack,
            ]);
        }

        return $app;
    }
}
