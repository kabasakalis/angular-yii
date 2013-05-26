// base path, that will be used to resolve files and exclude
    basePath: '',

    // frameworks to use
 /*   frameworks: [%FRAMEWORKS%],*/

    // list of files / patterns to load in the browser
 files = [
 	JASMINE,
 	JASMINE_ADAPTER,
     REQUIRE,
     REQUIRE_ADAPTER,


     {pattern: './test/scripts/libs/*.js', included: true},
     {pattern: './src/scripts/libs/*.js', included: false},


     {pattern: './src/scripts/models/**/*.js', included: false},
     {pattern: './src/scripts/controllers/**/*.js', included: false},
     {pattern: './src/scripts/services/**/*.js', included: false},
     {pattern: './src/scripts/directives/**/*.js', included: false},
     {pattern: './src/scripts/config/**/*.js', included: false},
     {pattern: './src/scripts/animations/**/*.js', included: false},
    {pattern: './src/scripts/app.js', included: false},
     {pattern: './src/scripts/bootstrap.js', included: false},
     {pattern: './src/scripts/run.js', included: false},
     {pattern: './src/plugins/ui-bootstrap/ui-bootstrap-tpls-0.3.0.js', included: false},


     //Test files
     {pattern: './test/scripts/services/utilsServiceSpec.js', included: false},
     {pattern: './test/scripts/services/collectionServiceSpec.js', included: false},

     //Bootstrap file for require.js,loads all scripts for testing.
     './test/scripts/main-test.js'

 ],

    // list of files to exclude
    exclude= [
        './test/scripts/libs/jasmine.js',//the adapter takes care of that
        './test/scripts/libs/jasmine-html.js', //the adapter takes care of that
        './test/scripts/libs/angular-mocks.js',//we use test\scripts\libs\angular-mocks-1.1.3.js  instead
        './src/scripts/libs/angular.js', //we use test\scripts\libs\angular-1.1.3.js instead
        './src/scripts/libs/angular-resource.js', //we use test\scripts\libs\angular-resource-1.1.3.js instead
        './src/scripts/libs/html5shiv-printshiv.js',
    ];


    // test results reporter to use
    // possible values: 'dots', 'progress', 'junit', 'growl', 'coverage'
    reporters= ['progress'];

    // web server port
    port= 9876;

    // cli runner port
    runnerPort=9100;

    // enable / disable colors in the output (reporters and logs)
    colors= true,

    // level of logging
    // possible values: .LOG_DISABLE || LOG_ERROR || LOG_WARN || LOG_INFO || LOG_DEBUG
    logLevel=LOG_INFO,


    // enable / disable watching file and executing tests whenever any file changes
    autoWatch=true,


    // Start these browsers, currently available:
    // - Chrome
    // - ChromeCanary
    // - Firefox
    // - Opera
    // - Safari (only Mac)
    // - PhantomJS
    // - IE (only Windows)
    browsers= ['Chrome'],

    // If browser does not capture in given timeout [ms], kill it
    captureTimeout=60000,

    // Continuous Integration mode
    // if true, it capture browsers, run tests and exit
    singleRun=false