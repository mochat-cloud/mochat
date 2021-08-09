const getters = {
  navShow: state => state.app.navShow,
  contactId: state => state.app.customerInfo.id || '',
  userInfo: state => state.app.userInfo,
  customerWxUserId: state => state.app.customerWxUserId
}
export default getters
