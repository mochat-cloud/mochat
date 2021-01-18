<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
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
        /** @var \Qbhy\HyperfAuth\AuthManager $auth */
        $auth = \Hyperf\Utils\ApplicationContext::getContainer()->get(\Qbhy\HyperfAuth\AuthManager::class);

        /** @var \App\Model\User $userModel */
        $userModel = $auth->guard('jwt')->user();

        if (! $userModel) {
            return [];
        }
        $resData  = $userModel->toArray();
        $request  = \Hyperf\Utils\Context::get(\Psr\Http\Message\ServerRequestInterface::class);
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
            'digest_alg'       => 'sha256', //可以用openssl_get_md_methods() 查看支持的加密方法
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
     * @param string $path 文件的路径
     * @param string $adapter 适配器
     * @return string 上传文件的url
     */
    function file_full_url(string $path, string $adapter = 'oss'): string
    {
        if (! $path) {
            return $path;
        }
        /** @var \Hyperf\Filesystem\FilesystemFactory $factory */
        $factory = \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\Filesystem\FilesystemFactory::class);
        $client  = $factory->get($adapter);

        switch ($adapter) {
            case 'oss':
                return $client->getAdapter()->getSignedUrl($path, 60 * 60 * 24);
            default:
                $config = $client->getConfig();
                return 'https://' . $config->get('bucket') . '.' . $config->get('endpoint') . $path;
        }
    }
}

if (! function_exists('oss_up_queue')) {
    /**
     * OSS队列上传.
     * @param array $files 例子
     *                     [
     *                     ["URL或本地文件路径", "oss存储路径", "本地路径时，是否删除本地文件，0否1是"],
     *                     ['https://example.com/path/demo.jpg' ,'path/demo.jpg'],
     *                     ['/tmp/path/demo.jpg' ,'path/demo.jpg',],
     *                     ].
     */
    function oss_up_queue(array $files): void
    {
        $ossQueue = make(\App\QueueService\Common\OssUpload::class);
        if (count($files) <= 10) {
            $ossQueue->up($files);
            return;
        }

        $newFiles = array_chunk($files, 10);
        foreach ($newFiles as $newFile) {
            $ossQueue->up($newFile);
        }
    }
}
