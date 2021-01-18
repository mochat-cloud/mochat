import { getWorkContactDetail } from '@/api/customer'
import { getUserInfo } from '@/api/wxconfig'
import { getCurExternalContact } from '@/utils/wxCodeAuth'
import { setStorage } from '@/utils'
// import router from '@/router'
const app = {
  state: {
    navShow: false,
    userInfo: {},
    customerInfo: {},
    customerWxUserId: ''
  },
  mutations: {
    SET_NAV_SHOW: (state, data) => {
      state.navShow = data
    },
    SET_USER_INFO: (state, data) => {
      state.userInfo = data
    },
    SET_CUSTOMER_INFO: (state, data) => {
      state.customerInfo = data
    },
    SET_CUSTOMER_WX_USER_ID: (state, data) => {
      state.customerWxUserId = data
      setStorage('customerWxUserId', data)
    }
  },
  actions: {
    async GET_CUSTOMER_INFO ({ dispatch, state, commit }) {
      try {
        let customerWxUserId = state.customerWxUserId
        if (!customerWxUserId) {
          await dispatch('GET_CUSTOMER_WX_USER_ID')
          customerWxUserId = state.customerWxUserId
        }
        const { data: { id, name, avatar, corpId } } = await getWorkContactDetail({ wxExternalUserid: customerWxUserId })
        const customerInfo = {
          id, name, avatar, corpId
        }
        commit('SET_CUSTOMER_INFO', customerInfo)
      } catch (e) {
        console.log(e)
      }
    },
    async GET_USER_INFO ({ commit }) {
      try {
        const { data } = await getUserInfo()
        const userInfo = data
        commit('SET_USER_INFO', userInfo)
      } catch (e) {
        console.log(e)
      }
    },
    async GET_CUSTOMER_WX_USER_ID ({ commit }) {
      try {
        const customerWxUserId = await getCurExternalContact()
        commit('SET_CUSTOMER_WX_USER_ID', customerWxUserId)
      } catch (e) {
        console.log(e)
      }
    }
  }
}

export default app
