import { getConfigInfo } from '@/api/wxconfig'
import { Toast } from 'vant'

const wx = window.wx
// 获取微信注入参数
async function getConfigParam (corpId, uriPath, agentId) {
  try {
    const params = {
      corpId,
      uriPath
    }
    if (agentId) {
      params.agentId = agentId
    }
    const { data } = await getConfigInfo(params)
    return data
  } catch (e) {
    console.log(e)
  }
}
const jsApiList = ['getCurExternalContact', 'sendChatMessage', 'getContext']
// wx.config
export function wxConfig (corpId, uriPath) {
  return new Promise((resolve, reject) => {
    getConfigParam(corpId, uriPath).then(data => {
      const { appId, timestamp, nonceStr, signature } = data
      wx.config({
        beta: true, // 必须这么写，否则wx.invoke调用形式的jsapi会有问题
        debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: appId, // 必填，企业微信的corpID
        timestamp: timestamp, // 必填，生成签名的时间戳
        nonceStr: nonceStr, // 必填，生成签名的随机串
        signature: signature, // 必填，签名，见 附录-JS-SDK使用权限签名算法
        jsApiList // 必填，需要使用的JS接口列表，凡是要调用的接口都需要传进来
      })
      wx.ready(async function () {
        resolve()
      })
      wx.error(function (res) {
        Toast({ position: 'top', message: 'wx.config fail' })
        console.log(res)
        reject(res)
      })
    }).catch(e => {
      reject(e)
    })
  })
}
// wx.agentConfig
export function agentConfig (corpId, uriPath, agentId) {
  return new Promise((resolve, reject) => {
    getConfigParam(corpId, uriPath, agentId).then(data => {
      const { corpid, agentid, timestamp, nonceStr, signature } = data
      wx.agentConfig({
        corpid: corpid, // 必填，企业微信的corpid，必须与当前登录的企业一致
        agentid: agentid, // 必填，企业微信的应用id （e.g. 1000247）
        timestamp: timestamp, // 必填，生成签名的时间戳
        nonceStr: nonceStr, // 必填，生成签名的随机串
        signature: signature, // 必填，签名，见附录-JS-SDK使用权限签名算法
        jsApiList,
        success: function (res) {
          resolve()
        },
        fail: function (res) {
          Toast({ position: 'top', message: 'wx.agentConfig fail' })
          reject(res)
          if (res.errMsg.indexOf('function not exist') > -1) {
            alert('版本过低请升级')
          }
        }
      })
    }).catch(e => {
      reject(e)
    })
  })
}
export function getContext () {
  return new Promise((resolve, reject) => {
    wx.invoke('getContext', {
    }, function (res) {
      if (res.err_msg == 'getContext:ok') {
        const entry = res.entry // 返回进入H5页面的入口类型，目前有normal、contact_profile、single_chat_tools、group_chat_tools
        resolve(entry)
      } else {
      // 错误处理
        reject(res.err_msg)
      }
    })
  })
}
// 获取当前对话客户微信userId
export function getCurExternalContact () {
  return new Promise((resolve, reject) => {
    wx.invoke('getCurExternalContact', {
    }, function (res) {
      if (res.err_msg == 'getCurExternalContact:ok') {
        const userId = res.userId // 返回当前外部联系人userId
        // commit('SET_WX_USER_ID', userId)
        resolve(userId)
      } else {
        Toast({ position: 'top', message: res.err_msg })
        // 错误处理
        reject(res.err_msg)
      }
    })
  })
}

export function sendChatMessage (type, content) {
  // 1 文本 2 图片 3 图文 5 视频 7文件
  let param
  switch (type) {
    case 1:
      param = {
        msgtype: 'text',
        text: {
          content
        }
      }
      break
    case 2:
      param = {
        msgtype: 'image',
        image: {
          mediaid: content
        }
      }
      break
    case 3:
      param = {
        msgtype: 'news',
        news: content
      }
      break
    case 5:
      param = {
        msgtype: 'video',
        video: {
          mediaid: content
        }
      }
      break
    case 7:
      param = {
        msgtype: 'file',
        file: {
          mediaid: content
        }
      }
      break
  }
  return new Promise((resolve, reject) => {
    wx.invoke('sendChatMessage', param, function (res) {
      if (res.err_msg == 'sendChatMessage:ok') {
        // 发送成功
        resolve()
      } else {
        Toast({ position: 'top', message: res.err_msg })
        reject(res.err_msg)
      }
    })
  })
}
