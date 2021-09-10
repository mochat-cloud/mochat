<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\AutoTag\Task;

use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\AutoTag\Contract\AutoTagContract;
use MoChat\Plugin\AutoTag\Contract\AutoTagRecordContract;
use MoChat\Plugin\AutoTag\QueueService\KeyWordTag as KeyWordTagQueue;

/**
 * @Crontab(name="keyWordTag", rule="*\/5 * * * *", callback="execute", singleton=true, memo="企业微信-关键词打标签")
 */
class KeyWordTag
{
    /**
     * @Inject
     * @var AutoTagContract
     */
    protected $autoTagService;

    /**
     * @Inject
     * @var AutoTagRecordContract
     */
    protected $autoTagRecordService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var KeyWordTagQueue
     */
    private $keyWordTagQueue;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function execute(): void
    {
        if (date('H:i') < '23:00') {
            return;
        }

        $this->logger->info('关键词打标签开始');
        $corp = $this->corpService->getCorps(['id']);
        foreach ($corp as $item) {
            ## 查询企业全部敏感词【状态:开启】
            $autoTag = $this->autoTagService->getAutoTagByCorpIdStatus([$item['id']], 1, 1, ['id', 'corp_id', 'employees', 'fuzzy_match_keyword', 'exact_match_keyword', 'tag_rule', 'mark_tag_count']);
            $contact_list = $this->workContactService->getWorkContactsByCorpId((int) $item['id'], ['id', 'wx_external_userid']);

            $start = 0;
            for ($i = 0; $i < 100; ++$i) {
                ## 异步处理
                try {
                    empty(array_slice($contact_list, $start, 1000)) || $this->keyWordTagQueue->handle(array_slice($contact_list, $start, 1000), $autoTag, (int) $item['id']);
                } catch (InvalidConfigException|GuzzleException $e) {
                    $this->logger->error('关键词打标签异步调用失败' . $e->getMessage());
                }
                $start += 1000;
                if ($start >= count($contact_list)) {
                    break;
                }
            }
        }
        $this->logger->info('关键词打标签完成');
    }
}
