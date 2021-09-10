<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Radar\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Radar\Logic\StoreLogic;

/**
 * 互动雷达 - 增加.
 *
 * Class RadarArticle.
 * @Controller
 */
class RadarArticle extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var StoreLogic
     */
    protected $storeLogic;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/radar/radarArticle", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $url = $this->request->input('url');
        ## method  file
        $html = file_get_contents($url);
        ## 去除微信中的抓取干扰代码
        $html = str_replace('<!--headTrap<body></body><head></head><html></html>-->', '', $html);
        ## 文章内容
        preg_match_all('/<div class="rich_media_content ".*?>.*?<\\/div>/ism', $html, $content);
        ## 图片、视频不显示处理
        $content = $content[0][0];
        $content = str_replace(['data-src', 'preview.html'], ['src', 'player.html'], $content);

        ## method  url
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $str_7 = substr($result, strripos($result, 'twitter:title') + 24);
        $str_8 = substr($str_7, 0, strrpos($str_7, 'twitter:creator') - 23); //公众号文章标题
        $str_9 = substr($result, strripos($result, 'twitter:image') + 24);
        $str_10 = substr($str_9, 0, strrpos($str_9, 'twitter:title') - 23); //公众号文章封面图
        $str_11 = substr($result, strripos($result, 'twitter:description') + 30);
        $str_12 = substr($str_11, 0, strrpos($str_11, 'var testRdmUrl') - 110);
        $str_13 = substr($str_12, 0, strrpos($str_12, '>') - 3); //公众号文章摘要
        $str_14 = substr($result, 0, strrpos($result, 'og:title') - 27);
        $str_15 = substr($str_14, strripos($str_14, 'author') + 17); //公众号文章作者
//        $str_16 = substr($result, strripos($result, "div class='rich_media_content'") + 76);
//        $str_17 = substr($str_16, 0, strrpos($str_16, "first_sceen__time") - 75); //公众号文章正文
        $gzhmsg = [
            'title' => mb_convert_encoding($str_8, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'),
            'cover_url' => $str_10,
            'desc' => mb_convert_encoding($str_13, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'),
            'author' => mb_convert_encoding($str_15, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'),
            'content' => $content,
        ];
        curl_close($ch);
        return $gzhmsg;
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
        ];
    }
}
