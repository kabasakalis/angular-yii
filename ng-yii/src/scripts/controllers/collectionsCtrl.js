angular.module('app').controller('collectionsCtrl', [
    '$scope',
    '$rootScope',
    '$location',
    '$log',
    'imageService',
    'collectionService',
    'notifyService', 'spinnerService',
    '$timeout',
    'utilsService',
    function ($scope, $rootScope, $location, $log, imageService, collectionService, notifyService, spinnerService, $timeout, utilsService) {
    //Images that will be shown after collection is selected
        $scope.collections=[];

        //watching for collections event to be broadcasted as soon as collections arrive from server (see  collectionService)
              $scope.$on('collections', function (event, collections) {
              angular.copy(collections, $scope.collections);
                        });

        //watch selectedCollectionModel and set the rendered images accordingly
        $scope.$watch('selectedCollectionModel', function (newValue, oldValue) {
            if (!angular.equals(newValue.id, "all")) {     //if we select any option other than all,filter the images
                $scope.selectedCollectionModel = newValue;
                $scope.renderedImages = utilsService.findById($rootScope.collections, $scope.selectedCollectionModel.id).images;
            } else   $scope.renderedImages = $rootScope.images;    //else show all images
        }, true);
    }
]);
