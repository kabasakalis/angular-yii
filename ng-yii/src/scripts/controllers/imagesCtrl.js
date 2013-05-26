angular.module('app').controller('imagesCtrl', [
    '$scope',
    '$rootScope',
    '$location',
    '$log',
    'imageService',
    'collectionService',
    'notifyService', 'spinnerService',
    '$timeout',
    'utilsService',
    'YII_APP_BASE_URL',
    'COLLECTION_REST_CONTROLLER_ID',
    'X_REST_USERNAME',
    'X_REST_PASSWORD',
    'MAX_FILES_NUMBER',
    'MAX_COLLECTIONS_NUMBER',
    function ($scope,
                       $rootScope,
                       $location,
                       $log,
                       imageService,
                       collectionService,
                       notifyService,
                       spinnerService,
                       $timeout,
                       utilsService,
                       YII_APP_BASE_URL,
                       COLLECTION_REST_CONTROLLER_ID,
                       X_REST_USERNAME,
                       X_REST_PASSWORD,
                       MAX_FILES_NUMBER,
                       MAX_COLLECTIONS_NUMBER
                        )
                         {

                             $scope.MAX_FILES_NUMBER=  MAX_FILES_NUMBER;
                             $scope.MAX_COLLECTIONS_NUMBER=  MAX_COLLECTIONS_NUMBER;

                             $scope.create=(MAX_FILES_NUMBER > $scope.images.length)?'#/image-create':'#/images';


    //Images that will be shown after collection is selected
        $scope.renderedImages = [];
        $scope.$on('images', function (event, images) {
        angular.copy(images, $scope.renderedImages);
                  });


        //options for the Collection select2 input
        $scope.collectionOptions = new Array();
        //default value for the options,it will  initialize select2.
        $scope.selectedCollectionModel = {id: 'all', text: "All"};
        //make it first,default  option
        $scope.collectionOptions.push($scope.selectedCollectionModel);



        //Prepare   the  ui-select2 configuration object
        $scope.filters = {};
        $scope.filters.collectionOptions = {};


        $scope.filters.collectionOptions = {
            multiple: false,
            width: '25%',
            //placeholder:'Select Collection',
            initSelection: function (element, callback) {
                callback($(element).data('$ngModelController').$modelValue);
                //this will initialize select2 with initial value of selectedCollectionModel {id: 'all', text:"All"};
            },
            query: function (query) {
                var options=[];
                options.push({id: 'all', text: "All"});
                $.ajax({
                  url:     YII_APP_BASE_URL + 'api/' + COLLECTION_REST_CONTROLLER_ID,
                    method:"GET",
                    headers: {
                            "X_REST_USERNAME":X_REST_USERNAME,
                            "X_REST_PASSWORD":X_REST_PASSWORD
                        }
                }).done(function ( response ) {
                       var  collections=response.data.collection;
        angular.forEach(collections, function (value, key) {
           options.push({id: value.id, text: value.name});
        });
                   query.callback({results: options });
                });
            }
        };




        //watch selectedCollectionModel and set the rendered images accordingly
        $scope.$watch('selectedCollectionModel', function (newValue, oldValue) {
            if (!angular.equals(newValue.id, "all")) {     //if we select any option other than all,filter the images
                $scope.selectedCollectionModel = newValue;
                $scope.renderedImages = utilsService.findById($rootScope.collections, $scope.selectedCollectionModel.id).images;
                console.log(',$scope.renderedImages',$scope.renderedImages);
            } else   $scope.renderedImages = $rootScope.images;    //else show all images
        }, true);
    }
]);

