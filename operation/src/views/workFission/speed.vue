<template>
    <div class="page">
        <div class="info">
            <div class="tips">
                <div class="tip" v-if="!isEnd">
                    已有<span class="num">{{dataList.invite_count}}</span>人完成助力
                </div>
                <div class="tip" v-if="!isEnd">
                    还差<span class="num">{{dataList.differ_count}}</span>人完成下一阶段任务
                </div>
                <div class="tip" v-if="isEnd">活动已结束</div>
            </div>

            <div class="total">
                <div class="icon"></div>
                当前助力人数：<span class="num">{{dataList.invite_count}}</span>人
            </div>

            <div class="gift">
                <div :class="{item: true}" @click="openGiftMask(item, index)" v-for="(item, index) of dataList.task"
                     :key="index">
                    <div :class="{top: true, 'top-received': item.receive_status || item.count > dataList.invite_count}">
                        <img src="../../static/images/gift.png" alt="" class="icon">
                        <div class="received" v-if="item.receive_status">已领取</div>
                    </div>
                    <div class="bottom">
                        <div class="lines">
                            <div :class="{line: true, light: item.count <= dataList.invite_count}"></div>
                            <div :class="{line: true, light: dataList.task[index + 1] ? dataList.task[index + 1].count <= dataList.invite_count : false}"
                                 v-show="index !== dataList.task.length - 1"></div>
                        </div>
                        <div :class="{round: true, light: item.count <= dataList.invite_count}"></div>
                        <div class="level">+{{item.count}}人</div>
                    </div>
                </div>
            </div>

            <div class="countdown" v-if="!isEnd">
                还剩
                <span class="time">{{countDown.day}}</span>天
                <span class="time">{{countDown.hour}}</span>时
                <span class="time">{{countDown.min}}</span>分
                <span class="time">{{countDown.sec}}</span>秒后结束
            </div>
        </div>
        <div class="list">
            <div class="title">
                <span class="text">我的助力好友</span>
            </div>
            <div class="content">
                <div class="row" v-for="(item, index) of friendList" :key="index">
                    <div class="left">
                        <img :src="item.avatar" class="avatar">
                    </div>
                    <div class="right">
                        <div class="user-info">
                            <div class="name">{{item.nickname}}</div>
                            <div class="time">{{item.createdAt}}</div>
                        </div>
                        <div class="tips">
                            <div class="tip" v-if="item.fail" @click="failMaskFlag = true">助力失败</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mask" v-if="giftMaskFlag" @click="giftMaskFlag = false">
            <div class="content" @click.stop>
                <div class="title">恭喜你完成任务</div>
                <img src="../../static/images/gift-image.png" alt="" class="gift-image">
                <div class="tip">点击下方按钮领取任务奖励</div>
                <div class="get-gift" @click="getGift">前往领取</div>
            </div>
        </div>

        <div class="code-mask" v-if="codeMaskFlag" @click="codeMaskFlag = false">
            <div class="content" @click.stop>
                <div class="title">领取奖品</div>
                <div class="tip">恭喜你完成任务，快添加微信领取奖品吧！</div>
                <img :src="codeUrl" alt="" class="code-image">
                <div class="bottom-tip">长按识别二维码添加微信</div>
            </div>
        </div>

        <div class="fail-mask" v-if="failMaskFlag" @click="failMaskFlag = false">
            <div class="content" @click.stop>
                <div class="title">助力失败</div>
                <img src="../../static/images/error.png" alt="" class="fail-image">
                <div class="tip">该客户已删除员工好友，助力失败</div>
                <div class="fail-btn" @click="failMaskFlag = false">我知道了</div>
            </div>
        </div>
    </div>
</template>

