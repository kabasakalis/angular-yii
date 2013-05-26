var tests = Object.keys(window.__karma__.files).filter(function (file) {
    return /Spec\.js$/.test(file);
});  //get the test files from all files that karma watches.

//console.log(tests);

requirejs.config({
    // Karma serves files from '/base'
    baseUrl: '/base/src/scripts/',
    paths: {
        'plugs': '../plugins',
        'test': '../../test'
    },
    shim: {
        'app': {
            deps: [
                '/base/test/scripts/libs/angular-1.1.3.js',
                '/base/test/scripts/libs/angular-resource-1.1.3.js',
                '/base/src/scripts/libs/angular-ui.js',
                'plugs/ui-bootstrap/ui-bootstrap-tpls-0.3.0',
               'libs/angular-ui-states'
            ]
        },
        '/base/test/scripts/services/utilsServiceSpec.js': {
            deps: [ 'app','/base/src/scripts/services/utilsService.js']
        },
        '/base/test/scripts/services/collectionServiceSpec.js': {
            deps: ['app' , '/base/src/scripts/models/collectionService.js']
        },

        '/base/src/scripts/services/utilsService.js': {
            deps: [ 'app']
        },
        '/base/src/scripts/models/collectionService.js': {
            deps: [
                              'app',
                              '/base/src/scripts/services/spinnerService.js',
                              '/base/src/scripts/services/notifyService.js',
                              '/base/src/scripts/config/constants.js',
            ]
        },
        '/base/src/scripts/services/spinnerService.js': {
            deps: ['app']
        },
        '/base/src/scripts/services/notifyService.js': {
            deps: ['app']
        },
        '/base/src/scripts/config/constants.js': {
            deps: ['app']
        }
    },

    // ask Require.js to load these files (all our tests)
    deps: tests,

    // start test run, once Require.js is done
    callback: window.__karma__.start
});

