import { getWorkContactDetail } from '@/api/contact'
// import { getUserInfo } from '@/api/wxconfig'
// eslint-disable-next-line no-unused-vars
import { getCurExternalContact } from '@/utils/wxCodeAuth'
import { setStorage } from '@/utils'
// import router from '@/router'
const app = {
  state: {
    navShow: false,
    // userInfo: {},
    contactInfo: {},
    contactWxUserId: '',
    initAgentConfig: false
  },
  mutations: {
    SET_NAV_SHOW: (state, data) => {
      state.navShow = data
    },
    // SET_USER_INFO: (state, data) => {
    //   state.userInfo = data
    // },
    SET_CUSTOMER_INFO: (state, data) => {
      state.contactInfo = data
    },
    SET_CUSTOMER_WX_USER_ID: (state, data) => {
      state.contactWxUserId = data
      setStorage('contactWxUserId', data)
    },
    SET_INIT_AGENT_CONFIG: (state, data) => {
      state.initAgentConfig = data
    }
  },
  actions: {
    async GET_CUSTOMER_INFO ({ dispatch, state, commit }) {
      try {
        let contactWxUserId = state.contactWxUserId
        if (!contactWxUserId) {
          await dispatch('GET_CUSTOMER_WX_USER_ID')
          contactWxUserId = state.contactWxUserId
        }
        const { data: { id, name, avatar, corpId } } = await getWorkContactDetail({ wxExternalUserid: contactWxUserId })
        const contactInfo = {
          id, name, avatar, corpId
        }
        commit('SET_CUSTOMER_INFO', contactInfo)
      } catch (e) {
        console.log(e)
      }
    },
    async GET_CUSTOMER_WX_USER_ID ({ commit }) {
      try {
        const contactWxUserId = await getCurExternalContact()
        commit('SET_CUSTOMER_WX_USER_ID', contactWxUserId)
      } catch (e) {
        console.log(e)
      }
    }
  }
}

export default app
