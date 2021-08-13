<template>
    <div>
        <!--      用户页面-->
        <div class="page" v-show="type==2">
            <div class="top">
                <div class="code-image">
                    <div class="code-border">
                        <div class="show_poster">
                            <img :src="posterNews.coverPic" alt="" class="cover_pic">
                            <div class="show_user_news">
                                <div class="show_user_data">
                                    <img :src="wxUserData.headimgurl" alt="" class="user_img">
                                    <div class="user_name" :style="{color:posterNews.nicknameColor}">{{
                                        wxUserData.nickname }}
                                    </div>
                                </div>
                                <div class="qr-code" ref="qrCode"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bottom-box">
                <div
                        class="long-btn"
                        @click="$router.push('/fissionSpeed?fission_id='+fission_id+'&wxUserData='+JSON.stringify(wxUserData))">
                    查看助力进度
                </div>
            </div>
            <input type="text" ref="copyInput" style="position: fixed; opacity: 0">
        </div>
        <!--      群聊页面-->
        <div class="pageCode" v-if="type==1">
            <div class="qc_code">
                <div class="code-box">
                    <div class="top">
                        <div class="right">
                            <div class="shop_name">{{ roomNews.name }}</div>
                        </div>
                    </div>
                    <div class="bottom">
                        <div class="code">
                            <img :src="roomNews.roomQrcode" alt="" class="qr_room_img"/>
                        </div>
                    </div>
                    <div class="shop_guide">长按识别图中二维码加入群聊</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import QRCode from 'qrcodejs2'
    import {openUserInfoApi, posterApi} from '@/api/roomFission'

    export default {
        name: "index",
        data() {
            return {
                type: 0,
                dataList: {},
                userInfo: {},
                bgHeight: 'auto',
                base64Img: '',
                //    微信授权信息
                wxUserData: {},
                //  群聊信息
                roomNews: {},
                //  用户海报信息
                posterNews: {}
            }
        },

        created() {
            this.fission_id = this.$route.query.id;
            this.parentUnionId = this.$route.query.parent_union_id;
            this.wxUserId = this.$route.query.wx_user_id;
            this.getOpenUserInfo();
        },
        methods: {
            getOpenUserInfo() {
                let that = this;
                openUserInfoApi({
                    id: that.fission_id
                }).then((res) => {
                    if (res.data.openid === undefined) {
                        let redirectUrl = '/auth/roomFission?id=' + that.fission_id + '&target=' + encodeURIComponent(that.url);
                        that.$redirectAuth(redirectUrl);
                    }

                    this.wxUserData = res.data;
                    this.getPosterData();
                });
            },
            //  获取海报数据
            getPosterData() {
                let params = {
                    fission_id: this.fission_id,
                    parentUnionId: this.parentUnionId,
                    wxUserId: this.wxUserId,
                    union_id: this.wxUserData.unionid,
                    nickname: this.wxUserData.nickname,
                    avatar: this.wxUserData.headimgurl,
                }
                posterApi(params).then((res) => {
                    document.title = "群裂变"
                    if (res.data.type == 0) {
                        this.$message.info('活动已结束');
                        return false
                    }
                    if (res.data.type == 1) {
                        this.roomNews = res.data.room
                    }
                    if (res.data.type == 2) {
                        this.posterNews = res.data.poster
                        this.initQrcode()
                    }
                    this.type = res.data.type
                })
            },
            initQrcode() {
                this.$refs.qrCode.innerHTML = ''
                new QRCode(this.$refs.qrCode, {
                    text: this.posterNews.qrCodeUrl,
                    width: 85,
                    height: 85
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
        padding: 0 20px 80px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background-image: url("../../static/images/bg.png");
        background-size: cover;
        overflow-y: auto;

        .top {
            .reply {
                padding-top: 16px;

                .content {
                    padding: 16px 20px;
                    border-radius: 16px;
                    background-color: rgb(253, 253, 229);

                    .title {
                        display: flex;
                        justify-content: space-between;

                        .tip {
                            color: saddlebrown;
                            font-size: 15px;
                            position: relative;

                            &:before {
                                content: '';
                                position: absolute;
                                bottom: 0;
                                left: 0;
                                width: 100%;
                                height: 7px;
                                background-color: #b97e1229;
                            }
                        }

                        .copy {

                        }
                    }

                    .text {

                    }
                }


            }

            .tips {
                padding: 20px 0;

                .tip {
                    display: flex;
                    justify-content: center;
                    margin-bottom: 4px;

                    .text {
                        position: relative;
                        color: white;
                        z-index: 0;

                        &::before {
                            $width: 90%;
                            position: absolute;
                            content: '';
                            bottom: 0;
                            z-index: -1;

                            left: ((100% - $width) / 2);

                            height: 10px;
                            width: $width;
                            border-radius: 100px;
                            background-color: rgba(255, 255, 255, 0.15);
                        }
                    }

                    .long-text {
                        &::before {
                            $width: 110%;
                            left: ((100% - $width) / 2);
                            width: $width;
                        }
                    }
                }
            }

            .code-image {
                width: 100%;
                background-color: navajowhite;
                border-radius: 10px;
                box-shadow: inset 0 4px 6px 0 #0000001c;
                border: 2px solid white;
                padding: 10px;
                margin-top: 30px;

                .code-border {
                    width: 100%;
                    padding: 16px;
                    background-color: white;
                    border-radius: 12px;
                }
            }
        }

        .bottom-box {
            width: 100vw;
            position: fixed;
            bottom: 0;
            left: 0;
            padding: 10px 0 15px;
            box-shadow: 0 -2px 6px 0 rgba(0, 0, 0, 0.19);
            background-color: #ff5636;
            display: flex;
            justify-content: center;

            .long-btn {
                width: 90%;
                height: 42px;
                display: flex;
                justify-content: center;
                align-items: center;
                color: darkorange;
                font-size: 16px;
                border: 2px solid #ffd6a1;
                border-radius: 100px;
                background-color: #ffecdb;
            }
        }


    }

    .show_poster {
        width: 100%;
        height: 75vh;
        position: relative;
        //background: skyblue;
    }

    .cover_pic {
        width: 100%;
        height: 100%;
    }

    .show_user_news {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
    }

    .show_user_data {
        margin-left: 15px;
        margin-top: 15px;
        display: flex;
    }

    .qr-code {
        position: absolute;
        bottom: 10px;
        right: 10px;
    }

    .user_img {
        width: 35px;
        height: 35px;
        border-radius: 5px;
    }

    .user_name {
        margin-left: 10px;
        margin-top: 6px;
        font-size: 15px;
        font-weight: bold;
    }

    .user_qrCode {
        width: 50px;
        width: 50px;
    }

    //abc
    .pageCode {
        width: 100vw;
        height: 100vh;
        background-color: #bfddff;
    }

    .qr_room_img {
        width: 257px;
        height: auto;
    }

    .qc_code {
        display: flex;
        justify-content: center;

        .code-box {
            margin-top: 50px;
            width: 90vw;
            height: auto;
            background-color: #ffffff;
            padding-bottom: 20px;
        }

        .top {
            display: flex;
            align-items: center;
            padding: 28px 28px 20px 28px;

            .left {
                margin-right: 15px;
            }
        }

        .shop_name {
            font-size: 17px;
            color: #222;
            font-weight: bold;
        }

        .shop_describe {
            color: #818181;
        }

        .shop_guide {
            margin-top: 17px;
            text-align: center;
            color: #818181;
        }
    }
</style>
