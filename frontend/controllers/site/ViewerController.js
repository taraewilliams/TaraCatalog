app.controller('ViewerController', function($scope, AuthService, Session, $http, CONFIG, RequestService, $routeParams)
{
    /* Redirect if not logged in */
    if( AuthService.redirectOnUnauthorized() ) {
        return;
    }

    function init(){

        var creatorID = $routeParams.id;

        $http.get(CONFIG.api + '/viewers/' + creatorID)
        .then(function(response) {
            $scope.viewer = response.data;
            console.log($scope.viewer);
        });

        $http.get(CONFIG.api + '/book_viewers/' + creatorID)
        .then(function(response) {
            $scope.books = response.data;
        });

        $http.get(CONFIG.api + '/movie_viewers/' + creatorID)
        .then(function(response) {
            $scope.movies = response.data;
        });

        $http.get(CONFIG.api + '/game_viewers/' + creatorID)
        .then(function(response) {
            $scope.games = response.data;
        });

    }

    init();
});
