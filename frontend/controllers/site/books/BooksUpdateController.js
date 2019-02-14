app.controller('BooksUpdateController', function($scope, $routeParams, CONFIG, RequestService, $http)
{

  function init(){

      $http.get(CONFIG.api + '/books/' + $routeParams.id)
        .then(function(response) {
          $scope.book = response.data;
          $scope.book_clone = $scope.clone($scope.book);
      }, function(error){
          console.log("Error");
      });

      $http.get(CONFIG.api + '/books/authors/all')
        .then(function(response) {
          $scope.authors = response.data;
      }, function(error){
          console.log("Error");
      });

      $http.get(CONFIG.api + '/books/titles/all')
        .then(function(response) {
          $scope.titles = response.data;
      }, function(error){
          console.log("Error");
      });

  }

  $scope.updateBook = function(book_clone){

    var new_book = removeNotUpdatedFields(book_clone, $scope.book);
    var url = CONFIG.api + '/books/' + $scope.book.id;

    RequestService.post(url, new_book, function(data) {
        console.log("Book was updated.");
    }, function(error, status){
        console.log(error.message);
    });

  };

  $scope.hasChanged = function(image){
      return ($scope.book.image != image);
  }

  //* Private functions *//

  var removeNotUpdatedFields = function(book_clone, book){

    var new_book = {};

    for (var prop in book_clone) {

        if(!book_clone.hasOwnProperty(prop)) continue;

        if(book_clone[prop] != book[prop] && prop != "old_author" && prop != "old_title"){
          new_book[prop] = book_clone[prop];
        }
    }

    if(book_clone.old_author){
        if(book_clone.old_author.author != book.author && book_clone.author == book.author){
            new_book.author = book_clone.old_author.author;
        }
    }

    if(book_clone.old_title){
        if(book_clone.old_title.title != book.title && book_clone.title == book.title){
            new_book.title = book_clone.old_title.title;
        }
    }

    return new_book;
  };

  init();

});
