app.controller('StatsController', function($scope, CONFIG, $http)
{

  function init(){

    /* Book Statistics */
    $http.get(CONFIG.api + '/books/content_type/count')
    .then(function(response) {
      $scope.content_types = response.data;
      $scope.makePieChartBookContentType($scope.content_types);
    });

    $http.get(CONFIG.api + '/books/cover_type/count')
    .then(function(response) {
      $scope.cover_types = response.data;
      $scope.makePieChartBookCoverType($scope.cover_types);
    });

    /* Movie Statistics */
    $http.get(CONFIG.api + '/movies/format/count')
    .then(function(response) {
      $scope.formats = response.data;
      $scope.makePieChartMovieFormatType($scope.formats);
    });

    $http.get(CONFIG.api + '/movies/content_type/count')
    .then(function(response) {
      $scope.content_types = response.data;
      $scope.makePieChartMovieContentType($scope.content_types);
    });
  }


  $scope.makePieChartBookContentType = function(content_types){
    var dataArray = [['Book Content Type', 'Number of Books']];
    var title = 'Book Content Type';
    var html_element = 'BookContentTypeChart';
    $scope.makePieChart(content_types, html_element, title, dataArray);
  };

  $scope.makePieChartBookCoverType = function(cover_types){
      var dataArray = [['Book Cover Type', 'Number of Books']];
      var title = 'Book Cover Type';
      var html_element = 'BookCoverTypeChart';
      $scope.makePieChart(cover_types, html_element, title, dataArray);
  };

  $scope.makePieChartMovieFormatType = function(formats){
    var dataArray = [['Movie Format', 'Number of Movies']];
    var title = 'Movie Format';
    var html_element = 'MovieFormatChart';
    $scope.makePieChart(formats, html_element, title, dataArray);
  };

  $scope.makePieChartMovieContentType = function(content_types){
    var dataArray = [['Movie Content Type', 'Number of Movies']];
    var title = 'Movie Content Type';
    var html_element = 'MovieContentTypeChart';
    $scope.makePieChart(content_types, html_element, title, dataArray);
  };

  $scope.makePieChart = function(items, html_element, title, dataArray){

    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {

      for (i = 0; i < items.length; i++){
        dataArray.push([items[i].type, parseInt(items[i].num)]);
      }

      var data = google.visualization.arrayToDataTable(dataArray);

      var options = {
        'title': title,
        'height':400,
        'backgroundColor': 'transparent',
        'titleTextStyle': {
          fontSize: 20
        }
      };

      var chart = new google.visualization.PieChart(document.getElementById(html_element));
      chart.draw(data, options);
    }
  };


  init();

});
