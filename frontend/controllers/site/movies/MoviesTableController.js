app.controller('MoviesTableController', function($scope, CONFIG, $http)
{

  function init(){

    $scope.filter = {
      title:"",
      edition:"",
      format:"",
      content_type:""
    };

    $scope.showFilter = false;

    var url = CONFIG.api + '/movies';

    $http.get(url)
    .then(function(response) {
      $scope.movies = response.data;
      $scope.movies = $scope.addLettersToTitles($scope.movies);
    }, function(response){
      console.log("Error");
    });

  }

  $scope.isFilterResult = function(movie){
    if (!movie.isHeader){
      var is_title = filterResultItem(movie.title, $scope.filter.title);
      var is_edition = filterResultItem(movie.edition, $scope.filter.edition);
      var is_format = filterResultItem(movie.format, $scope.filter.format);
      var is_content_type = filterResultItem(movie.content_type, $scope.filter.content_type);

      return is_title && is_edition && is_format && is_content_type;
    }else{
      return $scope.isEmpty($scope.filter.title) && $scope.isEmpty($scope.filter.edition)
      && $scope.isEmpty($scope.filter.format) && $scope.isEmpty($scope.filter.content_type);
    }
  };

  $scope.clearFilter = function(){
      $scope.filter = {
        title:"",
        edition:"",
        format:"",
        content_type:""
      };
  };

  $scope.toggleFilter = function(){
      $scope.showFilter = !$scope.showFilter;
  };

  //* Private Functions *//

  var filterResultItem = function(movie_item, filter_item){
    if (!$scope.isEmpty(filter_item) && $scope.isEmpty(movie_item)){
      return false;
    }else{
      return !$scope.isEmpty(filter_item) ? (Number.isInteger(movie_item) ? movie_item == filter_item : movie_item.toLowerCase().includes(filter_item.toLowerCase())) : true;
    }
  };

  init();

});
