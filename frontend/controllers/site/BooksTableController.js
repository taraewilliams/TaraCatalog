app.controller('BooksTableController', function($scope, RequestService, CONFIG, $http)
{

  function init(){

    var url = CONFIG.api + '/books';

    $http.get(url)
    .then(function(response) {
      $scope.books = response.data;
      $scope.books = $scope.addLetters($scope.books);
    }, function(response){
      console.log("Error");
    });

  }

  $scope.addLetters = function(books){

    var books_clone = clone(books);
    var added_letters = 0;

    for (var i = 0; i < books.length; i++){
      if (i !== books.length - 1){
          var prev_letter = books[i].title.charAt(0).toUpperCase();
          var curr_letter = books[i + 1].title.charAt(0).toUpperCase();

          if (prev_letter !== curr_letter){
            var index = (i + 1) + added_letters;
            var letter = {
              title: curr_letter,
              isHeader: 1
            };
            books_clone.splice(index, 0, letter);
            added_letters += 1;
          }
      }
    }
    return books_clone;
  };

  var clone = function(obj) {
      if (null == obj || "object" != typeof obj) return obj;
      var copy = obj.constructor();
      for (var attr in obj) {
          if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
      }
      return copy;
  }

  init();

});
