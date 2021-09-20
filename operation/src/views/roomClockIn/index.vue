<template>
    <div class="page">
        <div class="title-box" v-if="clientclockIn.corp_card_status==1">
            <div class="title-content">
                <div class="logo">
                    <img :src="clientclockIn.corp_info.logo" height="40" width="40" alt=""/>
                </div>
                <div class="company-name">
                    <div class="title">{{ clientclockIn.corp_info.name }}</div>
                </div>
            </div>
        </div>
        <div class="bg">
            <span @click="$refs.roomClockInExplain.show(clientclockIn.description)">活动说明</span>
        </div>
        <div class="days-box-top">
            <div class="customer-profile">
                <div class="customer-box">
                    <img :src="weChatUserNews.headimgurl" alt="" class="user_head">
                </div>
                <div style="font-size: 16px">{{ weChatUserNews.nickname }}</div>
            </div>
            <div class="continuity">
                <a-icon type="calendar" theme="twoTone" two-tone-color="#fd823f" style="margin-right: 6px"/>
                已<span v-if="clientclockIn.type==1">连续</span><span v-else>累计</span>打卡
                <span class="days">{{ clientclockIn.day_count }}</span>天
            </div>
            <div class="btn-box">
                <div class="btn" @click="getClock" v-if="clientclockIn.clock_in_status==0">参与打卡活动</div>
                <div class="color" v-else>今日已打卡</div>
            </div>
        </div>
        <div class="days-box-bottom">
            <div class="customer-profile">
                <div class="title-small">
                    <span v-if="clientclockIn.type==1">连续</span><span v-else>累计</span>打卡任务
                </div>
                <div class="task">
                    <div class="task-box" v-for="(item,index) in clientclockIn.tasks" :key="index"
                         @click="lookDetails(item,index)">
                        <div :class="[item.task_status==0?' clockIn_state':'']">
                            <p><span v-if="clientclockIn.type==1">连续</span><span v-else>累计</span>打卡</p>
                            <p><span class="day_span">{{ item.count }}</span>天</p>
                            <p class="prize_span">{{ item.prize }}</p>
                        </div>
                    </div>
                </div>
                <div>
                </div>
            </div>
        </div>
        <!--    日历-->
        <div class="details">
            <div class="title">签到详情</div>
            <div class="clear-box">
                <div :style="{ width: '300px', borderRadius: '4px', marginTop:'10px' }">
                    <a-calendar :fullscreen="false">
                        <template slot="dateCellRender" slot-scope="value">
                            <div v-if="getDateCellRender(value)" class="events"></div>
                        </template>
                    </a-calendar>
                </div>
            </div>
        </div>
        <!--    表格-->
        <div class="ranking">
            <div class="title">排行榜
                <span style="font-size: 14px;color: #fab34b;margin-left: 10px">已有{{ ranklist.total_user }}人参与</span>
            </div>
            <div class="clear-box">
                <span class="tips" v-if="clientclockIn.clock_in_status==0">你未参与打卡，目前无排名</span>
                <span class="tips" v-else>
          你今日已打卡，目前排第 <span>{{ ranklist.contact_ranking }}</span> 名
        </span>
            </div>
            <div class="table">
                <a-table :columns="table.col" :data-source="table.data" :pagination='false'>
                    <div slot="nickname" slot-scope="text, record">
                        <img :src="record.avatar" alt="" class="user_img">
                        <span>{{ text }}</span>
                    </div>
                </a-table>
            </div>
        </div>
        <roomClockInExplain ref="roomClockInExplain"/>
        <success ref="success"/>
    </div>
</template>

<script>
import 'moment/locale/zh-cn';
import roomClockInExplain from "@/views/roomClockIn/explain";
import success from "@/views/roomClockIn/success";
import {
    contactDataApi,
    clockInRankingApi,
    contactClockInApi,
    receiveApi,
    openUserInfoApi
} from "@/api/roomClockIn";

