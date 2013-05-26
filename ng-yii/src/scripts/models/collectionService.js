angular.module('app').factory('collectionService', [
    '$resource',
    '$log',
    '$rootScope',
    'spinnerService',
    'notifyService',
    'utilsService',
    '$timeout',
    '$http',
    'YII_APP_BASE_URL',
    'COLLECTION_REST_CONTROLLER_ID',
    'X_REST_USERNAME',
    'X_REST_PASSWORD',
    function ($resource,
                            $log,
                             $rootScope,
                             spinnerService,
                             notifyService,
                             utilsService,
                             $timeout,
                             $http,
                             YII_APP_BASE_URL,
                             COLLECTION_REST_CONTROLLER_ID,
                             X_REST_USERNAME,
                             X_REST_PASSWORD
                            ) {


        var Collection_DS;
          //custom authorization headers are also set  by default in scripts/config/http.config.js,but are ignored by $httpBackend used in tests.
          //That's the reason for setting them here also.
        var cutomAuthHeaders={
            "X_REST_USERNAME": X_REST_USERNAME,
           "X_REST_PASSWORD": X_REST_PASSWORD
        }

        //http://docs.angularjs.org/api/ngResource.$resource
        Collection_DS = $resource(
            YII_APP_BASE_URL + 'api/' + COLLECTION_REST_CONTROLLER_ID + '/:id',
            {id: '@id'}, //default parameters
            {
                'get':    {method:'GET', headers: cutomAuthHeaders},
                'save': {method: 'POST', headers: cutomAuthHeaders},
                'query': {method: 'GET',
                    isArray: true,
                    headers:cutomAuthHeaders
                },
                'remove': {method: 'DELETE',
                    headers: cutomAuthHeaders
                },
                'update': {method: 'PUT',
                    headers:cutomAuthHeaders},
                'delete': {method: 'DELETE', headers: cutomAuthHeaders}
            }
        );

        var collectionService = {}

        //get all  Collections
        collectionService.getAll = function () {

           // console.log('getAll FIRED');
            Collection_DS.query(
                {},
                function (response) {
                    $rootScope.collections = response[0]['collection'];
                    $rootScope.$broadcast('collections', response[0]['collection']);
                    $log.info('Collections loaded', $rootScope.collections);
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
        }


        //Generic get_All definition to override,this is not used in the app.
        collectionService.get_All = function (onSuccess, onError) {
            Collection_DS.query(
                {},
                function (response, getResponseHeaders) {
                    return onSuccess(response);
                },
                function (response, getResponseHeaders) {
                    return onError(response.data);
                }
            );
        };


        collectionService.getById = function (id, onSuccess, onError) {
            Collection_DS.get(
                { id: id},
                function (response, getResponseHeaders) {
                    return onSuccess(response.data);
                },
                function (response, getResponseHeaders) {
                    return onError(response.data);
                }
            );
        };


        collectionService.delete = function (id) {
            //start loading spinner as the request fires
            //See  spin.js at http://fgnass.github.io/spin.js/#!  on how to  customize the configuration object
            spinnerService.broadcastSpinner({color: '#00ff00'});
            Collection_DS.delete(
                { id: id},
                function (response) {
                    //stop the spinner.Use timeout locally to see if spinners actually starts and stops as expected.
                    /*    $timeout(function () {
                     spinnerService.broadcastSpinner(null);
                     }, 2000);*/
                    spinnerService.broadcastSpinner(null);
                    //image was deleted on server,so remove it from client also
                    utilsService.removeById($rootScope.collections, id);

                    //Prepare and send notification message,see  noty http://needim.github.io/noty/ for options.
                    var message = {}
                    message.type = "information";
                    message.layout = "top";
                    message.text = response.data.message;
                    notifyService.broadcastMessage(message);

                    //go back to details view.
                    $rootScope.$state.transitionTo('collections');
                    $log.info('response', response);
                },
                function (error_response) {
                    //stop the spinner.Use timeout locally to see if spinners actually starts and stops as expected.
                    /*  $timeout(function () {
                     spinnerService.broadcastSpinner(null);
                     }, 2000);*/
                    spinnerService.broadcastSpinner(null);

                    //since delete failed, restore the original image.
                    //  angular.copy($scope.original_collection, $scope.collection);

                    //Prepare and send notification message,see  noty http://needim.github.io/noty/ for options.
                    var message = {}
                    message.type = "error";
                    message.layout = "top";
                    message.text = error_response.data.message;
                    notifyService.broadcastMessage(message);

                    //and go back to collections view
                    $rootScope.$state.transitionTo('collections');
                    $log.info('error_response', error_response);
                }
            )
        };


//generic delete function
        collectionService.deleteBy_Id = function (id, onSuccess, onError) {
            Collection_DS.delete(
                { id: id},
                function (response, getResponseHeaders) {
                    return onSuccess(response);
                },
                function (response, getResponseHeaders) {
                    return onError(response);
                }
            );
        };


//Success Callback function for Creating Or Updating a Collection
        collectionService.onCreateUpdateSuccess = function (jsonresponse, status, headers, config) {
            var response = angular.fromJson(jsonresponse);
            //stop the spinner.Use timeout locally to see if spinners actually starts and stops as expected.
            spinnerService.broadcastSpinner(null);
            /*   $timeout(function () {
             spinnerService.broadcastSpinner(null);
             }, 2000);*/

            //array of image ids for newly created or updated collections
            var image_ids = response.data.images;

            var collection = response.data.collection;
            collection.images = new Array;

            //find all image  objects for this collection  and assign them as an array collection.images
        /*    angular.forEach(image_ids, function (image_id, index) {
                var image = utilsService.findById($rootScope.images, image_id.id);
                collection.images.push(image);
            });*/

            //get Images (we refresh so that images pick up the changes of their related Collections)
            $rootScope.Image.getAll();
            //refresh Collections
            $rootScope.Collection.getAll();

            //Prepare notification message see  noty http://needim.github.io/noty/ for options.
            var message = {}
            message.type = "success";
            message.layout = "top";

            if (!($rootScope.$state.current.name == 'collection-edit')) {
                //If a new collection is created,push collection to $scope.collections  array.
            /*    newCollection = response.data.collection;
                newCollection.images = response.data.images;
                $rootScope.collections.push(newCollection);*/
                message.text = "New Collection  " + response.data.collection.name + " created succesfully.";
                //go to collection detailed view.
                $rootScope.$state.transitionTo('collections')
            }

            else {
                //if we are updating,replace the old image with the new edited copy.
           /*     angular.copy(collection, utilsService.findById($rootScope.collections, response.data.collection.id));*/
                message.text = "Collection " + response.data.collection.name + " updated succesfully.";
                $rootScope.$state.transitionTo('collections', {id: response.data.collection.id})
            }

            //send notification message to end user.
            notifyService.broadcastMessage(message);
            $log.info('response', response);
            $log.info('status', status);
            $log.info('headers', headers);
            $log.info('config', config);
        };

//Error Callback function for Creating Or Updating a Collection
        collectionService.onCreateUpdateError = function (jsonresponse, status, headers, config) {
            var response = angular.fromJson(jsonresponse);
            spinnerService.broadcastSpinner(null);
            /*    $timeout(function () {
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

            $log.info('response', response);
            $log.info('status', status);
            $log.info('headers', headers);
            $log.info('config', config);

            $rootScope.$state.transitionTo('collections');

        }


        collectionService.create = function (newCollectionObj) {
            //start loading spinner as the request fires
            //See  spin.js at http://fgnass.github.io/spin.js/#!  on how to  customize the configuration object
            spinnerService.broadcastSpinner({color: '#00ff00'});
            Collection_DS.save(
                {},
                newCollectionObj,
                collectionService.onCreateUpdateSuccess,
                collectionService.onCreateUpdateError
            )
        };


        collectionService.update = function (updatedCollectionObj) {
            //start loading spinner as the request fires
            //See  spin.js at http://fgnass.github.io/spin.js/#!  on how to  customize the configuration object
            spinnerService.broadcastSpinner({color: '#0000ff'});
            Collection_DS.update(
                updatedCollectionObj,
                collectionService.onCreateUpdateSuccess,
                collectionService.onCreateUpdateError
            )
        };


//generic
        collectionService._create = function (newCollectionObj, onSuccess, onError) {
            Collection_DS.save(
                {},
                newCollectionObj,
                function (response, getResponseHeaders) {
                    return onSuccess(response);

                },
                function (response, getResponseHeaders) {
                    return onError(response);
                }
            );
        };

//generic
        collectionService._update = function (collection, onSuccess, onError) {
            Collection_DS.update(
                {
                    //   id: product.id    //id will be extracted from default parameter @id
                },
                collection,
                function (response, getResponseHeaders) {
                    return onSuccess(response);
                },
                function (response, getResponseHeaders) {
                    return onError(response);
                }
            );
        };

        return collectionService;

    }
]);



