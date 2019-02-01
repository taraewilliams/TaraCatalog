app.controller('BooksController', function($scope, RequestService, CONFIG, $http)
{
  $scope.maxPerPage = 25;

  function init(){

    var offset = 0;
    var limit = $scope.maxPerPage;
    var url = CONFIG.api + '/books/limit/' + offset + "/" + limit;

    $http.get(url)
    .then(function(response) {
      $scope.books = response.data;
    });

    $http.get(CONFIG.api + '/books/count/all')
    .then(function(response) {
      $scope.num_books = parseInt(response.data.num_books);
      var num_pages = Math.ceil($scope.num_books/$scope.maxPerPage);

      $scope.pages = [];
      for(i = 1; i <= num_pages; i++){
        var page = {
          num: i,
          offset: (i - 1) * $scope.maxPerPage,
          limit: $scope.maxPerPage,
          active: i == 1 ? 1 : 0
        };
        $scope.pages.push(page);
      }
    });

  }

  $scope.switchPage = function(page){

    var url = CONFIG.api + '/books/limit/' + page.offset + "/" + page.limit;

    $http.get(url)
    .then(function(response) {
      $scope.books = response.data;
      window.scrollTo(0, 0);

      for (i = 0; i < $scope.pages.length; i++){
        if (page.num == $scope.pages[i].num){
          $scope.pages[i].active = 1;
        }else{
          $scope.pages[i].active = 0;
        }
      }
    });
  };

  init();

});
