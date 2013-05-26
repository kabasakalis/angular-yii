angular.module('app').directive('executeClickOnce', [
  '$log', function($log) {
      return{
          restrict: 'A',
                             link: function(scope, element, attrs) {
                                         $(element).one("click", function() {
                                             scope.$apply(attrs.executeClickOnce);
                                         });
                                       }
      }
  }
]);
