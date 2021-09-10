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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Utils\File;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\Radar\Contract\RadarContract;

/**
 * 互动雷达-增加.
 *
 * Class StoreLogic
 */
class UpdateLogic
{
    /**
     * @Inject
     * @var RadarContract
     */
    protected $radarService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * StoreLogic constructor.
     */
    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @throws \JsonException
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理参数
        $data = $this->handleParam($user, $params);
        ## 创建活动
        $id = $this->updateRadar((int) $params['id'], $data);

        return [$id];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @throws \JsonException|\League\Flysystem\FileExistsException
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        if ($params['type'] === 3 && (int) $params['article_type'] === 2) {
            if (str_contains($params['article']['cover_url'], 'https')) {
                $params['article']['cover_url'] = strstr($params['article']['cover_url'], 'static');
            } else {
                $params['article']['cover_url'] = File::uploadBase64Image($params['article']['cover_url'], 'image/radar/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
            }
        }
        ## 基本信息
        return [
            'link' => isset($params['link']) ? $params['link'] : '',
            'link_title' => isset($params['link_title']) ? $params['link_title'] : '',
            'link_description' => isset($params['link_description']) ? $params['link_description'] : '',
            'link_cover' => isset($params['link_cover']) ? File::uploadBase64Image($params['link_cover'], 'image/radar/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg') : '',
            'pdf_name' => isset($params['pdf_name']) ? $params['pdf_name'] : '',
            'pdf' => isset($params['pdf']) ? $params['pdf'] : '',
            'article_type' => isset($params['article_type']) ? $params['article_type'] : 0,
            'article' => isset($params['article']) ? json_encode($params['article'], JSON_THROW_ON_ERROR) : '{}',
            'employee_card' => isset($params['employee_card']) ? $params['employee_card'] : 0,
            'action_notice' => isset($params['action_notice']) ? $params['action_notice'] : 0,
            'dynamic_notice' => isset($params['dynamic_notice']) ? $params['dynamic_notice'] : 0,
            'tag_status' => isset($params['tag_status']) ? $params['tag_status'] : 0,
            'contact_tags' => isset($params['contact_tags']) ? json_encode($params['contact_tags'], JSON_THROW_ON_ERROR) : '{}',
            'contact_grade' => isset($params['contact_grade']) ? json_encode($params['contact_grade'], JSON_THROW_ON_ERROR) : '{}',
        ];
    }

    /**
     * 修改活动.
     */
    private function updateRadar(int $id, array $params): int
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $id = $this->radarService->updateRadarById($id, $params);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '活动修改失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动修改失败'
        }
        return $id;
    }
}