<script>
    import {taskDataApi, inviteFriendsApi, receiveApi, openUserInfoApi} from "../../api/workFission";

    export default {
        name: "speed",
        data() {
            return {
                isEnd: false,
                giftMaskFlag: false,
                giftInfo: {},
                codeMaskFlag: false,
                codeUrl: '',
                failMaskFlag: false,
                dataList: {},
                friendList: [],
                countDown: {
                    day: '-',
                    hour: '-',
                    min: '-',
                    sec: '-',
                },
                timer: 0
            }
        },
        created() {
            this.fission_id = this.$route.query.fission_id
            this.union_id = this.$route.query.union_id
            this.getOpenUserInfo()
        },
        methods: {
            getOpenUserInfo() {
                let that = this;
                openUserInfoApi({
                    id: that.fission_id
                }).then((res) => {
                    if (res.data.openid === undefined) {
                        let redirectUrl = '/auth/workFission?id=' + that.fission_id + '&target=' + encodeURIComponent(that.url);
                        that.$redirectAuth(redirectUrl);
                    }

                    this.wxUserData = res.data;
                    this.getDataList();
                });
            },
            async getDataList() {
                //获取任务信息
                await taskDataApi({
                    union_id: this.union_id,
                    fission_id: this.fission_id
                }).then(res => {
                    this.dataList = res.data;
                })
                //获取邀请的好友列表
                await inviteFriendsApi({
                    union_id: this.union_id,
                    fission_id: this.fission_id
                }).then(res => {
                    // console.log(res);
                    this.friendList = res.data;
                })
                this.setCountDown();
            },
            getGift() {
                receiveApi({
                    union_id: this.union_id,
                    fission_id: this.fission_id,
                    level: this.giftInfo.level
                }).then(res => {
                })

                if (this.giftInfo.gift_type === 1) {
                    window.open(this.giftInfo.gift_url);
                }
                if (this.giftInfo.gift_type === 0) {
                    this.giftMaskFlag = false;
                    this.codeUrl = this.giftInfo.gift_url;
                    this.codeMaskFlag = true;
                }

                this.getDataList();
            },
            openGiftMask(item, index) {
                // console.log(item);
                if (this.dataList.invite_count < item.count) return;
                this.giftMaskFlag = true;

                let clone = JSON.parse(JSON.stringify(item));
                clone.level = index + 1;
                this.giftInfo = clone;
            },
            setCountDown() {
                clearInterval(this.timer);

                //先检测一次
                let tempNow = Math.floor(new Date().getTime() / 1000);
                let tempGap = this.dataList.end_time - tempNow;
                // console.log('剩余时间', tempGap)
                if (tempGap <= 0) {
                    this.isEnd = true;
                    return;
                }
                this.setTime(tempGap);

                this.timer = setInterval(() => {
                    let now = Math.floor(new Date().getTime() / 1000);
                    let gap = this.dataList.end_time - now;
                    if (gap <= 0) {
                        this.isEnd = true;
                        clearInterval(this.Timer);
                        return;
                    }
                    this.setTime(gap);
                }, 1000);
            },
            setTime(time) {
                this.countDown.day = Math.floor(time / 60 / 60 / 24);
                this.countDown.hour = Math.floor(time / 60 / 60 % 24);
                this.countDown.min = Math.floor(time / 60 % 60);
                this.countDown.sec = Math.floor(time % 60);
            }
        }
    }
</script>

