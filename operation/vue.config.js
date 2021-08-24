const env = process.env.NODE_ENV === 'production' || process.env.NODE_ENV === 'preview'

module.exports = {
    publicPath: env
    ? 'http://img-gewu.jifenone.com/operation_images/'
    : '/',
}
