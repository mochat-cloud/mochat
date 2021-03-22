<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateRoomMessageBatchSendResultTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room_message_batch_send_result', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('batch_id')->default(0)->comment('客户群消息群发id （mc_contact_message_batch_send.id)');
            $table->unsignedInteger('employee_id')->default(0)->comment('员工id （mc_work_employee.id)');
            $table->unsignedInteger('room_id')->default(0)->comment('客户群表id（work_room.id）');
            $table->string('chat_id',50)->default('')->comment('外部客户群id，群发消息到客户不吐出该字段');
            $table->string('user_id',50)->default('')->comment('企业服务人员的userid');
            $table->tinyInteger('status')->default(0)->comment('发送状态 0-未发送 1-已发送 2-因客户不是好友导致发送失败 3-因客户已经收到其他群发消息导致发送失败');
            $table->unsignedInteger('send_time')->default(0)->comment('发送时间，发送状态为1时返回');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `mc_room_message_batch_send_result` comment '客户群消息群发结果表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_message_batch_send_result');
    }
}
