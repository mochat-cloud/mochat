<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomFission\Action\Operation;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContactContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionPosterContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionRoomContract;
use Psr\Container\ContainerInterface;

/**
 * 群裂变-H5-查看助力进度
 * Class Receive.
 * @Controller
 */
class Receive extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

    /**
     * @Inject
     * @var RoomFissionPosterContract
     */
    protected $roomFissionPosterService;

    /**
     * @Inject
     * @var RoomFissionRoomContract
     */
    protected $roomFissionRoomService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var RoomFissionContactContract
     */
    protected $roomFissionContactService;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

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
     * @RequestMapping(path="/operation/roomFission/receive", methods="get")
     * @throws \JsonException
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);

        ## 群裂变信息-客服
        $fission = $this->roomFissionService->getRoomFissionById((int) $params['fission_id'], ['receive_employees']);
        $employeeArr = json_decode($fission['receiveEmployees'], true, 512, JSON_THROW_ON_ERROR);
        $employeeId = array_rand($employeeArr, 1);
        $employee = $this->workEmployeeService->getWorkEmployeeById($employeeArr[$employeeId], ['qr_code']);
        ## 客户信息
        $contactRecord = $this->roomFissionContactService->getRoomFissionContactByUnionIdFissionID($params['union_id'], (int) $params['fission_id'], ['id', 'status', 'receive_status']);
        if ($contactRecord['status'] === 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未完成任务');
        }
        if ($contactRecord['receiveStatus'] === 1) {
            return ['qrCode' => file_full_url($employee['qrCode'])];
        }
        $this->roomFissionContactService->updateRoomFissionContactById($contactRecord['id'], ['receive_status' => 1]);
        return ['qrCode' => file_full_url($employee['qrCode'])];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'fission_id' => 'required',
            'union_id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'fission_id.required' => 'fission_id 必传',
            'union_id.required' => 'union_id 必传',
        ];
    }
}
