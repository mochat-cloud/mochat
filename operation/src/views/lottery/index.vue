<template>
    <div class="page">
        <div class="title-box" v-if="JSON.stringify(corp) == '{}'">
            <div class="title-content">
                <div class="logo">
                    <img :src="corp.logo" height="40" width="40" alt="" v-if="corp.logo!=''"/>
                </div>
                <div class="company-name">
                    <div class="title">{{ corp.name }}</div>
                    <span class="synopsis">{{ corp.description }}</span>
                </div>
            </div>
        </div>
        <div class="explain">
            <span @click="$refs.explain.show(clientData.lottery)">活动说明</span>
        </div>
        <div class="lottery-draw">
            <div class="lottery-box">
                <div class="top">
                    <div class="logo"><img :src="weChatUserNews.headimgurl" height="32" width="32"/></div>
                    {{ weChatUserNews.nickname }}
                </div>
                <div class="bottom">
                    <div class="box">
                        <LuckyWheel
                                style="width: 260px; height: 260px"
                                ref="LuckyWheel"
                                :prizes="luckDraw.prizes"
                                :defaultConfig="luckDraw.defaultConfig"
                                :blocks="luckDraw.blocks"
                                :buttons="luckDraw.buttons"
                                @start="startCallBack"
                        />
                    </div>
                </div>
                <div class="prize_name" v-if=" clientData.message!==undefined ">
                    <img :src="clientData.message.avatar" alt="">
                    恭喜<span>{{ clientData.message.nickname }}</span>获得{{ clientData.message.prize_name }}
                </div>
            </div>
        </div>
        <div class="record">
            <div class="lottery-box">
                <div class="top"><p>中奖记录</p><span>记得及时兑奖哦~</span></div>
                <div class="bottom">
                    <div class="box">
                        <div class="draw_list" v-for="(item,index) in clientData.win_list">
                            <div>{{ item.createdAt }}</div>
                            <div class="exchange">
                                <div class="exchange-box">
                                    <div class="exchange-name">
                                        <img :src="item.receiveQr" alt="" style="width: 35px;height: 35px;">
                                        <span>{{ item.prizeName }}</span>
                                    </div>
                                    <div class="cash-prize">
                                        <a-button type="danger" @click="receiveReward(item)"
                                                  v-if="item.receiveStatus==0">兑奖
                                        </a-button>
                                        <a-button class="converted" @click="receiveReward(item)" v-else>已兑换</a-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>注：每次抽奖需要消耗<span></span>积分</div>
            </div>
            <explain ref="explain"/>
            <prize ref="prize"/>
        </div>
    </div>
</template>

