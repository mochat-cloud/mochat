<template>
    <div class="record" v-show="modalShow">
        <div class="bg" @click="modalShow = false"></div>
        <div class="lottery-box">
            <div class="top">
                <p>
                    抽奖说明
                </p>
            </div>
            <div class="content">
                <div class="box">
                    {{ lottery.description }}
                </div>
            </div>
            <div class="time">
                <p>
                    抽奖活动时间
                </p>
                <div class="bottom">
                    <div class="box" v-if="lottery.timeType===1">
                        永久有效
                    </div>
                    <div class="box" v-else>
                        {{ lottery.startTime }} - {{ lottery.endTime }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            modalShow: false,
            lottery: {}
        }
    },
    methods: {
        show(lottery) {
            this.lottery = lottery
            this.modalShow = true
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
        height: 430px;
        background: linear-gradient(#fff4a4, #faae7c);
        border-radius: 18px;
        margin-top: 20px;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
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

        .content {
            width: 78vw;
            height: 270px;
            display: block;
            margin-top: -20px;
            position: relative;
            z-index: 10;
            background: linear-gradient(to top, #ffd38f, #f48744);
            border-radius: 18px;

            .box {
                width: 75vw;
                height: 257px;
                background-color: #fff;
                border-radius: 18px;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                padding: 16px;
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

        .time {
            text-align: center;
            margin-top: -16px;

            p {
                color: #e3453a;
                font-size: 18px;
                font-weight: bold;
            }
        }
    }
}
</style>
