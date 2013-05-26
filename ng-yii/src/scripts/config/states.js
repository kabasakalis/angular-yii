angular.module('app')
    .config(
        [ '$stateProvider', '$routeProvider', '$urlRouterProvider',
            function ($stateProvider, $routeProvider, $urlRouterProvider) {

                $routeProvider
                        .when('', {
                          redirectTo: '/'
                        });

                $stateProvider
                    .state('root', {
                        url: '/',
                        views: {
                            'intro': {
                                templateUrl: 'views/intro.html'
                            }
                        }
                    })
                    .state('gallery', {
                        url: '/gallery',
                        views: {
                            'gallery': {
                                templateUrl: 'views/gallery.html',
                                controller: 'imagesCtrl'
                            }
                        }
                    })
                    .state('images', {
                        url: '/images',
                        views: {
                            'images': {
                                templateUrl: 'views/images.html',
                                controller: 'imagesCtrl'
                            }
                        }
                    })
                    .state('collections', {
                        url: '/collections',
                        views: {
                            'collections': {
                                templateUrl: 'views/collections.html'
                            }
                        }
                    })
                 .state('image', {
                        url: '/image/:id',
                        views: {
                            'image': {
                                templateUrl: 'views/image.html',
                                controller: 'imageCtrl'
                            }
                        }
                    })
                    .state('image-create', {
                        // resolve: { r: '$rootScope' },
                        url: '/image-create',
                        views: {
                            'image-edit': {
                                templateUrl: 'views/image-form.html',
                                controller: 'imageFormCtrl'
                            }
                        }
                    })
                    .state('image-edit', {
                        url: '/image-edit/:id',
                        views: {

                            'image-edit': {
                                templateUrl: 'views/image-form.html',
                                controller: 'imageFormCtrl'
                            }
                        }
                    })
                    .state('collection-edit', {
                        url: '/collection-edit/:id',
                        views: {
                            'collection-edit': {
                                templateUrl: 'views/collection-form.html',
                                controller: 'collectionFormCtrl'
                            }
                        }
                    })
                    .state('collection-create', {
                        url: '/collection-create',
                        views: {
                            'collection-edit': {
                                templateUrl: 'views/collection-form.html',
                                controller: 'collectionFormCtrl'
                            }
                        }
                    });
            }]);

