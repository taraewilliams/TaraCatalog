app.config(function($routeProvider, $locationProvider, CONFIG) {

  $locationProvider.hashPrefix('');
  $routeProvider.

  when('/', {
    controller: 'HomeController',
    templateUrl: CONFIG.homeTemplate
  }).
  /* Book list page */
  when('/books', {
    controller: 'BooksController',
    templateUrl: 'views/site/books.html'
  }).
  /* Book creation page */
  when('/books_create', {
    controller: 'BooksCreateController',
    templateUrl: 'views/site/books_create.html'
  }).
  /* Statistics page */
  when('/stats', {
    controller: 'StatsController',
    templateUrl: 'views/site/stats.html'
  }).

  /* 404 Page */
  when('/404', {
    controller: '404Controller',
    templateUrl: 'views/site/404.html'
  }).
  otherwise({
    redirectTo: '/404'
  });
});
