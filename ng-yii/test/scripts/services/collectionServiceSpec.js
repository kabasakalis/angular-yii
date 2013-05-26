//test data
var imageOne = {
    id: 1,
    name: "imageOne",
    description: "imageOne_Description",
}
var imageTwo = {
    id: 2,
    name: "imageTwo",
    description: "imageTwo_Description",
}
var collectionOne = {
    id: 1,
    name: "collectionOne",
    description: "collectionOne_Description",
    images: [imageOne, imageTwo]
}

var collectionTwo = {
    id: 2,
    name: "collectionTwo",
    description: "collectionTwo_Description",
    images: [imageOne]
}
var newCollection = {
    name: "collectionThree",
    description: "collectionThree_Description",
    images: [imageTwo]
}

var collections = [collectionOne, collectionTwo];

beforeEach(function () {
    //module('app');
     this.addMatchers({
        toEqualData: function (expected) {
            return angular.equals(this.actual, expected);
        }
    });
});

afterEach(inject(function (_$httpBackend_) {
    $httpBackEndService = _$httpBackend_;
    $httpBackEndService.verifyNoOutstandingExpectation();
    $httpBackEndService.verifyNoOutstandingRequest();
}));

describe('collectionService', function () {
    var $httpBackEndService, collectionService;

    beforeEach(inject(function (_$httpBackend_, $injector) {
        $httpBackEndService = _$httpBackend_;
        collectionService = $injector.get('collectionService');
        YII_APP_BASE_URL = $injector.get('YII_APP_BASE_URL');
        COLLECTION_REST_CONTROLLER_ID = $injector.get('COLLECTION_REST_CONTROLLER_ID');
        X_REST_USERNAME = $injector.get('X_REST_USERNAME');
        X_REST_PASSWORD = $injector.get('X_REST_PASSWORD');
    }));


     it('should query for all  collections  at GET  /api/collectionrest  and receive an array of two collections', function () {
        var failure, success;

        success = function (result, headers) {
            //console.log('getAll Success result', result);
          expect(result[0].collections).toEqualData(collections);

        };
        failure = function (result, headers) {
          //  console.log('getAll Failure result', result);
            expect(true).toBe(false);
        };
         var url = YII_APP_BASE_URL + 'api/' + COLLECTION_REST_CONTROLLER_ID;
              $httpBackEndService.expect('GET', url,undefined,function(headers) {
                  // check if the header was send, if it wasn't the expectation won't
                  // match the request and the test will fail
                  return headers['X_REST_USERNAME'] ==X_REST_USERNAME && headers['X_REST_PASSWORD'] ==X_REST_PASSWORD}).
                  respond({'data': {'collections': collections}});
        collectionService.get_All(success, failure);
        return $httpBackEndService.flush();
    });

          it('should query  collection of id 1  at  GET /api/collectionrest/1  and receive collectionOne as response', function () {
            var failure, success;
            success = function (result, headers) {
                //console.log('getById  Success result', result);
              expect(result.collections).toEqualData(collectionOne);

            };
            failure = function (result, headers) {
               // console.log('getById failure result', result);
                expect(true).toBe(false);
            };
              var url = YII_APP_BASE_URL + 'api/' + COLLECTION_REST_CONTROLLER_ID+'/1';
              $httpBackEndService.expect('GET', url,undefined,function(headers) {
                            // check if the header was send, if it wasn't the expectation won't
                            // match the request and the test will fail
                            return headers['X_REST_USERNAME'] ==X_REST_USERNAME && headers['X_REST_PASSWORD'] ==X_REST_PASSWORD}).
                            respond({'data': {'collections': collectionOne}});
            collectionService.getById(1,success, failure);
            return $httpBackEndService.flush();
        });

    it('should  delete  collectionTwo  of id 2  at  DELETE /api/collectionrest/2  and receive {id:2} as response', function () {
         var failure, success;

         success = function (result, headers) {
             //console.log('deleteBy_Id  Success result', result);
           expect(result.data).toEqualData({id:2});

         };
         failure = function (result, headers) {
             //console.log('deleteBy_Id failure result', result);
             expect(true).toBe(false);
         };
           var url = YII_APP_BASE_URL + 'api/' + COLLECTION_REST_CONTROLLER_ID+'/2';

           $httpBackEndService.expect('DELETE', url,undefined,function(headers) {
                         // check if the header was send, if it wasn't the expectation won't
                         // match the request and the test will fail

                         return headers['X_REST_USERNAME'] ==X_REST_USERNAME && headers['X_REST_PASSWORD'] ==X_REST_PASSWORD}).
                         respond({data: {id: collectionTwo.id}});
         collectionService.deleteBy_Id(2,success, failure);
         return $httpBackEndService.flush();
     });


    it('should  save  a newCollection   at    POST   /api/collectionrest   and receive the new id 3  as response', function () {
         var failure, success;
         success = function (result, headers) {
          //   console.log('_create   Success result', result);
           expect(result.data).toEqualData({id:'3'});

         };
         failure = function (result, headers) {
        //     console.log('_create  Failure result', result);
             expect(true).toBe(false);
         };
        var url = YII_APP_BASE_URL + 'api/' + COLLECTION_REST_CONTROLLER_ID;
           $httpBackEndService.expect('POST', url,newCollection,function(headers) {
                         // check if the header was send, if it wasn't the expectation won't
                         // match the request and the test will fail

                         return headers['X_REST_USERNAME'] ==X_REST_USERNAME && headers['X_REST_PASSWORD'] ==X_REST_PASSWORD}).
                         respond({data:{id:'3'}});
         collectionService._create(newCollection,success, failure);
         return $httpBackEndService.flush();
     });
});
