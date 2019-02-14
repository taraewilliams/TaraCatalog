app.controller('MoviesUpdateController', function($scope, $routeParams, CONFIG, RequestService, $http)
{

  function init(){

      $http.get(CONFIG.api + '/movies/' + $routeParams.id)
        .then(function(response) {
          $scope.movie = response.data;
          $scope.movie_clone = $scope.clone($scope.movie);
      });

  }

  $scope.updateMovie = function(movie_clone){

    var new_movie = removeNotUpdatedFields(movie_clone, $scope.movie);
    var url = CONFIG.api + '/movies/' + $scope.movie.id;

    RequestService.post(url, new_movie, function(data) {
        console.log("Movie was updated.");
    }, function(error, status){
        console.log(error.message);
    });

  };

  $scope.hasChanged = function(image){
      return ($scope.movie.image != image);
  };

  //* Private functions *//

  var removeNotUpdatedFields = function(movie_clone, movie){

    var new_movie = {};

    for (var prop in movie_clone) {

        if(!movie_clone.hasOwnProperty(prop)) continue;

        if(movie_clone[prop] != movie[prop]){
          new_movie[prop] = movie_clone[prop];
        }
    }

    return new_movie;
  };

  init();

});
