<template>
    <div class="record" v-show="modalShow">
        <div class="bg" @click="modalShow = false"></div>
        <div class="lottery-box">
            <div class="end">×</div>
            <div class="top">
                <p>活动说明</p>
            </div>
            <div class="content">
                <div class="box">
                    {{ description }}
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
            description: ''
        }
    },
    methods: {
        show(description) {
            this.description = description
            this.modalShow = true
        },
        hide() {
            this.modalShow = false
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
    position: relative;
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
        min-height: 200px;
        background-color: #ffffff;
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
    }
}

.end {
    position: absolute;
    font-size: 24px;
    right: 12px;
}

.content {
    width: 78vw;
    min-height: 200px;
}
</style>
