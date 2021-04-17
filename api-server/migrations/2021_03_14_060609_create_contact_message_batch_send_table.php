<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateContactMessageBatchSendTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_message_batch_send', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('corp_id')->default(0)->comment('企业表ID （mc_corp.id）');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID【mc_user.id】');
            $table->string('user_name', 255)->default('')->comment('用户名称【mc_user.name】');
            $table->json('filter_params')->nullable()->comment('筛选客户参数');
            $table->json('filter_params_detail')->nullable()->comment('筛选客户参数显示详情');
            $table->json('content')->comment('群发消息内容');
            $table->tinyInteger('send_way')->default(1)->comment('发送方式（1-立即发送，2-定时发送）');
            $table->timestamp('definite_time')->nullable()->comment('定时发送时间');
            $table->timestamp('send_time')->nullable()->comment('发送时间');
            $table->unsignedInteger('send_employee_total')->default(0)->comment('发送成员数量');
            $table->unsignedInteger('send_contact_total')->default(0)->comment('发送客户数量');
            $table->unsignedInteger('send_total')->default(0)->comment('已发送数量');
            $table->unsignedInteger('not_send_total')->default(0)->comment('未发送数量');
            $table->unsignedInteger('received_total')->default(0)->comment('已送达数量');
            $table->unsignedInteger('not_received_total')->default(0)->comment('未送达数量');
            $table->unsignedInteger('receive_limit_total')->default(0)->comment('客户接收已达上限');
            $table->unsignedInteger('not_friend_total')->default(0)->comment('因不是好友发送失败');
            $table->tinyInteger('send_status')->default(0)->comment('状态（0-未发送，1-已发送）');
            $table->timestamps();
            $table->softDeletes();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `mc_contact_message_batch_send` comment '客户消息群发表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_message_batch_send');
    }
}
