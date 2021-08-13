<template>
    <div>
        <div v-if="target_type==2">
            <div class="back_view" @click="backSeek" v-if="showPdf">返回查看</div>
            <div class="pdf_cont" v-if="!showPdf">
                <img src="../../static/images/pdf.png" alt="">
                <div class="title">{{pdfData.title}}</div>
                <div class="preview_seek">
                    <span @click="previewSeek">预览查看</span>
                </div>
                <div class="preview_seek">
                    <span @click="downloadPdf">下载PDF</span>
                </div>
            </div>
            <pdf ref="pdf" :src="pdfData.pdf" v-if="showPdf"/>
        </div>
        <div v-if="target_type==3" class="article_page">
            <h2 class="article_title">{{articleData.title}}</h2>
            <p><span class="">{{articleData.title}}</span></p>
            <p><span class="article_author">{{articleData.author}}</span></p>
            <div v-html="articleData.content" id="show_content"></div>
        </div>
    </div>
</template>

<script>
    import pdf from 'vue-pdf'
    import {getRadarApi, openUserInfoApi} from "../../api/radar";

    export default {
        components: {
            pdf
        },
        data() {
            return {
                //  微信用户数据
                showPdf: false,
                weChatUserData: {},
                link: '',
                //pdf数据
                pdfData: {},
                //文章数据
                articleData: {}
            }
        },
        created() {
            //  获取参数
            this.target_type = this.$route.query.type;
            this.staff_id = this.$route.query.employee_id;
            this.radar_id = this.$route.query.id;
            this.target_id = this.$route.query.target_id;
            this.getUserData()
        },
        methods: {
            //跳转下载
            downloadPdf() {
                window.location.href = this.pdfData.pdf
            },
            //返回查看
            backSeek() {
                this.showPdf = false
            },
            //预览查看
            previewSeek() {
                this.showPdf = true
            },
            //获取用户数据
            getUserData() {
                let that = this;
                openUserInfoApi({
                    id: that.radar_id
                }).then((res) => {
                    if (res.data.openid === undefined) {
                        let redirectUrl = '/auth/radar?id=' + that.radar_id + '&target=' + encodeURIComponent(that.url);
                        that.$redirectAuth(redirectUrl);
                    }
                    this.weChatUserData = res.data;
                  this.getRadarData();
                });

            },
            //  获取数据
            getRadarData() {
                let params = {
                    union_id: this.weChatUserData.unionid,
                    nickname: this.weChatUserData.nickname,
                    avatar: this.weChatUserData.headimgurl,
                    target_type: this.target_type,
                    radar_id: this.radar_id,
                    target_id: this.target_id,
                    staff_id: this.staff_id
                }
                getRadarApi(params).then((res) => {
                    if (this.target_type == 1) {
                        window.location.href = res.data.link
                    } else if (this.target_type == 2) {
                        this.pdfData = res.data
                        document.title = "雷达PDF"
                    } else if (this.target_type == 3) {
                        this.articleData = res.data.article
                        document.title = "雷达文章"
                    }
                })
            }
        }
    }
</script>
<style scoped lang="scss">
    .pdf_cont {
        padding-top: 57px;
        text-align: center;

        img {
            width: 75px;
            height: 75px;
        }

        .title {
            margin-top: 20px;
            color: #000;
            font-size: 17px;
        }
    }

    .back_view {
        padding-left: 25px;
        margin-top: 25px;
        font-size: 16px;
        display: inline-block;
    }

    .preview_seek {
        margin-top: 15px;
        font-size: 16px;
    }

    .article_page {
        padding: 15px;
        width: 100%;
        overflow-x: hidden;
    }

    /deep/ #js_content {
        visibility: visible !important;
    }

    /deep/ .rich_media_content {
        visibility: visible !important;
    }

    .article_title {
        font-weight: bold;
    }
</style>
