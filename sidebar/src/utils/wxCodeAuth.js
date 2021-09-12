import { getJssdkConfig } from '@/api/wxconfig'
import { Toast } from 'vant'
import { getCookie, getQuery } from '@/utils/index'
import store from '@/store'

const wx = window.wx
// 获取微信注入参数
async function getConfigParam (uriPath, agentId) {
  try {
    const params = {
      uriPath
    }
    if (agentId) {
      params.agentId = agentId
    }
    const { data } = await getJssdkConfig(params)
    return data
  } catch (e) {
    console.log(e)
  }
}
const jsApiList = ['getCurExternalContact', 'sendChatMessage', 'getContext', 'openUserProfile', 'getCurExternalChat', 'openExistedChatWithMsg', 'navigateToAddCustomer']
// wx.config
export function wxConfig (uriPath) {
  return new Promise((resolve, reject) => {
    getConfigParam(uriPath).then(data => {
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
export function agentConfig (uriPath, agentId) {
  return new Promise((resolve, reject) => {
    getConfigParam(uriPath, agentId).then(data => {
      const { corpid, agentid, timestamp, nonceStr, signature } = data
      wx.agentConfig({
        corpid: corpid, // 必填，企业微信的corpid，必须与当前登录的企业一致
        agentid: agentid, // 必填，企业微信的应用id （e.g. 1000247）
        timestamp: timestamp, // 必填，生成签名的时间戳
        nonceStr: nonceStr, // 必填，生成签名的随机串
        signature: signature, // 必填，签名，见附录-JS-SDK使用权限签名算法
        jsApiList,
        success: function (res) {
          // Toast({ position: 'top', message: '注册成功' })
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

export async function initAgentConfig () {
  if (store.getters.initAgentConfig) {
    return
  }

  let agentId = getQuery('agentId')

  if (!agentId) {
    agentId = getCookie('agentId')
  }

  if (!agentId) {
    return
  }

  // 从企业微信3.0.24及以后版本（可通过企业微信UA判断版本号），无须先调用wx.config，可直接wx.agentConfig.
  // await wxConfig(fullPath)
  const url = window.location.href.split('#')[0]
  await agentConfig(encodeURIComponent(url), agentId)
  store.commit('SET_INIT_AGENT_CONFIG', true)
}

export async function getContext (retry) {
  await initAgentConfig()
  return new Promise((resolve, reject) => {
    wx.invoke('getContext', {
    }, async function (res) {
      if (res.err_msg === 'getContext:ok') {
        const entry = res.entry // 返回进入H5页面的入口类型，目前有normal、contact_profile、single_chat_tools、group_chat_tools
        resolve(entry)
      } else {
        if (retry !== true) {
          store.commit('SET_INIT_AGENT_CONFIG', false)
          resolve(await getContext(true))
        } else {
          // 错误处理
          reject(res.err_msg)
        }
      }
    })
  })
}
// 获取当前对话客户微信userId
export async function getCurExternalContact (retry) {
  await initAgentConfig()
  return new Promise((resolve, reject) => {
    wx.invoke('getCurExternalContact', {
    }, async function (res) {
      if (res.err_msg === 'getCurExternalContact:ok') {
        const userId = res.userId // 返回当前外部联系人userId
        // commit('SET_WX_USER_ID', userId)
        resolve(userId)
      } else {
        if (retry !== true) {
          store.commit('SET_INIT_AGENT_CONFIG', false)
          resolve(await getCurExternalContact(true))
        } else {
          // 错误处理
          reject(res.err_msg)
        }
      }
    })
  })
}

export async function sendChatMessage (type, content, retry) {
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
  await initAgentConfig()
  return new Promise((resolve, reject) => {
    wx.invoke('sendChatMessage', param, async function (res) {
      if (res.err_msg === 'sendChatMessage:ok') {
        // 发送成功
        resolve()
      } else {
        if (retry !== true) {
          store.commit('SET_INIT_AGENT_CONFIG', false)
          resolve(await sendChatMessage(type, content, true))
        } else {
          Toast({ position: 'top', message: res.err_msg })
          reject(res.err_msg)
        }
      }
    })
  })
}
// getCurExternalChat
export async function openUserProfile (type, userid, retry) {
  const params = {
    type,
    userid
  }
  await initAgentConfig()
  return new Promise((resolve, reject) => {
    wx.invoke('openUserProfile', params, async function (res) {
      if (res.err_msg === 'openUserProfile:ok') {
        // const userId = res.userId // 返回当前外部联系人userId
        // resolve(userId)
      } else {
        if (retry !== true) {
          store.commit('SET_INIT_AGENT_CONFIG', false)
          await openUserProfile(type, userid, true)
        } else {
          Toast({ position: 'top', message: res.err_msg })
          // 错误处理
          reject(res.err_msg)
        }
      }
    })
  })
}
// 获取当前客户群的群ID
export async function getCurExternalChat (retry) {
  await initAgentConfig()
  return new Promise((resolve, reject) => {
    wx.invoke('getCurExternalChat', {}, async function (res) {
      if (res.err_msg === 'getCurExternalChat:ok') {
        const chatId = res.chatId // 返回当前外部联系人userId
        resolve(chatId)
      } else {
        if (retry !== true) {
          store.commit('SET_INIT_AGENT_CONFIG', false)
          resolve(await getCurExternalChat(true))
        }
      }
    })
  })
}
// 打开当前群聊
export async function openExistedChatWithMsg (chatId, retry) {
  await initAgentConfig()
  const params = {
    chatId
  }
  return new Promise((resolve, reject) => {
    wx.invoke('openExistedChatWithMsg', params, async function (res) {
      if (res.err_msg === 'openExistedChatWithMsg:ok') {
      } else {
        if (retry !== true) {
          store.commit('SET_INIT_AGENT_CONFIG', false)
          resolve(await openExistedChatWithMsg(chatId, true))
        } else {
          Toast({ position: 'top', message: res.errmsg })
          // 错误处理
          reject(res.errmsg)
        }
      }
    })
  })
}
// export function openEnterpriseChat (chatId) {
//   wx.openEnterpriseChat({
//     chatId,
//     success: function (res) {
//
//     },
//     fail: function (res) {
//       Toast({ position: 'top', message: res.errmsg })
//       if (res.errMsg.indexOf('function not exist') > -1) {
//         alert('版本过低请升级')
//       }
//     }
//   })
// }

// 进入添加客户界面
export async function navigateToAddCustomer () {
  await initAgentConfig()
  return new Promise((resolve, reject) => {
    wx.invoke('navigateToAddCustomer',
      {},
      function (res) {
      }
    )
  })
}
