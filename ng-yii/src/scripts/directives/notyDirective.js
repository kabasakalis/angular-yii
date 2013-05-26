             angular.module('app').directive('notyMsg', [
              '$log','$rootScope', function($log,$rootScope) {
                    return {
                    restrict: 'A',
                        scope:{notyoptions:"="},
                    link: function(scope, element, attrs) {

                        var opts_default=  {
                                             layout: 'top',
                                             theme: 'defaultTheme',
                                             type: 'alert',
                                             text: '',
                                             dismissQueue: true, // If you want to use queue feature set this true
                                             template: '<div class="noty_message"><span class="noty_text"></span><div class="noty_close"></div></div>',
                                             animation: {
                                               open: {height: 'toggle'},
                                               close: {height: 'toggle'},
                                               easing: 'swing',
                                               speed: 500 // opening & closing animation speed
                                             },
                                             timeout: false, // delay for closing event. Set false for sticky notifications
                                             force: false, // adds notification to the beginning of queue when set to true
                                             modal: false,
                                             closeWith: ['click'], // ['click', 'button', 'hover']
                                             callback: {
                                               onShow: function() {},
                                               afterShow: function() {},
                                               onClose: function() {},
                                               afterClose: function() {}
                                             },
                                             buttons: false
                                                  };

                        scope.$watch('notyoptions',function(val){
                            $.noty.closeAll()
                            /*console.log('new options',val);*/
                            var   newopts=     angular.extend(opts_default,  angular.fromJson(val));
                            if(newopts.text)      var new_noty_Object = angular.element(element).noty(newopts);

                        })
                      var   opts=     angular.extend(opts_default,  angular.fromJson( scope.options));
                     if(opts.text)  var notyObject = angular.element(element).noty(opts);

                    }
                    }
                }
            ]);