export default {
    components: {
        roomClockInExplain,
        success
    },
    data() {
        return {
            table: {
                col: [
                    {
                        dataIndex: 'ranking',
                        title: '名次'
                    },
                    {
                        dataIndex: 'nickname',
                        title: '昵称',
                        scopedSlots: {customRender: 'nickname'}
                    },
                    {
                        dataIndex: 'day_count',
                        title: '打卡天数'
                    }
                ],
                data: []
            },
            clockShow: false,
            daysShow: true,
            //用户微信信息
            weChatUserNews: {},
            //  客户打卡信息
            clientclockIn: {},
            //排行榜
            ranklist: {},
            //  未签到的天数
            noSignIn: []
        }
    },
    created() {
        this.id = this.$route.query.id;
        this.getOpenUserInfo();
    },
    methods: {
        getOpenUserInfo() {
            let that = this;
            openUserInfoApi({
                id: that.id
            }).then((res) => {
                if (res.data.openid === undefined) {
                    let redirectUrl = '/auth/roomClockIn?id=' + that.id + '&target=' + encodeURIComponent(that.url);
                    that.$redirectAuth(redirectUrl);
                }

                this.weChatUserNews = res.data;
                //  获取客户数据
                this.getClinentData()
                //  获取排行榜数据
                this.getRankList()
            });
        },
        getDateCellRender(value) {
            let month = value._d.getMonth()
            let selectMonth = this.clientclockIn.day_detail[month]
            if (selectMonth.indexOf(value.format('YYYY-MM-DD')) == -1) {
                return false
            } else {
                return true
            }
        },
        //  打卡签到
        getClock() {
            let params = {
                id: this.id,
                union_id: this.weChatUserNews.unionid
            }
            contactClockInApi(params).then((res) => {
                //  刷新排行榜
                this.$refs.success.getNews(0, res.data, this.clientclockIn.employee_qrcode)
                this.getRankList()
                this.getClinentData()
            })
        },
        //  查看任务完成详情
        lookDetails(item, index) {
            if (item.task_status == 1 && item.receive_status == 0) {
                receiveApi({
                    id: this.id,
                    union_id: this.weChatUserNews.unionid,
                    level: index + 1
                }).then((res) => {
                    this.$message.success('奖励领取成功');
                })
            }
            if (item.task_status == 1) {
                let puchData = {
                    day_count: item.count
                }
                this.$refs.success.getNews(1, puchData, this.clientclockIn.employee_qrcode)
            }
        },
        //  获取客户数据
        getClinentData() {
            let params = {
                id: this.id,
                union_id: this.weChatUserNews.unionid,
                nickname: this.weChatUserNews.nickname,
                avatar: this.weChatUserNews.headimgurl,
                city: this.weChatUserNews.city
            }
            contactDataApi(params).then((res) => {
                document.title = "群打卡"
                this.clientclockIn = res.data
                this.currentMonth()
                //  处理筛选未签到日期
            })
        },
        //  获取排行榜数据
        getRankList() {
            let params = {
                id: this.id,
                union_id: this.weChatUserNews.unionid
            }
            clockInRankingApi(params).then((res) => {
                this.ranklist = res.data
                this.table.data = this.ranklist.contact_list
            })
        }
    }
}
</script>

