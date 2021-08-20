const getters = {
  navShow: state => state.app.navShow,
  contactId: state => state.app.contactInfo.id || '',
  userInfo: state => state.app.userInfo,
  contactWxUserId: state => state.app.contactWxUserId
}
export default getters
