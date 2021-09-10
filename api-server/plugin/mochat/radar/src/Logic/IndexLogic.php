<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Radar\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\User\Contract\UserContract;
use MoChat\Plugin\Radar\Contract\RadarChannelLinkContract;
use MoChat\Plugin\Radar\Contract\RadarContract;

/**
 * 抽奖活动-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * @Inject
     * @var RadarContract
     */
    protected $radarService;

    /**
     * @Inject
     * @var RadarChannelLinkContract
     */
    protected $radarChannelLinkService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @throws \JsonException
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理请求参数
        $params = $this->handleParams($user, $params);

        ## 查询数据
        return $this->getRadarList($params);
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        $where['type'] = $params['type'];
        if (! empty($params['title'])) {
            $where[] = ['title', 'LIKE', '%' . $params['title'] . '%'];
        }
        $where['corp_id'] = $user['corpIds'][0];
        if ($user['isSuperAdmin'] === 0) {
            $where['create_user_id'] = $user['id'];
        }
        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取互动雷达列表.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getRadarList(array $params): array
    {
        $columns = ['id', 'type', 'title', 'link', 'link_title', 'link_description', 'link_cover', 'pdf_name', 'pdf', 'article_type', 'article', 'contact_tags', 'corp_id', 'create_user_id', 'created_at'];
        $radarList = $this->radarService->getRadarList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($radarList['data']) ? $data : $this->handleData($radarList);
    }

    /**
     * 数据处理.
     * @param array $radarList 互动雷达列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $radarList): array
    {
        $list = [];
        foreach ($radarList['data'] as $key => $val) {
            //处理创建者信息
            $username = $this->userService->getUserById($val['createUserId']);
            $type = '链接';
            $article = $val['article'];
            if ($val['type'] === 2) {
                $type = 'PDF';
            }
            if ($val['type'] === 3) {
                $type = '文章';
                $article = json_decode($val['article'], true, 512, JSON_THROW_ON_ERROR);
                if ($val['articleType'] === 2) {
                    $article['cover_url'] = file_full_url($article['cover_url']);
                }
            }
            $list[$key] = [
                'id' => $val['id'],
                'title' => $val['title'],
                'contact_tags' => empty($val['contactTags']) ? '' : array_column(json_decode($val['contactTags'], true, 512, JSON_THROW_ON_ERROR), 'tagname'),
                'nickname' => isset($username['name']) ? $username['name'] : '',
                'link' => $val['link'],
                'link_title' => $val['linkTitle'],
                'link_description' => $val['linkDescription'],
                'link_cover' => empty($val['linkCover']) ? '' : file_full_url($val['linkCover']),
                'pdf_name' => $val['pdfName'],
                'pdf' => $val['pdf'],
                'article_type' => $val['articleType'],
                'article' => $article,
                'click_num' => $this->radarChannelLinkService->countRadarChannelLinkByCorpIdRadarId($val['corpId'], $val['id'], 'click_person_num'),
                'created_at' => $val['createdAt'],
                'type' => $type,
            ];
        }
        $data['page']['total'] = $radarList['total'];
        $data['page']['totalPage'] = $radarList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
