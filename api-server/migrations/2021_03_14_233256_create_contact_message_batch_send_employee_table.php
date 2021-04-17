<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateContactMessageBatchSendEmployeeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_message_batch_send_employee', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('batch_id')->default(0)->comment('客户消息群发id （mc_contact_message_batch_send.id)');
            $table->unsignedInteger('employee_id')->default(0)->comment('员工id （mc_work_employee.id)');
            $table->string('wx_user_id', 255)->default('')->comment('微信userId （mc_work_employee.wx_user_id)');
            $table->unsignedInteger('send_contact_total')->default(0)->comment('发送客户数量');
            $table->json('content')->comment('群发消息内容');
            $table->string('err_code', 10)->default(0)->comment('返回码');
            $table->string('err_msg', 255)->default('')->comment('对返回码的文本描述内容');
            $table->string('msg_id', 50)->default('')->comment('企业群发消息的id，可用于获取群发消息发送结果');
            $table->timestamp('send_time')->nullable()->comment('发送时间');
            $table->timestamp('last_sync_time')->nullable()->comment('最后一次同步结果时间');
            $table->tinyInteger('status')->default(0)->comment('状态（0-未发送，1-已发送, 2-发送失败）');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `mc_contact_message_batch_send_employee` comment '客户消息群发成员表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_message_batch_send_employee');
    }
}
