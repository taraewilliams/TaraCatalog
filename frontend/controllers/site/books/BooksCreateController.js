app.controller('BooksCreateController', function($scope, CONFIG, RequestService, $http)
{

  function init(){

      $scope.book = {
        title:"",
        old_title:"",
        author:"",
        old_author:"",
        volume:null,
        isbn:"",
        cover_type:"",
        content_type:"",
        location:"",
        image:""
      };

      $http.get(CONFIG.api + '/books/authors/all')
        .then(function(response) {
          $scope.authors = response.data;
      });

      $http.get(CONFIG.api + '/books/titles/all')
        .then(function(response) {
          $scope.titles = response.data;
      });

  }

  $scope.createBook = function(){

    var url = CONFIG.api + '/books';

    if(!$scope.isEmpty($scope.book.old_author) && $scope.isEmpty($scope.book.author)){
      $scope.book.author = $scope.book.old_author.author;
    }
    delete $scope.book.old_author;

    if(!$scope.isEmpty($scope.book.old_title) && $scope.isEmpty($scope.book.title)){
      $scope.book.title = $scope.book.old_title.title;
    }
    delete $scope.book.old_title;

    if(!$scope.isEmpty($scope.book.title))
    {
        RequestService.post(url, $scope.book, function(data) {
            alert("Book was created.")
            clearBook();
            window.scrollTo(0,0);

        }, function(error, status) {
            console.log(error.message);
        });
    }else{
        alert("Title is required.")
    }
  };

  var clearBook = function(){
    $scope.book = {
      title:"",
      old_title:"",
      author:"",
      old_author:"",
      volume:null,
      isbn:"",
      cover_type:"",
      content_type:"",
      location:"",
      image:""
    };
  };

  init();

});
