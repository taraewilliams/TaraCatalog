app.controller('GamesCreateController', function($scope, CONFIG, RequestService, $http)
{

  function init(){

      $scope.game = {
        title:"",
        platform:"",
        old_platform:"",
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
          location:"",
          image:""
      };
  };

  init();

});
