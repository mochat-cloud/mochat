<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateRoomMessageBatchSendTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room_message_batch_send', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('corp_id')->default(0)->comment('企业表ID （mc_corp.id）');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID【mc_user.id】');
            $table->string('user_name',255)->default('')->comment('用户名称【mc_user.name】');
            $table->string('batch_title',100)->default('')->comment('群发名称');
            $table->json('content')->comment('群发消息内容');
            $table->tinyInteger('send_way')->default(1)->comment('发送方式（1-立即发送，2-定时发送）');
            $table->timestamp('definite_time')->nullable()->comment('定时发送时间');
            $table->timestamp('send_time')->nullable()->comment('发送时间');
            $table->unsignedInteger('send_room_total')->default(0)->comment('发送成员数量');
            $table->unsignedInteger('send_contact_total')->default(0)->comment('发送客户数量');
            $table->unsignedInteger('send_total')->default(0)->comment('已发送数量');
            $table->unsignedInteger('not_send_total')->default(0)->comment('未发送数量');
            $table->unsignedInteger('received_total')->default(0)->comment('已送达数量');
            $table->unsignedInteger('not_received_total')->default(0)->comment('未送达数量');
            $table->tinyInteger('send_status')->default(0)->comment('状态（0-未发送，1-已发送）');
            $table->timestamps();
            $table->softDeletes();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `mc_room_message_batch_send` comment '客户群消息群发表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_message_batch_send');
    }
}
