app.controller('StatsController', function($scope, CONFIG, $http)
{

  function init(){

    var url = CONFIG.api + '/books/content_type/count';

    $http.get(url)
    .then(function(response) {
      $scope.books = response.data;
      $scope.makePieChart($scope.books);
    });
  }


  $scope.makePieChart = function(books){

    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {

      var dataArray = [['Book Content Type', 'Number of Books']];

      for (i = 0; i < books.length; i++){
        dataArray.push([books[i].content_type, parseInt(books[i].num_books)]);
      }
      console.log(dataArray);

      var data = google.visualization.arrayToDataTable(dataArray);

      var options = {
        'title':'Book Content Type',
        'height':400,
        'backgroundColor': 'transparent',
        'titleTextStyle': {
          fontSize: 20
        }
      };

      var chart = new google.visualization.PieChart(document.getElementById('BookContentTypeChart'));
      chart.draw(data, options);
    }
  };

  init();

});