<script>
    import {contactDataApi, openUserInfoApi, contactLotteryApi, receiveApi} from "@/api/lottery";
    import explain from "@/views/lottery/explain";
    import prize from "@/views/lottery/prize";

    export default {
        components: {
            explain,
            prize
        },
        data() {
            return {
                //  抽奖
                luckDraw: {
                    //轮盘数据
                    prizes: [],
                    defaultConfig: {//默认样式
                        gutter: '5px',//扇形之间的缝隙（默认等于 0无间隙）
                        offsetDegree: 45
                    },
                    blocks: [//背景样式修改
                        {
                            padding: '17px',
                            imgs: [
                                {
                                    src: require("../../static/images/001.png"), // 转盘底图
                                    width: "100%",
                                    rotate: true
                                }
                            ]
                        },
                        {padding: '20px', background: '#F9D201'}
                    ],
                    buttons: [
                        {radius: '28px', background: '#FF5636', pointer: true},
                        {radius: '35px', background: '#FEC739'},
                        {
                            radius: '30px', background: '#FF5636',
                            fonts: [
                                {
                                    text: '开始\n抽奖',
                                    fontSize: '15px',
                                    top: -18,
                                    fontColor: "#fff",
                                }
                            ]
                        }
                    ]
                },
                //  用户微信信息
                weChatUserNews: {},
                //  客户数据
                clientData: {},
                corp: {},
                //  设置的奖品
                prizeSetNews: []
            }
        },
        created() {
            //  获取参数
            this.id = this.$route.query.id;
            this.source = this.$route.query.source;
            this.getOpenUserInfo();
        },
        methods: {
            getOpenUserInfo() {
                let that = this;
                openUserInfoApi({
                    id: that.id
                }).then((res) => {
                    if (res.data.openid === undefined) {
                        let redirectUrl = '/auth/lottery?id='+that.id+'&target=' + encodeURIComponent(that.url);
                        that.$redirectAuth(redirectUrl);
                    }
                    this.weChatUserNews = res.data;
                    this.getClientData();
                });
            },
            //  领取奖励
            receiveReward(item) {
                if (item.receiveStatus == 0) {
                    receiveApi({
                        id: item.id
                    }).then((res) => {
                        this.$message.success('奖励领取成功')
                        this.getClientData()
                    })
                }
                this.$refs.prize.show(item)
            },
            //  获取客户数据
            getClientData() {
                let params = {
                    id: this.id,
                    union_id: this.weChatUserNews.unionid,
                    nickname: this.weChatUserNews.nickname,
                    avatar: this.weChatUserNews.headimgurl,
                    city: this.weChatUserNews.city,
                    source: this.source
                }
                contactDataApi(params).then((res) => {
                    document.title = res.data.lottery.name
                    this.clientData = res.data
                    this.corp = res.data.corp
                    //  处理奖品设置数据
                    this.prizeSetNews = this.clientData.prize.prizeSet
                    this.handSetprize(this.prizeSetNews)
                })
            },
            //处理奖品设置数据
            handSetprize(data) {
                this.luckDraw.prizes = []
                data.forEach((item, index) => {
                    let prizeNews = {
                        name: "",
                        background: "#fff",
                        fonts: [
                            {
                                text: "",
                                fontSize: '13px',
                                top: -17
                            }
                        ],
                        imgs: [{
                            src: '', // 转盘底图
                            width: "30px",
                            top: 10,
                            rotate: true
                        }]
                    }
                    prizeNews.name = item.name
                    prizeNews.fonts[0].text = item.name
                    prizeNews.imgs[0].src = item.image
                    this.luckDraw.prizes.push(prizeNews)
                })
            },
            //  开始抽奖
            startCallBack() {
                contactLotteryApi({
                    id: this.id,
                    union_id: this.weChatUserNews.unionid,
                    nickname: this.weChatUserNews.nickname
                }).then((res) => {
                    //在哪里停止
                    this.prizeSetNews.forEach((item, index) => {
                        if (item.name == res.data.prize_name) {
                            this.$refs.LuckyWheel.play()
                            setTimeout(() => {
                                // 索引值
                                this.$refs.LuckyWheel.stop(index)
                            }, 3000)
                        }
                    })
                    //弹窗提醒
                    setTimeout(() => {
                        let prizeData = {
                            receiveName: res.data.prize_name,
                            receiveQr: res.data.receive_qr,
                            receiveCode: res.data.receive_code,
                            nickname: this.weChatUserNews.nickname,
                            headimgurl: this.weChatUserNews.headimgurl
                        }
                        this.$refs.prize.show(prizeData)
                        this.getClientData()
                    }, 6000)
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
        background-image: url("../../static/images/bg.png");
        background-size: cover;
        position: relative;
        overflow-y: auto;
        padding-bottom: 17px;

        .title-box {
            background: linear-gradient(#f7c080, #f8bf94);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 10vh;

            .logo {
                margin: 0 14px;
            }

            .company-name {

                .title {
                    font-size: 16px;
                    color: #ca4a4a;
                    font-weight: bold;
                }

                .synopsis {
                    font-weight: bold;
                    font-size: 14px;
                    color: rgba(#ca3d46, 0.6);
                }
            }

        }

        .title-content {
            display: flex;
            width: 340px;
            padding: 7px 0;
            background-color: #fab34b;
            border: 2px solid #f59400;
            border-radius: 5px;
        }

        .explain {
            position: absolute;
            padding: 2px 10px;
            right: 0;
            top: 81px;
            color: #b73551;
            background-color: #ffd4b8;
        }

        .prize_name {
            width: 80vw;
            height: 27px;
            border-radius: 27px;
            background: #DD6D17;
            line-height: 27px;
            color: #fff;
            padding-left: 12px;

            img {
                width: 20px;
                height: 20px;
                border-radius: 50%;
            }

            span {
                font-weight: bold;
                margin-left: 2px;
                margin-right: 2px;
            }
        }

        .lottery-draw {
            display: flex;
            justify-content: center;

            .lottery-box {
                width: 88vw;
                height: 400px;
                background: linear-gradient(#fff4a4, #faae7c);
                border-radius: 18px;
                margin-top: 46px;
                display: flex;
                justify-content: center;
                flex-wrap: wrap;

                .top {
                    background-color: #f8bf65;
                    width: 78vw;
                    height: 45px;
                    margin-top: 12px;
                    border-radius: 4px;
                    display: flex;
                    box-shadow: 0 0 5px #D19275 inset;
                    align-items: center;
                    font-weight: bold;
                    font-size: 18px;
                    color: maroon;

                    .logo {
                        margin: 0 12px;
                    }
                }

                .bottom {
                    width: 78vw;
                    height: 300px;
                    display: block;
                    position: relative;
                    z-index: 10;
                    background: linear-gradient(to top, #ffd38f, #f48744);
                    border-radius: 18px;

                    .box {
                        width: 75vw;
                        height: 275px;
                        background-color: #fff;
                        border-radius: 18px;
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        padding: 7px 16px 0 25px;
                    }
                }
            }


        }

        .record {
            display: flex;
            justify-content: center;

            .lottery-box {
                width: 88vw;
                height: 430px;
                background: linear-gradient(#fff4a4, #faae7c);
                border-radius: 18px;
                margin-top: 20px;
                display: flex;
                justify-content: center;
                flex-wrap: wrap;

                .top {
                    text-align: center;
                    width: 78vw;
                    height: 45px;
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

                .bottom {
                    width: 78vw;
                    height: 320px;
                    display: block;
                    position: relative;
                    z-index: 10;
                    background: linear-gradient(to top, #f48744, #ffd38f);
                    border-radius: 18px;

                    .box {
                        width: 75vw;
                        height: 280px;
                        background-color: #fff;
                        border-radius: 18px;
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        padding: 12px 16px 10px 16px;
                        overflow-y: scroll;
                    }
                }
            }
        }
    }

    .draw_list {
        margin-top: 10px;
    }

    .draw_list:first-child {
        margin-top: 0;
    }

    .converted {
        width: 66px;
        text-align: center;
        background: #F5F5F5;
        color: #B8B8B8;
        border: 1px solid #D9D9D9;
    }

    .exchange-box {
        display: flex;
        padding: 10px;
        background-color: #fff2a4;
        border-radius: 18px;
        margin-top: 3px;
        align-items: center;

        .exchange-name {
            flex: 1;

            span {
                margin-left: 6px;
            }
        }
    }
</style>
