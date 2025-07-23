module.exports = {
    prefix: 'gm-',
    presets: [
      require('./vendor/statamic/cms/tailwind.config.js'),
    ],
    corePlugins: {
        preflight: false,
    },
}
