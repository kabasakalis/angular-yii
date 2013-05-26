/*  Error Codes
0 - No errors!
1 - Fatal error
2 - Missing gruntfile error
3 - Task error
4 - Template processing error
5 - Invalid shell auto-completion rules error
6 - Warning*/
var path;
path = require('path');

module.exports = function (grunt) {
 grunt.initConfig({

     //Bake angular html  views/templates  into cachable js,used in prod mode.
     html2js: {
        options: {
        },
        main: {
          src: [  './src/views/*.html'],
          dest: './src/scripts/views.js'
        }
      },
        //Clean dev,temp,prod directories
        clean: {
            dev: {
                src: ['./dev/', './.temp/']
            },
            prod: {
               src: ['./prod/', './.temp/']
                        }
        },

//Concatenate css for production
        concat: {
            options: {
                //   separator: ';'
            },
            prod_css: {
                src: [
                    //main app css
                    './src/css/bootstrap_cyborg.css',
                    './src/css/styles.css',
                    //plugins css
                    './src/plugins/ui-bootstrap/bootstrap-fileupload.css',
                    './src/plugins/ui/angular-ui.css',
                    './src/plugins/select2-3.3.2/select2.css',
                    './src/plugins/fancybox/jquery.fancybox.css',
                    './src/plugins/fancybox/helpers/jquery.fancybox-buttons.css',
                    './src/plugins/fancybox/helpers/jquery.fancybox-thumbs.css'
                ],
                dest: './.temp/css/app.css'
            }
        },

        //Copy
        copy: {
            temp_to_dev: {
                files: [
                    {
                        cwd: './.temp/',
                        src: '**',
                        dest: './dev/',
                        expand: true
                    }
                ]
            },
            temp_to_prod: {
                           files: [
                               {
                                   cwd: './.temp/',
                                   src: '**',
                                   dest: './prod/',
                                   expand: true
                               }
                           ]
                       },
            img_from_src_to_temp: {
                files: [
                    {
                        cwd: './src/',
                        src: 'img/**/*.*',
                        dest: './.temp/',
                        expand: true
                    }
                ]
            },
            plugins_from_src_to_temp: {
                files: [
                    {
                        cwd: './src/',
                        src: './plugins/**/*.*',
                        dest: './.temp/',
                        expand: true
                    }
                ]
            },
            css_from_src_to_dev: {
                                       files: [
                                           {
                                               cwd: './src/',
                                               src: 'css/*.css',
                                               dest: './dev/',
                                               expand: true
                                           }
                                       ]
                                   },
            css_from_src_to_temp: {
                                                   files: [
                                                       {
                                                           cwd: './src/',
                                                           src: 'css/*.css',
                                                           dest: './.temp/',
                                                           expand: true
                                                       }
                                                   ]
                                               },
          index_from_temp_to_dev : {
                files: [
                  {
                    cwd: './.temp/',
                    src: 'index.html',
                    dest: './dev/',
                    expand: true
                  }
                ]
              },
           js_from_src_to_dev: {
                              files: [
                                {
                                  cwd: './src/',
                                  src: 'scripts/**/*.js',
                                  dest: './dev/',
                                  expand: true
                                }
                              ]
                            },

            js_from_src_to_temp: {
                files: [
                    {
                        cwd: './src/',
                        src: 'scripts/**/*.js',
                        dest: './.temp/',
                        expand: true
                    }
                ]
            },
            js_from_temp_to_prod: {
                          files: [
                              {
                                  cwd: './.temp/',
                                  src: 'scripts/scripts.min.js',
                                  dest: './.prod/scripts/',
                                  expand: true
                              }
                          ]
                      },

            prod_plugin_images_from_src_to_temp: {
                files: [
                    {
                        cwd: './src/plugins/select2-3.3.2',
                        src: '*.png',
                        dest: './.temp/css',
                        expand: true
                    },
                    {
                        cwd: './src/plugins/select2-3.3.2',
                        src: '*.gif',
                        dest: './.temp/css',
                        expand: true
                    },
                    {
                        cwd: './src/plugins/fancybox',
                        src: '*.png',
                        dest: './.temp/css',
                        expand: true
                    },
                    {
                        cwd: './src/plugins/fancybox',
                        src: '*.gif',
                        dest: './.temp/css',
                        expand: true
                    }
                ]
            },
            views_from_temp_to_dev: {
                files: [
                    {
                        cwd: './.temp/',
                        src: 'views/**',
                        dest: './dev/',
                        expand: true
                    }
                ]
            }
        },
        express: {
            livereload: {
                options: {
                    port: 3005,
                    bases: path.resolve('./dev'),
                    debug: true,
                    monitor: {},
                    server: path.resolve('./server')
                }
            }
        },
        imagemin: {
            img: {
                files: [
                    {
                        cwd: './src/',
                        src: 'img/**/*.*',
                        dest: './.temp/',
                        expand: true
                    }
                ],
                options: {
                    optimizationLevel: 7
                }
            }
        },
      /*  less: {
            styles: {
                files: {
                }
            }
        },*/
        minifyHtml: {
            prod: {
                files: {
                    './.temp/index.min.html': './.temp/index.html'
                }
            }
        },
        regarde: {
            dev: {
                files: './dev/**',
                tasks: 'livereload'
            },
            server: {
                files: ['server.js', 'routes.js'],
                tasks: 'express-restart:livereload'
            }
        },
        requirejs: {
            scripts: {
                options: {
                    baseUrl: './src/scripts/',
                    paths: {
                        'plugs': '../../src/plugins',
                        'spin':'../plugins/spin/spin'
                    },
                    findNestedDependencies: true,
                    logLevel: 0,
                    mainConfigFile: './src/scripts/main.js',
                    name: 'main',
                    onBuildWrite: function (moduleName, path, contents) {
                        var modulesToExclude, shouldExcludeModule;
                        modulesToExclude = ['main'];
                        shouldExcludeModule = modulesToExclude.indexOf(moduleName) >= 0;
                        if (shouldExcludeModule) {
                            return '';
                        }
                        return contents;
                    },
                      optimize: 'uglify',
                  //  optimize: 'none',//for debugging
                    out: './.temp/scripts/scripts.min.js',
                    preserveLicenseComments: false,
                    skipModuleInsertion: true,
                    uglify: {
                        no_mangle: true
                    }
                }
            },
            styles: {
                options: {
                    baseUrl: './.temp/styles/',
                    cssIn: './.temp/css/app.css',
                    logLevel: 0,
                    optimizeCss: 'standard',
                    out: './.temp/css/app.min.css'
                }
            }
        },
        template: {
            views: {
                files: {
                    './.temp/views/': './src/views/'
                }
            },
            dev_index: {
                files: {
                    './.temp/index.html': './src/index.htm'
                },
                environment: 'dev'
            },
            prod_index: {
                files: {
                    './.temp/index.html': './src/index.htm'
                },
                environment: 'prod'
            }
        },


     //To have Karma Test Runner run every time files change,just start the server with grunt karma:unit ,
     // (or just  Run a configuration  of  Karma,if you are in PHP Storm),
     // and then in a parallel process ,grunt dev(to apply non-related karma watch tasks.-see registered tasks below for details).
     karma: {
         options: {
                             autoWatch: true,
                             browsers: ['Chrome'],
                             colors: true,
                             configFile: './karma.config.js',
                             keepalive: true,
                             port:9876,
                             reporters: ['progress'],
                             runnerPort: 9100,
                             singleRun: false
                         },
       unit: {
           background: false,
       },
         continuous: {
            singleRun: true,
            browsers: ['PhantomJS']
          }
     },

        watch: {      //watch for changes as you edit src files and copy them to dev

            index: {
                files: './src/index.htm',
                tasks: ['template:dev_index', 'copy:index_from_temp_to_dev']
            },
            scripts: {
                files: './src/scripts/**',
                tasks: [ 'copy:js_from_src_to_dev']
            },
            styles: {
                files: './src/css/**/*.css',
                tasks: [  'copy:css_from_src_to_dev']
            },
            views: {
                files: './src/views/**/*.*',
                tasks: ['template:views', 'copy:views_from_temp_to_dev']
            },
             //run unit tests with karma (server needs to be already running)
            //unfortunately watch:karma blocks the rest of watch tasks,so we don't use  it.
            //Instead we start the karma server with autowatch set to true,see karma configuration above.

          //  karma: {
           //     files: ['./src/scripts/**', './test/scripts/**/*.*.js'],
            //    tasks: ['karma:unit:run'] //NOTE the :run flag
            //  }

        }
    });


    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-livereload');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-express');
    grunt.loadNpmTasks('grunt-hustler');//the html template parser is in this
    grunt.loadNpmTasks('grunt-html2js');
    grunt.loadNpmTasks('grunt-regarde');
    grunt.loadNpmTasks('grunt-karma');

    grunt.registerTask('server', ['livereload-start', 'express', 'regarde']);

 grunt.registerTask('empty_views_cache', '', function() {
            grunt.file.write('./src/scripts/views.js', '/*Do not delete this empty file,it resets the views cache in dev mode.*/');
        });
    grunt.registerTask('default',
        [
       'clean:dev',//reset dev and temp directories
       'empty_views_cache',//Reset views.js to empty content  (views cache).
        'copy:js_from_src_to_temp',
       'copy:css_from_src_to_temp',
    //   'less',
        'copy:img_from_src_to_temp',
       'copy:plugins_from_src_to_temp',
       'template:views', //compile views and copy  to .temp
       'template:dev_index', //compile index and copy to .temp
        'copy:temp_to_dev'  //copy .temp to dev
        ]);
    grunt.registerTask('dev', ['default', 'watch']);
    grunt.registerTask('prod',
        [
           'clean:prod',//reset prod  and .temp directories
            'concat:prod_css', //concatenate all css files to app.css and copy to .temp
            'copy:img_from_src_to_temp',
            'copy:prod_plugin_images_from_src_to_temp', //so that the concatenated plugin css files can reference their images
            'imagemin',
            'html2js',  //cache  html partials in views folder.Create views.js to be optimized with the rest of the js files.
            'requirejs', //concatenate and minify all js files,minimize the concatenated app.css file.Copy them to .temp folder.
           'template:prod_index', //compile index and copy to .temp
            'minifyHtml', //yeah,minify even the index html file!exaggeration?Maybe,but If we can,why not?:P
           'copy:temp_to_prod'  //copy .temp to prod
        ]);
};
