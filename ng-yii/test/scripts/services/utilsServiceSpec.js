beforeEach(function () {
    //module('app')
/*module('app', [
        'ngResource',
       'ui.compat',
        'ui',
        'ui.bootstrap',
        'templates-main'
    ]);*/



   module('app')
    return this.addMatchers({
        toEqualData: function (expected) {
            return angular.equals(this.actual, expected);
        }
    });
});

beforeEach(inject(function ($injector) {
    return utilsService = $injector.get('utilsService');
}));


describe('utilsService.', function () {


    it('readableFilesize should convert file sizes from  bytes to  a  readable string.For example,Convert 135*1024*1024 to 135 MB ', function () {
        expect(utilsService.readableFilesize(135 * 1024 * 1024)).toEqualData('135 MB');
    });


    it('flattenObject should Flatten Objects ', function () {
        var unflattenedObj =
        {
            name: 'Sunset in Hawaii',
            filesize: '500KB',
            collections: [
                {
                    name: 'Vacation'
                },
                {
                    name: 'Family',
                    members: [
                        {
                            name: 'me'
                        },
                        {
                            name: 'my Mom'
                        }
                    ]
                }
            ]
        }
        var flattened =
        { name: 'Sunset in Hawaii', filesize: '500KB', 'collections.0.name': 'Vacation', 'collections.1.name': 'Family', 'collections.1.members.0.name': 'me', 'collections.1.members.1.name': 'my Mom' }
        expect(utilsService.flattenObject(unflattenedObj)).toEqualData(flattened);
    });


    it('findById should return an object from an array based on id value', function () {

        var itemA = {
            id: 1, name: 'Sunset in Hawaii', filesize: '500KB'
        }
        var itemB = {
            id: 2, name: 'Mountains of Chile', filesize: '124KB'
        }

        var itemC = {
            id: 3, name: 'Valley Of Death', filesize: '300KB'
        }
        var objects = [itemA, itemB, itemC];
        expect(utilsService.findById(objects, 3)).toEqualData(itemC);
    });


    it('removeById should remove  an object from an array based on id value', function () {
        var itemA = {
            id: 1, name: 'Sunset in Hawaii', filesize: '500KB'
        }
        var itemB = {
            id: 2, name: 'Mountains of Chile', filesize: '124KB'
        }
        var itemC = {
            id: 3, name: 'Valley Of Death', filesize: '300KB'
        }
        var objects = [itemA, itemB, itemC];
        utilsService.removeById(objects, 3);
        expect(objects).toEqualData([itemA, itemB]);
    });

});




