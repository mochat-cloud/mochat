import storage from 'store'

const app = {
  state: {
    sideCollapsed: false,
    isMobile: false,
    multiTab: true
  },
  mutations: {
    SIDEBAR_TYPE: (state, type) => {
      state.sideCollapsed = type
      storage.set('SIDEBAR_TYPE', type)
    },
    TOGGLE_MOBILE_TYPE: (state, isMobile) => {
      state.isMobile = isMobile
    }
  },
  actions: {

  }
}

export default app
