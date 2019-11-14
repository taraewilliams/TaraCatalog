app.config(function($routeProvider, $locationProvider, CONFIG) {

  $locationProvider.hashPrefix('');
  $routeProvider.

  /* Admin Pages */

  when('/admin', {
    controller: 'AdminHomeController',
    templateUrl: 'views/admin/home.html'
  }).
  when('/admin/images', {
    controller: 'AdminImagesController',
    templateUrl: 'views/admin/images.html'
  }).

  /* Site Pages */

  when('/', {
    controller: 'HomeController',
    templateUrl: CONFIG.homeTemplate
  }).

  when('/login', {
    controller: 'LoginController',
    templateUrl: 'views/site/login.html'
  }).
  when('/logout', {
    controller: 'LogoutController',
    templateUrl: 'views/site/logout.html'
  }).
  when('/register', {
    controller: 'RegisterController',
    templateUrl: 'views/site/register.html'
  }).


  /* Books */

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
    controller: 'CreateController',
    templateUrl: 'views/site/books/books_create.html'
  }).
  /* Book update page */
  when('/books_update/:id', {
    controller: 'UpdateController',
    templateUrl: 'views/site/books/books_update.html'
  }).


  /* Movies */

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
    controller: 'CreateController',
    templateUrl: 'views/site/movies/movies_create.html'
  }).
  /* Movie update page */
  when('/movies_update/:id', {
    controller: 'UpdateController',
    templateUrl: 'views/site/movies/movies_update.html'
  }).


  /* Games */

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
    controller: 'CreateController',
    templateUrl: 'views/site/games/games_create.html'
  }).
  /* Game update page */
  when('/games_update/:id', {
    controller: 'UpdateController',
    templateUrl: 'views/site/games/games_update.html'
  }).


  /* View other user's lists */
  when('/view/:id', {
    controller: 'ViewerController',
    templateUrl: 'views/site/viewers/viewing.html'
  }).
  /* View other user's books */
  when('/book_view/:id', {
    controller: 'ViewerController',
    templateUrl: 'views/site/viewers/viewing.html'
  }).
  /* View other user's movies */
  when('/movie_view/:id', {
    controller: 'ViewerController',
    templateUrl: 'views/site/viewers/viewing.html'
  }).
  /* View other user's games */
  when('/game_view/:id', {
    controller: 'ViewerController',
    templateUrl: 'views/site/viewers/viewing.html'
  }).
  /* Add viewers of your catalog page */
  when('/add_viewers', {
    controller: 'AddViewerController',
    templateUrl: 'views/site/viewers/add_viewer.html'
  }).
  /* Request to view catalogs page */
  when('/request_view', {
    controller: 'AddViewerController',
    templateUrl: 'views/site/viewers/add_viewer.html'
  }).
  /* See viewers of your catalog page */
  when('/viewer_list', {
    controller: 'ViewerListController',
    templateUrl: 'views/site/viewers/viewer_list.html'
  }).
  /* See catalogs you can view page */
  when('/view_list', {
    controller: 'ViewerListController',
    templateUrl: 'views/site/viewers/viewer_list.html'
  }).


  /* Profile page */
  when('/profile', {
    controller: 'ProfileController',
    templateUrl: 'views/site/users/profile.html'
  }).
  /* User update page */
  when('/user_update', {
    controller: 'UserUpdateController',
    templateUrl: 'views/site/users/user_update.html'
  }).
  /* Reset password page */
  when('/reset_password', {
    controller: 'ResetPasswordController',
    templateUrl: 'views/site/users/reset_password.html'
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
