app.controller('BooksTableController', function($scope, CONFIG, $http)
{

  function init(){

    $scope.filter = {
      title:"",
      author:"",
      old_author:"",
      volume:null,
      isbn:"",
      cover_type:"",
      content_type:"",
      location:""
    };

    $scope.showFilter = false;

    var url = CONFIG.api + '/books';

    $http.get(url)
    .then(function(response) {
      $scope.books = response.data;
      $scope.books = $scope.addLettersToTitles($scope.books);
    }, function(response){
      console.log("Error");
    });

    $http.get(CONFIG.api + '/books/authors/all')
      .then(function(response) {
        $scope.authors = response.data;
    });
  }

  $scope.isFilterResult = function(book){
    if (!book.isHeader){
      var is_title = filterResultItem(book.title, $scope.filter.title);
      var is_volume = filterResultItem(book.volume, $scope.filter.volume);
      var is_isbn = filterResultItem(book.isbn, $scope.filter.isbn);
      var is_cover_type = filterResultItem(book.cover_type, $scope.filter.cover_type);
      var is_content_type = filterResultItem(book.content_type, $scope.filter.content_type);
      var is_location = filterResultItem(book.location, $scope.filter.location);
      var is_author = filterResultAuthor(book.author, $scope.filter);

      return is_title && is_author && is_volume && is_isbn && is_cover_type && is_content_type && is_location;
    }else{
      return $scope.isEmpty($scope.filter.title) && $scope.isEmpty($scope.filter.author)
      && $scope.isEmpty($scope.filter.old_author.author) && $scope.isEmpty($scope.filter.volume)
      && $scope.isEmpty($scope.filter.isbn) && $scope.isEmpty($scope.filter.cover_type)
      && $scope.isEmpty($scope.filter.content_type) && $scope.isEmpty($scope.filter.location);
    }
  };

  $scope.clearFilter = function(){
      $scope.filter = {
        title:"",
        author:"",
        old_author:"",
        volume:null,
        isbn:"",
        cover_type:"",
        content_type:"",
        location:""
      };
  };

  $scope.toggleFilter = function(){
      $scope.showFilter = !$scope.showFilter;
  };

  //* Private Functions *//

  var filterResultAuthor = function(author, filter){
      if (!$scope.isEmpty(filter.old_author.author) && !$scope.isEmpty(filter.author)){
        return filterResultItem(author, filter.author) && filterResultItem(author, filter.old_author.author);
      }
      else if (!$scope.isEmpty(filter.old_author.author)){
        return filterResultItem(author, filter.old_author.author);
      }
      else if (!$scope.isEmpty(filter.author)){
        return filterResultItem(author, filter.author);
      }else{
        return true;
      }
  }

  var filterResultItem = function(book_item, filter_item){
    if (!$scope.isEmpty(filter_item) && $scope.isEmpty(book_item)){
      return false;
    }else{
      return !$scope.isEmpty(filter_item) ? (Number.isInteger(book_item) ? book_item == filter_item : book_item.toLowerCase().includes(filter_item.toLowerCase())) : true;
    }
  };

  init();

});
