app.controller('MoviesCreateController', function($scope, CONFIG, RequestService, $http)
{

  function init(){

      $scope.movie = {
        title:"",
        edition:"",
        format:"",
        content_type:"",
        image:""
      };
  }

  $scope.createMovie = function(){

    var url = CONFIG.api + '/movies';

    if(!$scope.isEmpty($scope.movie.title) && !$scope.isEmpty($scope.movie.format))
    {
        RequestService.post(url, $scope.movie, function(data) {
            console.log("Movie was created.")
            clearMovie();

        }, function(error, status) {
            console.log(error.message);
        });
    }else{
        console.log("Error: title and format are required.")
    }
  };

  var clearMovie = function(){
    $scope.movie = {
      title:"",
      edition:"",
      format:"",
      content_type:"",
      image:""
    };
  };

  init();

});
