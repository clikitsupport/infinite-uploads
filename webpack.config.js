/**
 * External Dependencies
 */
const path = require('path');

/**
 * WordPress Dependencies
 */
const defaultConfig = require('@wordpress/scripts/config/webpack.config.js');

// Add configuration for react-jsx-runtime polyfill
const reactJSXRuntimePolyfill = {
  entry: {
    'react-jsx-runtime': {
      import: 'react/jsx-runtime',
    },
  },
  output: {
    path: path.resolve(__dirname, 'assets/js'),
    filename: 'react-jsx-runtime.js',
    library: {
      name: 'ReactJSXRuntime',
      type: 'window',
    },
  },
  externals: {
    react: 'React', // Use React provided by WordPress
  },
};

module.exports = {
  ...defaultConfig,
  ...{
    entry: {
	    block: path.resolve(process.cwd(), 'inc/video/block', 'index.js'),
	    admin: [
		    path.resolve(process.cwd(), 'inc/video/admin', 'index.js'),
		    path.resolve(process.cwd(), 'inc/assets/css', 'admin.css'),
	    ],
    },

  },
  ...reactJSXRuntimePolyfill, // Include polyfill configuration
};
