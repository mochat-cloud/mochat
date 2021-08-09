<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace HyperfTest\Cases;

use HyperfTest\HttpTestCase;

/**
 * /common/upload#post.
 * @internal
 * @coversNothing
 */
class CommonUploadTest extends HttpTestCase
{
    public $pngFile = '/tmp/mtFile.png';

    public $tgzFile = '/tmp/mtFile.tgz';

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        file_put_contents($this->pngFile, file_get_contents('https://hyperf.wiki/2.1/logo.png'));
        file_put_contents($this->tgzFile, file_get_contents('https://pecl.php.net/get/SeasLog-2.2.0.tgz'));
    }

    public function __destruct()
    {
        file_exists($this->pngFile) && unlink($this->pngFile);
        file_exists($this->tgzFile) && unlink($this->tgzFile);
    }

    public function post($uri, $data = [], $headers = [])
    {
        $reqData = [
            'headers' => $headers,
        ];
        empty($data['form_params']) || $reqData              = $data['form_params'];
        empty($data['multipart']) || $reqData['multipart'][] = [
            'name'     => $data['multipart']['name'],
            'contents' => fopen($data['multipart']['file'], 'rb'),
            'filename' => basename($data['multipart']['file']),
        ];
        $response = $this->request('POST', $uri, $reqData);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function testFile(): void
    {
        $res = $this->post(
            '/common/upload',
            [
                'multipart' => [
                    'name' => 'file',
                    'file' => $this->pngFile,
                ],
                'form_params' => ['path' => '22'],
            ]
        );
        self::assertSame(200, $res['code'], $res['msg']);

        $res = $this->post(
            '/common/upload',
            [
                'multipart' => [
                    'name' => 'file',
                    'file' => $this->tgzFile,
                ],
                'form_params' => ['path' => '../'],
            ]
        );
        self::assertNotSame(200, $res['code'], $res['msg']);
    }
}
