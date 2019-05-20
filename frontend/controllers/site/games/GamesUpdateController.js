app.controller('GamesUpdateController', function($scope, $routeParams, CONFIG, RequestService, $http, AuthService)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        $http.get(CONFIG.api + '/games/' + $routeParams.id)
        .then(function(response) {
            $scope.game = response.data;
            $scope.game_clone = $scope.clone($scope.game);
        }, function(error){
            console.log("Error");
        });

        $http.get(CONFIG.api + '/games/platforms/all')
        .then(function(response) {
            $scope.platforms = response.data;
        }, function(error){
            console.log("Error");
        });

    }

    $scope.updateGame = function(game_clone){

        var new_game = removeNotUpdatedFields(game_clone, $scope.game);
        var url = CONFIG.api + '/games/' + $scope.game.id;

        RequestService.post(url, new_game, function(data) {
            alert("Game was updated.");
        }, function(error, status){
            console.log(error.message);
        });

    };

    $scope.hasChanged = function(image){
        return ($scope.game.image != image);
    }

    //* Private functions *//

    var removeNotUpdatedFields = function(game_clone, game){

        var new_game = {};

        for (var prop in game_clone) {

            if(!game_clone.hasOwnProperty(prop)) continue;

            if(game_clone[prop] != game[prop] && prop != "old_platform"){
                new_game[prop] = game_clone[prop];
            }
        }

        if(game_clone.old_platform){
            if(game_clone.old_platform.platform != game.platform && game_clone.platform == game.platform){
                new_game.platform = game_clone.old_platform.platform;
            }
        }

        return new_game;
    };

    $scope.user.$promise.then(init);

});