<style scoped lang="scss">
    .page {
        width: 100vw;
        height: 100vh;
        background-color: #ff5636;
        display: flex;
        flex-direction: column;
        align-items: center;
        background-image: url("../../static/images/bg.png");
        background-size: cover;
        position: relative;
        overflow-y: auto;

        .title-box {
            width: 100%;
            display: flex;
            justify-content: center;

            .title-content {
                background-color: #ffd6b6;
                display: flex;
                align-items: center;
                border: 8px solid #fdbd6b;
                width: 86%;
                border-radius: 18px;
                min-height: 10vh;
                margin-top: 18px;

                .logo {
                    margin: 0 14px;
                }

                .company-name {

                    .title {
                        font-size: 16px;
                        color: #ca4a4a;
                        font-weight: bold;
                    }
                }
            }
        }

        .bg {
            span {
                padding: 3px 6px;
                position: absolute;
                right: 0;
                border-radius: 5px 0px 0px 5px;
                top: 140px;
                color: #fab34b;
                background-color: #fff;
            }
        }

        .days-box-top {
            width: 86%;
            height: 220px;
            border-radius: 10px 10px 0 0;
            margin-top: 27px;
            background-color: #fceee3;
            border-bottom: 1px dashed #ff5636;


            .btn-box {
                display: flex;
                justify-content: center;

                .btn {
                    width: 88%;
                    background-image: linear-gradient(to right, #fd823f, #fd632d);
                    display: flex;
                    justify-content: center;
                    border-radius: 24px;
                    color: #fff;
                    padding: 10px 0;
                    margin-bottom: 24px;
                    font-size: 16px;
                }
            }

            .customer-profile {
                display: flex;
                align-items: center;
                padding-top: 12px;
                font-weight: bold;

                .customer-box {
                    margin: 0 14px;

                    .user_head {
                        width: 28px;
                        height: 28px;
                        border-radius: 50%;
                    }
                }
            }
        }

        .user_img {
            width: 28px;
            height: 28px;
            border-radius: 50%;
        }

        .days-box-bottom {
            width: 86%;
            height: 210px;
            margin-bottom: 20px;
            background-color: #fceee3;
            border-radius: 0 0 10px 10px;
        }

        .details {
            width: 86%;
            height: 500px;
            background-color: #fceee3;
            border-radius: 10px;
            margin-bottom: 20px;

            .title {
                padding: 14px;
                font-weight: bold;
                font-size: 16px;
                border-bottom: 1px solid #CCCCCC;
            }

            .clear-box {
                display: flex;
                justify-content: center;
            }
        }

        .ranking {
            width: 86%;
            min-height: 400px;
            background-color: #fceee3;
            border-radius: 10px;
            margin-bottom: 20px;

            .title {
                padding: 14px;
                font-weight: bold;
                font-size: 16px;
                border-bottom: 1px solid #CCCCCC;
            }

            .clear-box {
                display: flex;
                justify-content: center;
            }

        }
    }

    .title-small {
        margin-left: 10px;
        font-size: 15px;
        margin-top: 10px;
        font-weight: bold;
    }

    .task {
        display: flex;
        align-items: center;
        padding: 10px;
    }

    .task-box {
        width: 40vw;
        border-radius: 5px;
        margin-left: 2px;
        margin-right: 2px;
        height: 110px;
        line-height: 24px;
        text-align: center;
        background-color: #ffffff;
    }

    .color {
        width: 88%;
        display: flex;
        justify-content: center;
        border-radius: 24px;
        color: #fff;
        padding: 10px 0;
        margin-bottom: 24px;
        font-size: 16px;
        background-color: #ffd6a1;
    }

    .continuity {
        display: flex;
        justify-content: center;
        margin-top: 12px;
        margin-bottom: 12px;
        align-items: center;
        font-size: 16px;

        .days {
            font-size: 28px;
            font-weight: bold;
            color: #ff5636;
            margin-right: 4px;
            margin-left: 4px;
        }
    }

    .clockIn_state {
        color: #9A9B9B;
    }

    .day_span {
        font-size: 26px;
        font-weight: bold;
        color: #ff5636;
    }

    .prize_span {
        color: #EA661C;
    }

    .clockIn_state .day_span {
        color: #9A9B9B;
    }

    .clockIn_state .prize_span {
        color: #9A9B9B;
    }

    .tips {
        padding: 10px 40px;
        background-color: #fcdac1;
        margin-top: 10px;
        margin-bottom: 12px;
        border-radius: 20px;
    }

    /deep/ .ant-fullcalendar-header {
        padding: 0px 16px 5px 0;
    }

    /deep/ .ant-select.ant-fullcalendar-year-select {
        display: none;
    }

    /deep/ .ant-fullcalendar-header .ant-radio-group {
        display: none;
    }

    /deep/ .ant-fullcalendar-selected-day .ant-fullcalendar-value, .ant-fullcalendar-month-panel-selected-cell .ant-fullcalendar-value {
        color: rgba(0, 0, 0, 0.65);
        background: transparent;
    }

    /deep/ .ant-fullcalendar {
        border-top: 0;
    }

    li {
        list-style: none;
    }

    .events {
        width: 26px;
        height: 26px;
        background: rgba(24, 144, 255, .4);
        margin-left: 7px;
        margin-top: -42px;
    }
</style>
