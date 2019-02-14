app.config(function($routeProvider, $locationProvider, CONFIG) {

  $locationProvider.hashPrefix('');
  $routeProvider.

  when('/', {
    controller: 'HomeController',
    templateUrl: CONFIG.homeTemplate
  }).

  /* Book list page */
  when('/books/:offset/:limit', {
    controller: 'BooksController',
    templateUrl: 'views/site/books/books.html'
  }).
  /* Book table page */
  when('/books_table', {
    controller: 'BooksTableController',
    templateUrl: 'views/site/books/books_table.html'
  }).
  /* Book creation page */
  when('/books_create', {
    controller: 'BooksCreateController',
    templateUrl: 'views/site/books/books_create.html'
  }).
  /* Book update page */
  when('/books_update/:id', {
    controller: 'BooksUpdateController',
    templateUrl: 'views/site/books/books_update.html'
  }).

  /* Movie list page */
  when('/movies/:offset/:limit', {
    controller: 'MoviesController',
    templateUrl: 'views/site/movies/movies.html'
  }).
  /* Movie table page */
  when('/movies_table', {
    controller: 'MoviesTableController',
    templateUrl: 'views/site/movies/movies_table.html'
  }).
  /* Movie creation page */
  when('/movies_create', {
    controller: 'MoviesCreateController',
    templateUrl: 'views/site/movies/movies_create.html'
  }).
  /* Movie update page */
  when('/movies_update/:id', {
    controller: 'MoviesUpdateController',
    templateUrl: 'views/site/movies/movies_update.html'
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
