<template>
    <div class="record" v-if="successShow">
        <div class="bg" @click="successShow=false"></div>
        <div class="lottery-box">
            <div class="end" @click="hide">×</div>
            <div class="top">
              <p>恭喜你打卡成功</p>
            </div>
            <div class="content" style="text-align: center;">
              <div class="clock_time" v-if="type==0">完成打卡时间：<span>{{ taskData.day }}</span></div>
              <div class="clock_tip_a" v-if="type==1">你已经完成连续打卡{{ taskData.day_count }}天任务</div>
              <img :src="employee_qrcode" class="qr_code" alt=""/>
              <div class="clock_tips" v-if="type==0">真棒!你已经完成连续打卡{{ taskData.day_count }}天任务</div>
              <div class="clock_tips" v-if="type==0">继续打卡完成其他任务吧</div>
              <div class="clock_tips">记得添加客服领取奖励哦~</div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
          successShow: false,
          taskData:{},
          employee_qrcode:'',
          type:0
        }
    },
    methods: {
        getNews(type,data,employee_qrcode) {
          this.type=type
          this.successShow = true
          if(this.type==0){
            this.taskData=data
          }
          this.employee_qrcode=employee_qrcode
        },
        hide() {
            this.successShow = false
        }
    }
}
</script>

<style scoped lang="scss">
.record {
    width: 100vw;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    z-index: 100;

    .bg {
        width: 100vw;
        height: 100vh;
        background-color: #00000094;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 10;
    }
    .lottery-box {
        width: 88vw;
        height: 300px;
        background-color: #ffffff;
        padding-bottom: 10px;
        border-radius: 18px;
        margin-top: 20px;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        position: relative;
        z-index: 20;

        .top {
            text-align: center;
            width: 78vw;
            height: 30px;
            margin-top: 12px;

            p {
                color: #e3453a;
                font-size: 18px;
                font-weight: bold;
            }

            span {
                display: block;
                margin-top: -16px;
            }
        }

        .box {
            width: 78vw;
            height: 200px;

            .top-box {
                display: flex;
                justify-content: center;
                margin-top: 34px;

                .top-left {
                    width: 50%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 30px;
                    border-right: 1px dashed #ff5636;
                }

                .top-right {
                    width: 50%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 30px;
                }

                .numSpan {
                    font-size: 26px;
                    color: #ff5636;
                    margin: 0 4px;
                }
            }
        }

        .bottom {
            width: 78vw;
            height: 43px;
            display: block;
            margin-top: -10px;
            position: relative;
            z-index: 10;
            background: linear-gradient(to top, #f48744, #ffd38f);
            border-radius: 8px;

            .box {
                width: 75vw;
                height: 30px;
                background-color: #fff;
                border-radius: 8px;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                padding: 16px;
            }
        }
    }
}

.end {
    position: absolute;
    font-size: 24px;
    right: 12px;
}
.qr_code{
  width: 150px;
  height: 150px;
}
.clock_time{
  color: #100800;
  font-size: 15px;
}
.clock_time span{
  color: #EC7507;
}
.clock_tips{
  color: #433A31;
  font-size: 15px;
}
.clock_tip_a{
  color: #FAB34B;
  font-size: 17px;
  margin-top: -20px;
  margin-bottom: 10px;
}

</style>
