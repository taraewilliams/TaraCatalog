app.controller('ToDoController', function($scope, $routeParams, CONFIG, $http, RequestService, AuthService)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        if($scope.isActive(['/books_table/read'])){
            $scope.variables = {
                item_type:"book",
                get_url:CONFIG.api + '/books/read/list/1',
                put_url: CONFIG.api + '/books/'
            };
        }else if ($scope.isActive(['/movies_table/watch'])){
            $scope.variables = {
                item_type:"movie",
                get_url: CONFIG.api + '/movies/watch/list/1',
                put_url: CONFIG.api + '/movies/'
            };
        }else{
            $scope.variables = {
                item_type:"game",
                get_url: CONFIG.api + '/games/play/list/1',
                put_url: CONFIG.api + '/games/'
            };
        }

        /* Get the items to display in the table */
        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.items = response.data;
            items_clone = $scope.clone($scope.items);
            $scope.item_length = items_clone.length;
            $scope.items = $scope.addLettersToTitles($scope.items);
        }, function(response){
            console.log("Error");
        });
    }

    /* Toggle Read/Watch List of Item */
    $scope.toggleReadList = function(id,toggle){

        if ($scope.variables.item_type == "book"){
            var new_item = { read_list:toggle };
        }else if ($scope.variables.item_type == "movie"){
            var new_item = { watch_list:toggle };
        }else{
            var new_item = { play_list:toggle };
        }

        var url = $scope.variables.put_url + id;

        RequestService.post(url, new_item, function(data) {
            alert($scope.variables.item_type + " was updated.");
        }, function(error, status){
            console.log(error.message);
        });
    };

    /* Add alphabetical letters between the titles to organize */
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
        return items_clone;
    };

    $scope.user.$promise.then(init);

});
