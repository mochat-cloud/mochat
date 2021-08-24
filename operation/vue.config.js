const env = process.env.NODE_ENV === 'production' || process.env.NODE_ENV === 'preview'

module.exports = {
    publicPath: env
    ? 'https://assets-gewu.bagrids.com/operation_images/'
    : '/',
}