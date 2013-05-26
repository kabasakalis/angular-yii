//app initialization code
angular.module('app').run([
   '$rootScope',
    '$log',
    '$http' ,
    '$state',
    '$stateParams',
    'imageService',
    'collectionService',
    'spinnerService',
    'notifyService',
    '$timeout',
    'utilsService',
    function(
        $rootScope,
        $log,
        $http,
        $state,
        $stateParams,
        imageService,
        collectionService,
        spinnerService,
        notifyService,
        $timeout,
        utilsService
        ) {
        //attach model services to $rootScope for global access from any view or scope
        $rootScope.Image = imageService;
        $rootScope.Collection = collectionService;

        //images and collections arrays.They will be populated with results returned from server.
        $rootScope.images = [];
        $rootScope.collections = [];
     //   $rootScope.renderedImages = [];
       //fetch images and collections.
        $rootScope.Image.getAll();
        $rootScope.Collection.getAll();

        //attach state and state parameters to $rootScope to keep track of navigation.
        $rootScope.$state = $state;
        $rootScope.$stateParams = $stateParams;

        //NotifiyService listener registration
        notifyService.registerListener();
        //SpinnerService listener registration
        spinnerService.registerListener();

          //state info log.Useful info for debugging.
        $rootScope.$on('$stateChangeSuccess', function (event, to, toParams, from, fromParams) {
            $log.info('stateEvent', event);
            $log.info('event.currentScope', event.currentScope);
            $log.info('event.targetScope', event.targetScope);
            $log.info('FROM STATE', from.name);
            $log.info('FROM PARAMS', fromParams);
            $log.info('TO STATE ', to.name);
            $log.info('TO  PARAMS', toParams);
        });
    }
]);

