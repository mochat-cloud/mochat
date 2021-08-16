import Vue from 'vue'

Vue.prototype.$getQueryVariable = name => {
    const reg = new RegExp(`(^|&)?` + name + `=([^&]*)(&|$)`, `i`);
    const r = window.location.href.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

Vue.prototype.$getState = (name, url = '') => {
    const newUrl = url ? url : decodeURIComponent(window.location.href);

    const match = newUrl.match(/&state=(.+)/i);

    if (!match) return false;

    const state = match[1].replace(/#\/(.+)/, '');

    const reg = new RegExp(`(^|&)?` + name + `=([^&]*)(&|$)`, `i`);
    if(state.match(reg)==null){
        return ''
    }else{
        return state.match(reg)[2];
    }
}

Vue.prototype.$redirectAuth = (url = '') => {
    if (url === '') {
        return;
    }
    url = process.env.BASE_URL + url;
    window.location.href = process.env.BASE_URL + url;
}