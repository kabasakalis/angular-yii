angular.module('app').controller('collectionFormCtrl', [
    '$scope',
    '$rootScope',
    '$location',
    '$log',
    'collectionService',
    'imageService',
    '$http',
    '$timeout',
    'notifyService',
    'spinnerService',
    'utilsService',
    'notifyService',
    'MAX_FILES_NUMBER',
    'MAX_COLLECTIONS_NUMBER',
    function (
        $scope,
         $rootScope,
              $location,
              $log,
              collectionService,
              imageService,
              $http,
              $timeout,
              notifyService,
              spinnerService,
              utilsService,
              notifyService,
             MAX_FILES_NUMBER,
            MAX_COLLECTIONS_NUMBER
        )
    {

        if ($rootScope.collections.length >=  MAX_COLLECTIONS_NUMBER && $rootScope.$state.current.name == 'collection-create'  ){
                     var message = {}
                                  message.type = "warning";
                                  message.layout = "top";
                                  message.text = 'Maximum number Of collections ('+MAX_COLLECTIONS_NUMBER+') allowed in this demo,has been reached!' +
                                                                     '  You can always  delete collections and create some of your own!';
                                  notifyService.broadcastMessage(message);
                     $rootScope.$state.transitionTo('collections');
                 }



        //on page refresh go back to collections.
      if ($rootScope.collections.length===0 && $rootScope.$state.current.name != 'collection-create') $rootScope.$state.transitionTo('collections');

        console.log('$rootScope.collections',$rootScope.collections);
        //$rootScope.$state.transitionTo('collections')
        $log.info('collectionFormCtrl  $scope', $scope);

        //the collection to update or create
        $scope.collection = {};

        $scope.editing = ($rootScope.$state.current.name == 'collection-edit');

        //options for image collections
        $scope.options = new Array();
        angular.forEach($rootScope.images, function (value, key) {
            $scope.options.push({id: value.id, text: value.name});
        });

        $scope.filters = {};
        $scope.filters.collectionOptions = {};


        if ($scope.editing) {

            //get the edited collection based on the state parameter and copy it to collection object.
            $scope.collection = angular.copy(utilsService.findById($rootScope.collections, $rootScope.$stateParams.id));
            //find all the edited collection's images  and build  selectedOptions array to be used as options for the select2 input
            var images = utilsService.findById($rootScope.collections, $rootScope.$stateParams.id).images;
            $scope.collection.images = new Array;
            angular.forEach(images, function (value, key) {
                $scope.collection.images.push({id: value.id, text: value.name});
            });


            //select2 options used in form
            $scope.filters.collectionOptions = {
                placeholder: 'Images',
                multiple: true,
                width: '75%',
                initSelection: function (element, callback) {
                    callback($(element).data('$ngModelController').$modelValue);
                },
                query: function (query) {
                    query.callback({results: $scope.options});
                }
            };

//new collection
        } else {
            $scope.filters.collectionOptions = {
                multiple: true,
                width: '100%',
                data: $scope.options
            };
        }
        ;

        $scope.cancel = function () {
            //go to collections  detailed view.
            $rootScope.$state.transitionTo('collections')
        };
    }
]);
