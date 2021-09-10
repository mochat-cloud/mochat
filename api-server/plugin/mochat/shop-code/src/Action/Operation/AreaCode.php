<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ShopCode\Action\Operation;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ShopCode\Contract\ShopCodeContract;
use MoChat\Plugin\ShopCode\Contract\ShopCodePageContract;
use MoChat\Plugin\ShopCode\Contract\ShopCodeRecordContract;
use Psr\Container\ContainerInterface;

/**
 * 门店活码-H5-获取门店活码
 * Class AreaCode.
 * @Controller
 */
class AreaCode extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ShopCodeContract
     */
    protected $shopCodeService;

    /**
     * @Inject
     * @var ShopCodePageContract
     */
    protected $shopCodePageService;

    /**
     * @Inject
     * @var ShopCodeRecordContract
     */
    protected $shopCodeRecordService;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @RequestMapping(path="/operation/shopCode/areaCode", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);
        ## 省
        $province = $this->shopCodeService->getShopCodeByCorpIdType((int) $params['corpId'], (int) $params['type'], '', ['province']);
        ## 城市
        foreach ($province as $k => $v) {
            $province[$k]['city'] = $this->shopCodeService->getShopCodeByCorpIdType((int) $params['corpId'], (int) $params['type'], $v['province'], ['city']);
        }
        ## 门店
        $info = [];
        ## 按省市搜索
        if (! empty($params['province']) && empty($params['name'])) {
            $info = $this->shopCodeService->getShopCodeByCorpIdTypeStatusProvince((int) $params['corpId'], (int) $params['type'], 1, $params['province'], $params['city'], ['name', 'employee_qrcode', 'qw_code', 'address', 'province', 'city', 'lat', 'lng']);
            if (empty($info)) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '门店不存在~~');
            }
        }
        ## 按店名搜索
        if (! empty($params['name']) && empty($params['province'])) {
            $info = $this->shopCodeService->getShopCodeByCorpIdTypeStatusName((int) $params['corpId'], (int) $params['type'], 1, $params['name'], ['name', 'employee_qrcode', 'qw_code', 'address', 'province', 'city', 'lat', 'lng']);
            if (empty($info)) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '门店不存在~~');
            }
        }

        ## 按省市-店名搜索
        if (! empty($params['province']) && ! empty($params['name'])) {
            $info = $this->shopCodeService->getShopCodeByCorpIdTypeStatusProvinceName((int) $params['corpId'], (int) $params['type'], 1, $params['province'], $params['city'], $params['name'], ['name', 'employee_qrcode', 'qw_code', 'address', 'province', 'city', 'lat', 'lng']);
        }

        ## 按经纬度搜索 添加点击记录
        if (! empty($params['lat']) && (int) $params['type'] !== 3) {
            $list = $this->shopCodeService->getShopCodeByCorpIdTypeStatus((int) $params['corpId'], (int) $params['type'], 1, ['id', 'name', 'employee_qrcode', 'qw_code', 'address', 'province', 'city', 'lat', 'lng']);
            ## 距离
            $data = [];
            foreach ($list as $k2 => $v2) {
                $data[$k2] = $this->getDistance($params['lat'], $params['lng'], $v2['lat'], $v2['lng']);
            }
            asort($data);
            $num = 0;
            foreach ($data as $key => $val) {
                if ($num === 0) {
                    $info = $list[$key];
                    break;
                }
                ++$num;
            }
            $record = [
                'type' => $params['type'],
                'corp_id' => (int) $params['corpId'],
                'shop_id' => $info['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->shopCodeRecordService->createShopCodeRecord($record);
        }
        if (! empty($params['lat']) && (int) $params['type'] === 3) {
            $list = $this->shopCodeService->getShopCodeByCorpIdTypeStatus((int) $params['corpId'], (int) $params['type'], 1, ['name', 'employee_qrcode', 'qw_code', 'address', 'province', 'city', 'lat', 'lng']);
            $info = empty($list) ? [] : $list[0];
        }
        ## 页面
        $page = $this->shopCodePageService->getShopCodePageByCorpIdType((int) $params['corpId'], (int) $params['type'], ['title', 'show_type', 'default', 'poster']);
        if (empty($page)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '页面未设置~~');
        }
        if ($page['showType'] === 1 && ! empty($page['default'])) {
            $default = json_decode($page['default'], true, 512, JSON_THROW_ON_ERROR);
            $default['logo'] = file_full_url($default['logo']);
            $page['default'] = $default;
        }

        if ((int) $params['type'] === 1 && ! empty($info)) {
            $code = json_decode($info['employeeQrcode'], true, 512, JSON_THROW_ON_ERROR);
            $localQrcode = file_full_url($code['local_qrcode']);
            $info['employeeQrcode'] = $localQrcode;
            unset($info['lat'], $info['lng']);
        }
        if ((int) $params['type'] > 1 && ! empty($info)) {
            $code = json_decode($info['qwCode'], true, 512, JSON_THROW_ON_ERROR);
            $info['employeeQrcode'] = $code['qrcodeUrl'];
            unset($info['lat'], $info['lng']);
        }

        return ['province' => $province, 'shop_info' => $info, 'page' => $page];
    }

    /**
     * 球面坐标取距离（单位：Km）.
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return float|int
     */
    public function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $radLat1 = $this->rad($lat1);
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) +
                cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * 6378.137;
        return round($s * 10000) / 10000;
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'corpId' => 'required',
            'type' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'corpId.required' => '企业id 必传',
            'type.required' => '类型 必传',
        ];
    }

    /**
     * 将用角度表示的角转换为近似相等的用弧度表示的角.
     * @param $d
     * @return float
     */
    private function rad($d)
    {
        return $d * pi() / 180.0;
    }
}
