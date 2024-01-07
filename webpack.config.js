const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'roadmap-block': path.resolve(__dirname, 'src/roadmap-block.js'),
        'roadmap-tabs-block': path.resolve(__dirname, 'src/roadmap-tabs-block.js') // New entry for roadmap-tabs-block
    },
};
