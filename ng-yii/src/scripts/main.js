require({
        paths: {
            'plugs': '../plugins',
            'spin': '../plugins/spin/spin'
        },
        shim: {  //Dependencies
            //Controllers
            'controllers/imageCtrl': {
                deps: ['app']
            },
            'controllers/imagesCtrl': {
                deps: ['app' ]
            },
            'controllers/imageFormCtrl': {
                deps: ['app']
            },
            'controllers/collectionFormCtrl': {
                deps: ['app']
            },
            //Models
            'models/imageService': {
                deps: ['app']
            },
            'models/collectionService': {
                deps: ['app']
            },
            //Services
            'services/utilsService': {
                deps: ['app']
            },
            'services/spinnerService': {
                deps: ['app']
            },
            'services/notifyService': {
                deps: ['app']
            },
            //Directives
            'directives/executeClickOnceDirective': {
                deps: ['app', 'libs/jquery-2.0.0' ]
            },
            'directives/spinnerDirective': {
                deps: ['app', 'libs/require', 'spin' ]
            },
            'directives/notyDirective': {
                deps: ['app', 'plugs/noty/jquery.noty' ]
            },
            'directives/fancyboxDirective': {
                deps: ['app', 'plugs/fancybox/jquery.fancybox' ]
            },
            //Configuration
            'config/http.config': {
                deps: ['app']
            },
            'config/states': {
                deps: ['app']
            },
            'config/constants': {
                deps: ['app']
            },
            //Animations
            'animations/animations': {
                deps: ['app']
            },
            //Plugins
            'plugs/fancybox/jquery.fancybox': {
                deps: ['libs/jquery-2.0.0']
            },
            'plugs/fancybox/helpers/jquery.fancybox-buttons': {
                deps: ['plugs/fancybox/jquery.fancybox']
            },
            'plugs/fancybox/helpers/jquery.fancybox-thumbs': {
                deps: ['plugs/fancybox/jquery.fancybox']
            },

            'plugs/select2-3.3.2/select2': {
                deps: ['libs/jquery-2.0.0']
            },

            'plugs/noty/jquery.noty': {
                deps: ['libs/jquery-2.0.0']
            },
            'plugs/noty/layouts/top': {
                deps: [ 'plugs/noty/jquery.noty']
            },
            'plugs/noty/layouts/topLeft': {
                deps: [ 'plugs/noty/jquery.noty']
            },
            'plugs/noty/layouts/topRight': {
                deps: [ 'plugs/noty/jquery.noty']
            },
            'plugs/noty/layouts/inline': {
                deps: [ 'plugs/noty/jquery.noty']
            },
            'plugs/noty/themes/default': {
                deps: [ 'plugs/noty/jquery.noty', 'plugs/noty/layouts/top']
            },
            //Libraries,modules
            'libs/bootstrap-fileupload': {
                deps: [ 'libs/jquery-2.0.0']
            },
            'libs/angular': {
                deps: [ 'libs/jquery-2.0.0']
            },
            'libs/angular-resource': {
                deps: ['libs/angular']
            },
            'libs/angular-ui': {
                deps: ['libs/angular', 'libs/jquery-2.0.0']
            },
            'libs/angular-ui-states': {
                deps: ['libs/angular']
            },
            'plugs/ui-bootstrap/ui-bootstrap-tpls-0.3.0': {
                deps: ['libs/angular']
            },
            //main app module
            'app': {
                deps: ['libs/angular', 'libs/angular-resource', 'libs/angular-ui', 'plugs/ui-bootstrap/ui-bootstrap-tpls-0.3.0', 'libs/angular-ui-states']
            },
            'bootstrap': {
                deps: [
                    'libs/jquery-2.0.0',
                    'app',
                    'models/imageService',
                    'models/collectionService',
                    'libs/angular-ui',
                    'plugs/ui-bootstrap/ui-bootstrap-tpls-0.3.0',
                    'libs/angular-ui-states',
                    'models/collectionService',
                    'services/spinnerService',
                    'services/notifyService',
                    'services/utilsService',
                    'config/http.config',
                    'config/states',
                    'config/constants',
                    'animations/animations',
                    'controllers/imagesCtrl',
                    'controllers/imageCtrl',
                    'directives/fancyboxDirective',
                    'plugs/select2-3.3.2/select2',
                    'controllers/imageFormCtrl',
                    'controllers/collectionFormCtrl',
                    'directives/spinnerDirective',
                    'directives/notyDirective',
                    'directives/executeClickOnceDirective',
                    'views'
                ]
            },
            'run': {
                deps: ['app']
            },
            'views': {
                deps: ['app']
            }
        }
    }, [
    'require',
    //app
    'app',
    'run',
    'views',
    'bootstrap',
    //Controllers
    'controllers/imageCtrl',
    'controllers/imagesCtrl',
    'controllers/imageFormCtrl',
    'controllers/collectionFormCtrl',
    //Models
    'models/imageService',
    'models/collectionService',
    //Services
    'services/utilsService',
    'services/notifyService',
    'services/spinnerService',
    //Directives
    'directives/executeClickOnceDirective',
    'directives/spinnerDirective',
    'directives/notyDirective',
    'directives/fancyboxDirective',
    //Configuration
    'config/http.config',
    'config/states',
    'config/constants',
    //Animations
    'animations/animations',
     //Plugins
    'libs/jquery-2.0.0',
    'plugs/fancybox/jquery.fancybox',
    'plugs/fancybox/helpers/jquery.fancybox-buttons',
    'plugs/fancybox/helpers/jquery.fancybox-thumbs',
    'plugs/noty/jquery.noty',
    'plugs/noty/layouts/top',
    'plugs/noty/layouts/topLeft',
    'plugs/noty/layouts/topRight',
    'plugs/noty/layouts/inline',
    'plugs/noty/themes/default',
    'plugs/select2-3.3.2/select2',
    'spin',
    //Modules
    'plugs/ui-bootstrap/ui-bootstrap-tpls-0.3.0',
    'libs/bootstrap-fileupload',
    'libs/angular-ui',
    'libs/angular-ui-states'
],
    function (require) {
        return require(['bootstrap']);
    });
