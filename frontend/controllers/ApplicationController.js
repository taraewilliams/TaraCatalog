app.controller('ApplicationController', function ($scope, $route, $location)
{

    $scope.goToPath = function(path, param=null){
        if(param != null){
            path = path + param;
        }
        $location.path(path);
    };

    $scope.clone = function(obj) {
        if (null == obj || "object" != typeof obj) return obj;
        var copy = obj.constructor();
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
        }
        return copy;
    };

    $scope.isEmpty = function(obj){
      return (obj == '' || typeof(obj) == "undefined" || obj == null);
    };

    $scope.addLettersToTitles = function(items){

      var items_clone = $scope.clone(items);
      var added_letters = 0;

      for (var i = 0; i < items.length; i++){
        if (i !== items.length - 1){
            var prev_letter = items[i].title.charAt(0).toUpperCase();
            var curr_letter = items[i + 1].title.charAt(0).toUpperCase();

            if (prev_letter !== curr_letter){
              var index = (i + 1) + added_letters;
              var letter = {
                title: curr_letter,
                isHeader: 1
              };
              items_clone.splice(index, 0, letter);
              added_letters += 1;
            }
        }
      }
      $scope.totalAddedLetters = added_letters;
      return items_clone;
    };

    $scope.switchPage = function(page, path){
        $scope.goToPath(path + page.offset + "/" + page.limit);
    };

    $scope.skipOnePage = function(skip, pages, path){
        var active_page = getActivePage(pages);

        if(skip == "forward"){
            active_page = (active_page + 1 >= pages.length) ? pages.length : active_page + 1;
        }else{
            active_page = (active_page - 1 <= 1) ? 1 : active_page - 1;
        }
        var page = getPage(active_page, pages);
        $scope.goToPath(path + page.offset + "/" + page.limit);
    };

    $scope.skipToFinalPage = function(skip, pages, path){
        if(skip == "forward"){
            var page = getPage(pages.length, pages);
        }else{
            var page = getPage(1, pages);
        }
        $scope.goToPath(path + page.offset + "/" + page.limit);
    };

    var getActivePage = function(pages){
        for (i = 0; i < pages.length; i++){
            var page = pages[i];
            if (page.active){
                return page.num;
            }
        }
    };

    var getPage = function(page_num, pages){
        for (i = 0; i < pages.length; i++){
            var page = pages[i];
            if (page.num == page_num){
                return page;
            }
        }
    };


});
