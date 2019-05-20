app.controller('ViewerController', function($scope, AuthService, Session, $http, CONFIG, RequestService, $routeParams)
{
    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }

        $scope.creatorID = $routeParams.id;

        $http.get(CONFIG.api + '/viewers/' + $scope.creatorID)
        .then(function(response) {
            $scope.viewer = response.data;
        }, function(response){
            console.log(response.data.message);
            $scope.goToPath('/profile');
        });

        if($scope.isActive('/book_view/:id')){
            $http.get(CONFIG.api + '/book_viewers/' + $scope.creatorID)
            .then(function(response) {
                $scope.books = response.data;
            });
        }else if ($scope.isActive('/movie_view/:id')){
            $http.get(CONFIG.api + '/movie_viewers/' + $scope.creatorID)
            .then(function(response) {
                $scope.movies = response.data;
            });
        }else if ($scope.isActive('/game_view/:id')){
            $http.get(CONFIG.api + '/game_viewers/' + $scope.creatorID)
            .then(function(response) {
                $scope.games = response.data;
            });
        }

    }

    $scope.search = function(searchTerm){

        var search = { search: searchTerm };

        RequestService.post(CONFIG.api + "/search/" + $scope.creatorID, search, function(response) {
            $scope.items = response.data;
        }, function(response){
            console.log(response.data.message);
        });

    };

    $scope.user.$promise.then(init);
});
