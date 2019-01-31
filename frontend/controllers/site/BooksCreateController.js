app.controller('BooksCreateController', function($scope, Book, CONFIG, RequestService, $http)
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
        location:""
      };

      $http.get(CONFIG.api + '/books/authors/all')
        .then(function(response) {
          $scope.authors = response.data;

          console.log($scope.authors);
        });

      $http.get(CONFIG.api + '/books/titles/all')
        .then(function(response) {
          $scope.titles = response.data;

          console.log($scope.titles);
        });

  }

  $scope.createBook = function(){

    var url = CONFIG.api + '/books';

    if($scope.book.author != '' && typeof($scope.book.author) != "undefined"){
      delete $scope.book.old_author;
    }
    if($scope.book.old_author != '' && typeof($scope.book.old_author) != "undefined"){
      $scope.book.author = $scope.book.old_author.author;
      delete $scope.book.old_author;
    }

    if($scope.book.title != '' && typeof($scope.book.title) != "undefined"){
      delete $scope.book.old_title;
    }
    if($scope.book.old_title != '' && typeof($scope.book.old_title) != "undefined"){
      $scope.book.title = $scope.book.old_title.title;
      delete $scope.book.old_title;
    }

    if($scope.book.title != '' && typeof($scope.book.title) != "undefined")
    {
        RequestService.post(url, $scope.book, function(data) {
            console.log("Book was created.")
            clearBook();

        }, function(error, status) {
            console.log(error.message);
        });
    }else{
        console.log("Error: title is required.")
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
      location:""
    };
  };

  init();

});
