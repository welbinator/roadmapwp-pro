module.exports = function( grunt ) {
    var pkg = grunt.file.readJSON('package.json');
	grunt.initConfig( {
		compress: {
			main: {
				options: {
					archive: 'roadmapwp-pro-' + pkg.version + '.zip',
				},
				files: [
					{ src: [ 'wp-roadmap-pro.php' ], dest: '/', filter: 'isFile' },
                    { src: [ 'gutenberg-market.php' ], dest: '/', filter: 'isFile' },
					{ src: [ 'README.md' ], dest: '/', filter: 'isFile' },
                    { src: [ 'CHANGELOG.md' ], dest: '/', filter: 'isFile' },
					{ src: [ 'build/**' ], dest: '/' },
					{ src: [ 'dist/**' ], dest: '/' },
					{ src: [ 'app/**' ], dest: '/' },
                    { src: [ 'pro/**' ], dest: '/' },
				],
			},
		},
	} );
	grunt.registerTask( 'default', [ 'compress' ] );

	grunt.loadNpmTasks( 'grunt-contrib-compress' );
};
