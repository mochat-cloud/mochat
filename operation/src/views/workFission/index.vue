<template>
    <div class="page">
        <div class="top">
            <!--  顶部-->
            <div class="reply" v-if="dataList.fowardText">
                <div class="content">
                    <div class="title">
                        <div class="tip">推荐转发文案:</div>
                        <div class="copy" @click="copyText(dataList.fowardText)">复制</div>
                    </div>
                    <div class="text">{{dataList.fowardText}}</div>
                </div>
            </div>
            <div class="tips">
                <div class="tip">
                    <span class="text">长按保存专属海报分享给好友</span>
                </div>
                <div class="tip">
                    <span class="text long-text">好友扫码添加微信后即助力成功</span>
                </div>
            </div>
            <div class="code-image">
                <div class="code-border">
                    <!--  个人海报  -->
                    <div id="code-template" ref="template" v-if="posterType == 0">
                        <img class="bg" ref="bg" :style="{height: bgHeight}" @load="setHeight" :src="dataList.coverPic">
                        <div class="user-info">
                            <img class="avatar" :src="wxUserData.headimgurl" v-if="dataList.avatarShow">
                            <div class="name" :style="{color: dataList.nicknameColor}" v-if="dataList.nicknameShow">
                                {{wxUserData.nickname}}
                            </div>
                        </div>
                        <img class="qrcode"
                             :style="{width: dataList.qrcodeW / 222 * 100 + '%', height: dataList.qrcodeH / 396 * 100 + '%', left: dataList.qrcodeX / 222 * 100 + '%', top: dataList.qrcodeY / 396 * 100  + '%'}"
                             :src="dataList.qrcodeUrl">
                    </div>
                    <!--          个人名片-->
                    <div id="code-template-simple" ref="template_simple" v-if="posterType== 1">
                        <div class="info">
                            <div class="left">
                                <img class="logo" :src="dataList.cardCorpLogo">
                            </div>
                            <div class="right">
                                <div class="title">{{dataList.cardCorpImageName}}</div>
                                <div class="name">{{dataList.cardCorpName}}</div>
                            </div>
                        </div>
                        <div class="code">
                            <img class="qrcode" :src="dataList.qrcodeUrl">
                            <img class="logo" :src="dataList.cardCorpLogo">
                        </div>
                        <div class="tip">扫一扫上面的二维码图案</div>
                        <div class="tip">加我企业微信</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bottom-box">
            <div class="long-btn"
                 @click="$router.push('/speed?fission_id='+fission_id+'&union_id='+wxUserData.unionid)">
                查看助力进度
            </div>
        </div>
        <input type="text" ref="copyInput" style="position: fixed; opacity: 0">
    </div>
</template>

<script>
    import {posterApi, openUserInfoApi} from "@/api/workFission";

    export default {
        name: "index",
        data() {
            return {
                dataList: {},
                userInfo: {},
                bgHeight: 'auto',
                base64Img: '',
                //  微信用户数据
                wxUserData: {},
                //  页面类型
                posterType: 0
            }
        },
        created() {
            this.fission_id = this.$route.query.id;
            this.getOpenUserInfo();
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
                    this.getPosterData();
                });
            },
            //获取页面海报信息
            getPosterData() {
                let params = {
                    fission_id: this.fission_id,
                    union_id: this.wxUserData.unionid,
                    nickname: this.wxUserData.nickname,
                    avatar: this.wxUserData.headimgurl
                }
                posterApi(params).then((res) => {
                    // console.log(res)
                    this.posterType = res.data.posterType
                    this.dataList = res.data
                })
            },
            copyText(text) {
                const inputElement = this.$refs.copyInput;
                inputElement.value = text;
                inputElement.select();
                document.execCommand('Copy');
                this.$message.success('复制成功')
            },
            setHeight() {
                this.bgHeight = (this.$refs.bg.width * 1.78) + 'px';
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

                .code-border {
                    width: 100%;
                    padding: 16px;
                    background-color: white;
                    border-radius: 12px;

                    #code-template {
                        position: relative;

                        .bg {
                            width: 100%;
                        }

                        .user-info {
                            position: absolute;
                            top: 10px;
                            left: 10px;
                            display: flex;
                            align-items: center;

                            .avatar {
                                margin-right: 10px;
                                $size: 36px;
                                width: $size;
                                height: $size;
                                border-radius: 50%;
                            }

                            .name {
                                font-size: 14px;
                            }
                        }

                        .qrcode {
                            width: 50px;
                            height: 50px;
                            position: absolute;
                            bottom: 0;
                            right: 0;
                        }
                    }

                    #code-template-simple {
                        border: 1px solid gray;
                        padding: 20px;

                        .info {
                            display: flex;
                            align-items: center;
                            margin-bottom: 16px;

                            .left {
                                margin-right: 10px;

                                .logo {
                                    $size: 34px;
                                    width: $size;
                                    height: $size;
                                }
                            }

                            .right {
                                .title {
                                    font-size: 14px;
                                    color: #000000;
                                }

                                .name {
                                    font-size: 12px;
                                }
                            }
                        }

                        .code {
                            width: 84%;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            position: relative;
                            margin: 0 auto 20px;

                            .qrcode {
                                $size: 100%;
                                width: $size;
                                height: $size;
                            }

                            .logo {
                                border: 1px solid white;
                                position: absolute;
                                $size: 24%;
                                width: $size;
                                height: $size;
                            }
                        }

                        .tip {
                            text-align: center;
                        }
                    }
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

    .user_img {
        width: 35px;
        height: 35px;
        border-radius: 5px;
    }
</style>
