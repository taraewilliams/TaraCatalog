app.controller('MoviesController', function($scope, $routeParams, RequestService, CONFIG, $http)
{
  $scope.maxPerPage = 25;
  $scope.maxPages = 5;

  function init(){

    var offset = $routeParams.offset;
    var limit = $routeParams.limit;
    var url = CONFIG.api + '/movies/limit/' + offset + "/" + limit;

    $http.get(url)
    .then(function(response) {
      $scope.movies = response.data;
    });

    $http.get(CONFIG.api + '/movies/count/all')
    .then(function(response) {
      $scope.num_movies = parseInt(response.data.num_movies);
      var num_pages = Math.ceil($scope.num_movies/$scope.maxPerPage);

      var current_page = (offset/$scope.maxPerPage) + 1;
      if ((current_page + $scope.maxPages - 1) >= num_pages){
        var last_page = num_pages;
        var first_page = (num_pages - $scope.maxPages) + 1;
      }else{
        var first_page = current_page;
        var last_page = (current_page + $scope.maxPages) - 1;
      }

      $scope.pages = [];
      for(i = 1; i <= num_pages; i++){
        var page = {
          num: i,
          offset: (i - 1) * $scope.maxPerPage,
          limit: $scope.maxPerPage,
          active: i == current_page,
          current: i >= first_page && i <= last_page,
          firstPage: i == first_page,
          lastPage: i == last_page
        };
        $scope.pages.push(page);
      }
    });
  }

  $scope.deleteMovie = function(movieID){

    if (confirm("Delete this movie?")){
      var url = CONFIG.api + '/movies/' + movieID;

      $http.delete(url)
      .then(function(response) {
        console.log("Success");
      }, function(response){
        console.log("Error");
      });
    }
  };

  init();

});
