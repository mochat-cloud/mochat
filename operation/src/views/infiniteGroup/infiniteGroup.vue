<template>
    <div class="qc-code">
        <div class="bottom">
            <div class="code">
              <div class="group_new_Row">
                <img :src="addGroupData.logo" alt="" class="group_logo" v-if="addGroupData.logo!=''">
                <div class="group_news">
                  <div class="group_name">{{ addGroupData.roomName }}</div>
                  <div>{{ addGroupData.describe }}</div>
                </div>
              </div>
              <img :src="addGroupData.qrcode" height="200" width="200" alt=""/>
            </div>
        </div>
    </div>
</template>

<script>
import { qrCodeApi } from '@/api/roomInfinite'
export default {
  data(){
    return{
      addGroupData:{}
    }
  },
  created() {
    this.id = this.$route.query.id
    this.getqrCodeData({id:this.id})
  },
  methods:{
    getqrCodeData(params){
      qrCodeApi(params).then((res)=>{
        document.title = "无限拉群"
        this.addGroupData=res.data
      })
    }
  }
}
</script>
<style scoped lang="scss">
.qc-code {
    width: 100vw;
    height: 100vh;
    display: flex;
    justify-content: center;
}
.bottom {
    box-shadow: 0 0 15px #CCCCCC;
    display: flex;
    justify-content: center;
    margin-top: 120px;
    width: 90vw;
    height: 400px;
    align-items: center;
}
.group_logo{
  width: 32px;
  height: 32px;
}
.group_new_Row{
  display: flex;
  margin-bottom: 15px;
}
.group_news{
  line-height: 16px;
  margin-left: 10px;
  font-size: 10px;
  color: rgba(0,0,0,.65);
}
.group_name{
  font-size: 12px;
  font-weight: 700;
  color: #000;
}
</style>
