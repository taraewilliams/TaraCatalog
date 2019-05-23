app.controller('GamesCreateController', function($scope, CONFIG, RequestService, $http, AuthService)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        $scope.game = {
            title:"",
            platform:"",
            old_platform:"",
            esrb_rating:"none",
            location:"",
            image:""
        };

        $http.get(CONFIG.api + '/games/platforms/all')
        .then(function(response) {
            $scope.platforms = response.data;
        });

    }

    $scope.createGame = function(){

        var url = CONFIG.api + '/games';

        if(!$scope.isEmpty($scope.game.old_platform) && $scope.isEmpty($scope.game.platform)){
            $scope.game.platform = $scope.game.old_platform.platform;
        }
        delete $scope.game.old_platform;

        if(!$scope.isEmpty($scope.game.title))
        {
            RequestService.post(url, $scope.game, function(data) {
                alert("Game was created.")
                clearGame();
                window.scrollTo(0,0);

            }, function(error, status) {
                console.log(error.message);
            });
        }else{
            alert("Title is required.")
        }
    };

    var clearGame = function(){
        $scope.game = {
            title:"",
            platform:"",
            old_platform:"",
            esrb_rating:"none",
            location:"",
            image:""
        };
    };

    $scope.user.$promise.then(init);

});
