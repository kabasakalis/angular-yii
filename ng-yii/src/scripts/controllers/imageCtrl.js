angular.module('app').controller('imageCtrl', [
    '$scope',
    '$rootScope',
    '$location',
    '$log',
    'utilsService',
    'notifyService',
    'MAX_FILES_NUMBER',
    'MAX_COLLECTIONS_NUMBER',
    function ($scope,
                       $rootScope,
                        $location,
                        $log,
                        utilsService,
                        notifyService,
                        MAX_FILES_NUMBER,
                       MAX_COLLECTIONS_NUMBER
        ) {
        $log.info('imageCtrl  $scope', $scope);



        if ($rootScope.images.length===0) $rootScope.$state.transitionTo('images');//page refresh will lead back to images
        $scope.id = $rootScope.$stateParams.id;
        //find image from stateParams id.
        $scope.image = angular.copy(utilsService.findById($rootScope.images, $rootScope.$stateParams.id));
        $log.info('image', $scope.image);
        //store the original value so that we can restore
        $scope.original_image = {};
        angular.copy($scope.image, $scope.original_image);
    }
]);
