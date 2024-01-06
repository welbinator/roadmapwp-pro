const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'roadmap-block': path.resolve(__dirname, 'src/roadmap-block.js')
    },
};