<style lang="scss" scoped>
    .page {
        width: 100vw;
        height: 100vh;
        background-color: #ff5636;
        padding: 16px;
        position: relative;
        background-image: url("../../static/images/bg.png");
        background-size: cover;
        display: flex;
        flex-direction: column;

        .info {
            background-color: #ffefdf;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;

            .tips {
                display: flex;
                align-items: center;
                flex-direction: column;
                margin-bottom: 10px;

                .tip {
                    color: saddlebrown;
                }

                .num {
                    padding: 0 6px;
                    color: #ff5636;
                    font-weight: bold;
                }
            }

            .total {
                display: flex;
                align-items: center;
                margin-bottom: 20px;

                .icon {
                    width: 4px;
                    height: 14px;
                    border-radius: 3px;
                    background-color: darkorange;
                    margin-right: 6px;
                }

                .num {
                    padding: 0 6px;
                    color: #ff5636;
                }
            }

            .gift {
                display: flex;
                overflow: auto;
                margin-bottom: 8px;

                .item {
                    $size: 86px;
                    min-width: $size;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    flex-grow: 1;

                    .top {
                        $size: 70px;
                        width: $size;
                        height: $size;
                        background-color: orange;
                        border-radius: 10px;
                        margin-bottom: 8px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        position: relative;
                        overflow: hidden;

                        .icon {
                            width: 50%;
                            height: 50%;
                        }

                        .received {
                            width: 100%;
                            text-align: center;
                            position: absolute;
                            background-color: #ffc271;
                            color: saddlebrown;
                            font-size: 12px;
                            bottom: 0;
                        }
                    }

                    .top-received {
                        background-color: #ffd6a1;

                        .icon {
                            opacity: .5;
                        }
                    }

                    .bottom {
                        width: 100%;
                        position: relative;

                        $lineHeight: 4px;

                        .lines {
                            display: flex;
                            align-items: center;

                            .line {
                                width: 50%;
                                height: $lineHeight;
                                background-color: #ffe1c4;
                            }

                            .light {
                                background-color: orange;
                            }
                        }

                        .round {
                            $size: 12px;
                            position: absolute;
                            top: 0 - $size / 2 + $lineHeight / 2;
                            left: calc(50% - (#{$size} / 2));
                            width: $size;
                            height: $size;
                            background-color: #ffd6a1;
                            border-radius: $size;
                        }

                        .light {
                            background-color: orange;
                        }
                    }

                    .level {
                        font-size: 12px;
                        margin-top: 2px;
                        text-align: center;
                        color: #ffaf45;
                    }
                }
            }

            .countdown {
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 13px;
                color: grey;

                .time {
                    $size: 24px;
                    width: $size;
                    height: $size;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    border-radius: 3px;
                    margin: 0 5px;
                    background-color: #ffe4bd;
                    color: darkorange;
                    font-size: 12px;
                    font-weight: bold;
                }
            }
        }

        .list {
            background-color: #ffefdf;
            border-radius: 10px;
            padding: 30px 30px 1px;
            position: relative;
            flex-grow: 1;
            display: flex;
            flex-direction: column;

            .title {
                $size: 100px;
                width: 100%;
                top: -4px;
                left: 0;
                position: absolute;
                display: flex;
                justify-content: center;

                .text {
                    padding: 3px 8px;
                    color: white;
                    $radius: 6px;
                    border-radius: 0 0 $radius $radius;
                    background-color: #ffaf45;
                }
            }

            .content {
                height: 0;
                flex-grow: 1;
                overflow-y: auto;

                .row {
                    padding: 14px 0;
                    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
                    display: flex;
                    align-items: center;

                    .left {
                        margin-right: 14px;

                        .avatar {
                            $size: 44px;
                            width: $size;
                            height: $size;
                            border-radius: 50%;
                            border: 2px solid #ffaf45;
                        }
                    }

                    .right {
                        flex-grow: 1;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;

                        .user-info {
                            .name {
                                color: saddlebrown;
                                font-size: 16px;
                                font-weight: bold;
                            }

                            .time {
                                color: #bd6e3a;
                                font-size: 12px;
                            }
                        }

                        .tips {
                            .tip {
                                font-size: 12px;
                                color: #bd6e3a;
                            }
                        }
                    }
                }
            }
        }

        .mask {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.64);
            display: flex;
            justify-content: center;
            align-items: center;

            .content {
                width: 84%;
                padding: 20px 0 30px;
                background-color: white;
                border-radius: 10px;
                display: flex;
                flex-direction: column;
                align-items: center;

                .title {
                    font-size: 16px;
                    color: black;
                }

                .gift-image {
                    width: 70%;
                }

                .tip {
                    margin-top: -15%;
                    margin-bottom: 10px;
                }

                .get-gift {
                    background-color: #ff5636;
                    width: 50%;
                    height: 32px;
                    display: flex;
                    color: white;
                    border-radius: 32px;
                    justify-content: center;
                    align-items: center;

                }
            }
        }

        .code-mask {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.64);
            display: flex;
            justify-content: center;
            align-items: center;

            .content {
                width: 84%;
                padding: 20px 0 30px;
                background-color: white;
                display: flex;
                border-radius: 6px;
                flex-direction: column;
                align-items: center;

                .title {
                    color: black;
                    margin-bottom: 10px;
                    font-size: 16px;
                }

                .tip {
                    color: saddlebrown;
                    font-size: 13px;
                }

                .code-image {
                    width: 70%;
                    margin-bottom: 20px;
                }

                .bottom-tip {
                    font-size: 15px;
                }
            }
        }

        .fail-mask {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.64);
            display: flex;
            justify-content: center;
            align-items: center;

            .content {
                width: 84%;
                padding: 20px 0 30px;
                background-color: white;
                border-radius: 10px;
                display: flex;
                flex-direction: column;
                align-items: center;

                .title {
                    font-size: 17px;
                    color: black;
                    margin-bottom: 20px;
                }

                .fail-image {
                    width: 34%;
                    margin-bottom: 12px;
                }

                .tip {
                    margin-bottom: 20px;
                }

                .fail-btn {
                    background-color: #ff5636;
                    width: 70%;
                    height: 40px;
                    font-size: 15px;
                    display: flex;
                    color: white;
                    border-radius: 32px;
                    justify-content: center;
                    align-items: center;
                }
            }
        }
    }
</style>