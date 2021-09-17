const path = require('path');

module.exports = {
    entry: './src/Resources/public/scripts/index.js',
    output: {
        library: "OnOfficeImport",
        libraryTarget: "var",
        filename: 'main.js',
        path: path.resolve(__dirname, 'src/Resources/public/scripts/dist'),
    }
};
