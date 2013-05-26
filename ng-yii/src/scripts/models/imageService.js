angular.module('app').factory('imageService', [
    '$resource',
    '$http',
    '$log',
    '$rootScope',
    'spinnerService',
    'notifyService',
    'utilsService',
    'collectionService',
    '$timeout',
    'YII_APP_BASE_URL',
    'IMAGE_REST_CONTROLLER_ID',
    'X_REST_PASSWORD',
    'X_REST_USERNAME',
    'IMAGES_FOLDER_PATH',
    function ($resource,
                         $http,
                          $log,
                          $rootScope,
                          spinnerService,
                          notifyService,
                          utilsService,
                          collectionService,
                          $timeout,
                          YII_APP_BASE_URL,
                          IMAGE_REST_CONTROLLER_ID,
                          X_REST_PASSWORD,
                          X_REST_USERNAME,
                          IMAGES_FOLDER_PATH
        ) {

        var Image_DS;
        //http://docs.angularjs.org/api/ngResource.$resource
        Image_DS = $resource(
            YII_APP_BASE_URL + 'api/' + IMAGE_REST_CONTROLLER_ID + '/:id',
            {id: '@id'}, //default parameters
            {
                'save': {method: 'POST'},
                'query': {method: 'GET', isArray: true},
                'remove': {method: 'DELETE'},
                'update': {method: 'PUT' },
                'delete': {method: 'DELETE'}
            }
        );

        var imageService = {};

        imageService.getAll = function () {
            //set a spinner (fetching images and collections on start up)
            spinnerService.broadcastSpinner({color: '#00ff00'});
            Image_DS.query(
                {},
                function (response) {
                    //remove spinner when we get the response
                    spinnerService.broadcastSpinner(null);
                    /*$timeout(function(){      spinnerService.broadcastSpinner({color: '#00ff00'});
                     },3000);*/

                    $rootScope.$broadcast('images', response[0]['image']);
                    $rootScope.images = response[0]['image'];
              //      $rootScope.renderedImages = response[0]['image'];
                    $log.info('Images loaded', $rootScope.images);
                },
                function (error_response) {
                    //remove spinner when we get the response
                    spinnerService.broadcastSpinner(null);
                    var message = {}
                    message.type = "error";
                    message.layout = "top";
                    message.text = 'Error!Status: ' + error_response.errorCode + ' .Message from server: ' + error_response.message;
                    notifyService.broadcastMessage(message);
                    //$log.info('error_response',error_response);
                }
            );
        };


//Generic getAll
        imageService.get_All = function (onSuccess, onError) {
            Image_DS.query(
                {},
                function (response, getResponseHeaders) {
                    return onSuccess(response[0]);
                },
                function (response, getResponseHeaders) {
                    return onError(response.data);
                }
            );
        };

        //Generic getById
        imageService.get_ById = function (id, onSuccess, onError) {
            return Image_DS.get(
                { id: id},
                function (response, getResponseHeaders) {
                    return onSuccess(response.data);
                },
                function (response, getResponseHeaders) {
                    return onError(response.data);
                }
            );
        };



        imageService.delete = function (id) {
            //start loading spinner as the request fires
            //See  spin.js at http://fgnass.github.io/spin.js/#!  on how to  customize the configuration object
            spinnerService.broadcastSpinner({color: '#ff0000'});

            imageService.delete_ById(id, function (response) {
                    //stop the spinner.Use timeout locally to see if spinners actually starts and stops as expected.
                  /*  $timeout(function () {
                        spinnerService.broadcastSpinner(null);
                    }, 2000);*/
                      spinnerService.broadcastSpinner(null);

                    //image was deleted on server,so remove it from client also
                    utilsService.removeById($rootScope.images, id);

                    //Prepare and send notification message,see  noty http://needim.github.io/noty/ for options.
                    var message = {}
                    message.type = "information";
                    message.layout = "top";
                    message.text = response.message;
                    notifyService.broadcastMessage(message);

                    //go back to details view.
                    $rootScope.$state.transitionTo('images');
                    $log.info('response', response);
                },
                function (error_response) {
                    //stop the spinner.Use timeout locally to see if spinners actually starts and stops as expected.
                   /* $timeout(function () {
                        spinnerService.broadcastSpinner(null);
                    }, 2000);*/
                   spinnerService.broadcastSpinner(null);

                    //since delete failed, restore the original image.
                  ///  angular.copy($scope.original_image, $scope.image);

                         //Prepare and send notification message,see  noty http://needim.github.io/noty/ for options.
                    var message = {}
                                       message.type = "error";
                                       message.layout = "top";
                                       message.text = error_response.message;
                                       notifyService.broadcastMessage(message);

                    //and go back to images view
                    $rootScope.$state.transitionTo('images');
                    $log.info('error_response', error_response);

                }
            )
        };


        //Generic deleteById
        imageService.delete_ById = function (id, onSuccess, onError) {
            return Image_DS.delete(
                { id: id},
                function (response, getResponseHeaders) {
                    return onSuccess(response.data);
                },
                function (response, getResponseHeaders) {
                    return onError(response.data);
                }
            );
        };

    //Generic create
        imageService._create = function (newImageObj, onSuccess, onError) {
            return Image_DS.save(
                {},
                newImageObj,
                function (response, getResponseHeaders) {
                    console.log('response from create new', response);
                    return onSuccess(response);

                },
                function (response, getResponseHeaders) {
                    return onError(response);
                }
            );
        };

    //Generic create
        imageService._update = function (image, onSuccess, onError) {
            return Image_DS.update(
                {  },
                image,
                function (response, getResponseHeaders) {
                    return onSuccess(response);
                },
                function (response, getResponseHeaders) {
                    return onError(response);
                }
            );
        };

    //This service function  both creates and updates an image.It skips ngResource and  uses  $http
    //  service and FormData to send text data and the image itself in a single  ajax POST request-(with an id if image
   // is updated,no id if image is created).The id is in a  hidden input field in the form.
   //This means we are violating the REST specification :we should do a PUT request for updating.Unfortunately it's tricky to parse  multipart-form data
   //PUT request on the server  (need to "weed  out"  the data based on  header boundaries etc).
        imageService.createOrUpdate = function () {
            //start loading spinner as the request fires
            //See  spin.js at http://fgnass.github.io/spin.js/#!  on how to  customize the configuration object
            spinnerService.broadcastSpinner({color: '#00ff00'});
            var form = $('#cform');
            var formdata = false;
            if (window.FormData) {
                formdata = new FormData(form[0]);
            }
            var method = 'POST';
            var formAction = YII_APP_BASE_URL + 'api/' + IMAGE_REST_CONTROLLER_ID;

            $http({
                data: formdata,
                url: formAction,
                method: method,
                headers: { 'Content-Type': false,
                    'X_REST_PASSWORD':X_REST_PASSWORD,
                    'X_REST_USERNAME': X_REST_USERNAME },
                transformRequest: function (data) {
                    return data;
                }
            }).success(function (jsonresponse, status, headers, config) {

                    var response = angular.fromJson(jsonresponse);
                    //stop the spinner.Use timeout locally to see if spinners actually starts and stops as expected.
                   spinnerService.broadcastSpinner(null);
                /*    $timeout(function () {
                        spinnerService.broadcastSpinner(null);
                    }, 2000);*/
                    //array of collection ids for newly created or updated image
                    var collection_ids = response.data.collections;
                    var image = response.data.image;
                    image.collections = new Array;

                    //find all collection objects for this image and assign them as an array image.collections
                    angular.forEach(collection_ids, function (collection_id, index) {
                        var collection = utilsService.findById($rootScope.collections, collection_id);
                        image.collections.push(collection);
                    });

                    //Prepare notification message see  noty http://needim.github.io/noty/ for options.
                    var message = {}
                    message.type = "success";
                    message.layout = "top";

                    //If a new image is created,push image to  $rootScope.images  array.
                    if (!($rootScope.$state.current.name == 'image-edit')) {
                        $rootScope.images.push(response.data.image);
                        message.text = "New Image " + response.data.image.name + " created succesfully.";
                    }
                    //if we are updating,replace the old image with the new edited copy.
                    else {
                        angular.copy(image, utilsService.findById($rootScope.images, response.data.image.id));
                        message.text = "Image " + response.data.image.name + " updated succesfully.";
                    }

                    //Refresh
                    $rootScope.Image.getAll();
                    $rootScope.Collection.getAll();

                    //send notification message to end user.
                    notifyService.broadcastMessage(message);

                    //go to image detailed view.
                    $rootScope.$state.transitionTo('image', {id: response.data.image.id})

                    /*    $log.info('response', response);
                     $log.info('status', status);
                     $log.info('headers', headers);
                     $log.info('config', config);*/
                    //refresh Collections so that they pick up related images changes


                }).
                error(function (jsonresponse, status, headers, config) {
                    var response = angular.fromJson(jsonresponse);
                    spinnerService.broadcastSpinner(null);
                  /*  $timeout(function () {
                        spinnerService.broadcastSpinner(null);
                    }, 2000);*/

                    var Errors = utilsService.flattenObject(response.message);
                    var errorsmessage = '';
                    for (var i  in Errors) {
                        errorsmessage = errorsmessage + Errors[i]
                    }
                    var message = {}
                    message.type = "error";
                    message.layout = "top";
                    message.text = 'Error!Status: ' + status + ' .Message from server: ' + errorsmessage;
                    notifyService.broadcastMessage(message);

                    // called asynchronously if an error occurs
                    // or server returns response with an error status.

                    $log.info('data', response);
                    $log.info('status', status);
                    $log.info('headers', headers);
                    $log.info('config', config);

                    //$rootScope.$state.transitionTo('image',$rootScope.$stateParams.id);
                    $rootScope.$state.transitionTo('images');
                });
        };


        imageService.getThumbImageURL = function (image) {
            if (image.img_name != null)
                return    YII_APP_BASE_URL + IMAGES_FOLDER_PATH + 'thumbnails/' + image.img_name + '_thumb_' + image.id + '.' + image.img_ext;
            else
                return 'http://placehold.it/100&text=No+Image.jpg';
        };

        imageService.getImageURL = function (image) {
            if (image.img_name != null)
                return      YII_APP_BASE_URL + IMAGES_FOLDER_PATH + image.img_name + '_full_' + image.id + '.' + image.img_ext;
            else
                return 'http://placehold.it/200&text=No+Image.jpg';
        };

        return imageService;
    }
]);



