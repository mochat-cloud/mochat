<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
import {openUserInfoApi} from "@/api/user";

export default {
  mounted() {
    // let path = this.$getState('jump');
    // if(path){
    //   let appid = this.$getState('appid');
    //   console.log(appid);
    //   console.log(window.location);
    //   let code = this.$getQueryVariable('code');
    //   localStorage.setItem('appid', appid)
    //   openUserInfoApi({code,appid}).then((res)=>{
    //     console.log('微信用户信息')
    //     console.log(res)
    //     localStorage.setItem('userInfo', JSON.stringify(res.data))
    //     if(localStorage.getItem('userInfo')){
    //       if (code) localStorage.setItem('mochat_code', code)
    //       localStorage.setItem('mochat_url', decodeURIComponent(window.location.href))
    //       if (path) this.$router.push({path})
    //     }
    //   })
    // }
  }
}
</script>

<style lang="scss">
*::-webkit-scrollbar {
  display: none;
}
</style>
