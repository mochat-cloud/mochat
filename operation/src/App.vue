<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
import {userInfo,openUserInfoApi} from "@/api/user";
export default {
  mounted() {
    let path = this.$getState('jump');
    if(path){
      let code = this.$getQueryVariable('code');
      let appid=localStorage.getItem('mochat_appid')
      openUserInfoApi({code,appid}).then((res)=>{
        console.log('微信用户信息')
        console.log(res)
        localStorage.setItem('userInfo', JSON.stringify(res.data))
        if(localStorage.getItem('userInfo')){
          if (path) this.$router.push({path})
          if (code) localStorage.setItem('mochat_code', code)
          localStorage.setItem('mochat_url', decodeURIComponent(window.location.href))
        }
      })
    }
  }
}
</script>

<style lang="scss">
*::-webkit-scrollbar {
  display: none;
}
</style>
