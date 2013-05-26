angular.module('app').controller('imageFormCtrl', [
    '$scope',
    '$rootScope',
    '$location',
    '$log',
    'imageService',
    'collectionService',
    '$http',
    '$timeout',
    'notifyService',
    'spinnerService',
    'utilsService',
    'MAX_FILE_SIZE',
    'notifyService',
      'MAX_FILES_NUMBER',
      'MAX_COLLECTIONS_NUMBER',
    function ($scope,
                      $rootScope,
                      $location,
                      $log,
                      imageService,
                      collectionService,
                      $http,
                      $timeout,
                      notifyService,
                      spinnerService,
                      utilsService,
                      MAX_FILE_SIZE,
                      notifyService,
                     MAX_FILES_NUMBER,
                     MAX_COLLECTIONS_NUMBER
        )
    {

        if ($rootScope.images.length >=  MAX_FILES_NUMBER && $rootScope.$state.current.name == 'image-create' ){
              var message = {}
                           message.type = "warning";
                           message.layout = "top";
                           message.text = 'Maximum number Of images ('+MAX_FILES_NUMBER+' ) allowed in this demo,exceeded!' +
                                                              'Feel free to delete images and upload your own!';
                           notifyService.broadcastMessage(message);
              $rootScope.$state.transitionTo('images');
          }


        $log.info('imageFormCtrl  $scope', $scope);

        //the image to update or create
        $scope.image = {};


        $scope.maxFilesize = MAX_FILE_SIZE;
        $scope.fileSelected = '';
        $scope.isFileSelected = false;
        $scope.filesize = 0;
        $scope.clicked = false;
        $scope.readableFilesize = utilsService.readableFilesize;

        //flag to distinguish  editing from creating new image
        $scope.editing = ($rootScope.$state.current.name == 'image-edit');

        //options for image collections
        $scope.options = new Array();
        angular.forEach($rootScope.collections, function (value, key) {
            $scope.options.push({id: value.id, text: value.name});
        });

        $scope.filters = {};
        $scope.filters.collectionOptions = {};


        if ($scope.editing) {

           // $log.info('imageFormCtrl  IMAGES', $rootScope.images);

            //get the edited image based on the state parameter and copy it to image object.
            $scope.image = angular.copy(utilsService.findById($rootScope.images, $rootScope.$stateParams.id));
            var collections = utilsService.findById($rootScope.images, $rootScope.$stateParams.id).collections;

            //$log.info('imageFormCtrl  collections', collections);

            //find all the edited image's collections and build  selectedOptions array to be used as options for the select2 input
            $scope.image.selectedOptions = new Array;
            angular.forEach(collections, function (value, key) {
                $scope.image.selectedOptions.push({id: value.id, text: value.name});
            });


            //select2 options used in form.They will pre-select the edited image's collections.
            $scope.filters.collectionOptions = {
               // placeholder: 'Collections',
                multiple: true,
                width: '100%',
                initSelection: function (element, callback) {
                    callback($(element).data('$ngModelController').$modelValue);
                },
                query: function (query) {
                    query.callback({results: $scope.options});
                }
            };
//new image
        }
        else {
            $scope.filters.collectionOptions = {
                multiple: true,
                width: '75%',

                data: $scope.options
            };
        }

        //keep track of file selection
        $scope.onImageselected = function (name, size) {
            $scope.$apply(
                function () {
                    $scope.fileSelected = name;
                    $scope.isFileSelected = !angular.equals($scope.fileSelected, '');
                    $scope.filesize = size;
                }
            );
        }

        $scope.cancel = function () {
            //go to image detailed view.
            $rootScope.$state.transitionTo('image', $rootScope.$stateParams.id)
        };

    }
]);
