app.controller('ViewerController', function($scope,
    AuthService,
    $http,
    CONFIG,
    RequestService,
    $routeParams,
    messageCenterService,
    MESSAGE_OPTIONS)
{
    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }

        $scope.creatorID = $routeParams.id;

        $http.get(CONFIG.api + CONFIG.api_routes.get_single_viewer + $scope.creatorID)
        .then(function(response) {
            $scope.viewer = response.data;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            $scope.goToPath('/profile');
        });

        if($scope.isActive('/book_view/:id')){
            $http.get(CONFIG.api + CONFIG.api_routes.get_books_viewer + $scope.creatorID)
            .then(function(response) {
                $scope.books = response.data;
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }else if ($scope.isActive('/movie_view/:id')){
            $http.get(CONFIG.api + CONFIG.api_routes.get_movies_viewer  + $scope.creatorID)
            .then(function(response) {
                $scope.movies = response.data;
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }else if ($scope.isActive('/game_view/:id')){
            $http.get(CONFIG.api + CONFIG.api_routes.get_games_viewer + $scope.creatorID)
            .then(function(response) {
                $scope.games = response.data;
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }

    }

    $scope.search = function(searchTerm){

        var search = { search: searchTerm };

        RequestService.post(CONFIG.api + CONFIG.api_routes.get_media_search_viewer + $scope.creatorID, search, function(response) {
            $scope.items = response.data;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });

    };

    $scope.user.$promise.then(init);
});
