angular.module('app').factory('utilsService', [
    function () {
//several  utility functions
        var utilsService = {};

        utilsService.findById = function (collection, id) {
            for (var i = 0; i < collection.length; i++) {
                if (collection[i].id == id) return collection[i];
            }
        }

        utilsService.removeById = function (collection, id) {
            for (var i = 0; i < collection.length; i++)
                if (collection[i].id == id) {
                    collection.splice(i, 1);
                    break;
                }
        }

        utilsService.flattenObject = function (ob) {
            var toReturn = {};
            for (var i in ob) {
                if (!ob.hasOwnProperty(i)) continue;
                if ((typeof ob[i]) == 'object') {
                    var flatObject = utilsService.flattenObject(ob[i]);
                    for (var x in flatObject) {
                        if (!flatObject.hasOwnProperty(x)) continue;
                        toReturn[i + '.' + x] = flatObject[x];
                    }
                } else {
                    toReturn[i] = ob[i];
                }
            }
            return toReturn;
        };

        //utility function for readable file sizes
        utilsService.readableFilesize = function (bytes, precision) {
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            var posttxt = 0;
            if (bytes == 0) return 'n/a';
            while (bytes >= 1024) {
                posttxt++;
                bytes = bytes / 1024;
            }
            return bytes.toFixed(precision) + " " + sizes[posttxt];
        }

        return utilsService;
    }
]);
