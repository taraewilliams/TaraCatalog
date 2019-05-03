app.config(function($routeProvider, $locationProvider, CONFIG) {

  $locationProvider.hashPrefix('');
  $routeProvider.

  when('/', {
    controller: 'HomeController',
    templateUrl: CONFIG.homeTemplate
  }).

  /* Book list page */
  when('/books/:offset/:limit', {
    controller: 'ListController',
    templateUrl: 'views/site/books/books.html'
  }).
  /* Book table page */
  when('/books_table', {
    controller: 'TableController',
    templateUrl: 'views/site/books/books_table.html'
  }).
  /* Book read list page */
  when('/books_table/read', {
    controller: 'ToDoController',
    templateUrl: 'views/site/books/books_read.html'
  }).
  /* Book read list add page */
  when('/books_table/read_add', {
    controller: 'AddController',
    templateUrl: 'views/site/books/books_add.html'
  }).
  /* Books table with order */
  when('/books_table/order_by/:order_by', {
    controller: 'TableController',
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
    controller: 'ListController',
    templateUrl: 'views/site/movies/movies.html'
  }).
  /* Movie table page */
  when('/movies_table', {
    controller: 'TableController',
    templateUrl: 'views/site/movies/movies_table.html'
  }).
  /* Movie watch list page */
  when('/movies_table/watch', {
    controller: 'ToDoController',
    templateUrl: 'views/site/movies/movies_watch.html'
  }).
  /* Movie watch list add page */
  when('/movies_table/watch_add', {
    controller: 'AddController',
    templateUrl: 'views/site/movies/movies_add.html'
  }).
  /* Movies table with order */
  when('/movies_table/order_by/:order_by', {
    controller: 'TableController',
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


  /* Games list page */
  when('/games/:offset/:limit', {
    controller: 'ListController',
    templateUrl: 'views/site/games/games.html'
  }).
  /* Games table page */
  when('/games_table', {
    controller: 'TableController',
    templateUrl: 'views/site/games/games_table.html'
  }).
  /* Games play list page */
  when('/games_table/play', {
    controller: 'ToDoController',
    templateUrl: 'views/site/games/games_play.html'
  }).
  /* Games play list add page */
  when('/games_table/play_add', {
    controller: 'AddController',
    templateUrl: 'views/site/games/games_add.html'
  }).
  /* Games table with order */
  when('/games_table/order_by/:order_by', {
    controller: 'TableController',
    templateUrl: 'views/site/games/games_table.html'
  }).
  /* Game creation page */
  when('/games_create', {
    controller: 'GamesCreateController',
    templateUrl: 'views/site/games/games_create.html'
  }).
  /* Game update page */
  when('/games_update/:id', {
    controller: 'GamesUpdateController',
    templateUrl: 'views/site/games/games_update.html'
  }).


  /* Statistics page */
  when('/stats', {
    controller: 'StatsController',
    templateUrl: 'views/site/stats.html'
  }).

  /* Statistics page */
  when('/settings', {
    controller: 'SettingsController',
    templateUrl: 'views/site/settings.html'
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
