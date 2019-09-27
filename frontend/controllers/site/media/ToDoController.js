app.controller('ToDoController', function($scope, $routeParams, CONFIG, $http, RequestService, AuthService)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        $scope.removedItems = [];
        $scope.editOn = false;

        if($scope.isActive(['/books_table/read'])){
            $scope.variables = {
                item_type:"book",
                get_url:CONFIG.api + CONFIG.api_routes.get_books_todo + '1',
                put_url: CONFIG.api + CONFIG.api_routes.update_book
            };
        }else if ($scope.isActive(['/movies_table/watch'])){
            $scope.variables = {
                item_type:"movie",
                get_url: CONFIG.api + CONFIG.api_routes.get_movies_todo + '1',
                put_url: CONFIG.api + CONFIG.api_routes.update_movie
            };
        }else{
            $scope.variables = {
                item_type:"game",
                get_url: CONFIG.api + CONFIG.api_routes.get_games_todo + '1',
                put_url: CONFIG.api + CONFIG.api_routes.update_game
            };
        }

        /* Get the items to display in the table */
        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.items = response.data;
            items_clone = $scope.clone($scope.items);
            $scope.item_length = items_clone.length;
            $scope.items = $scope.addLettersToTitles($scope.items, "none");
            $scope.items_resolved = true;
        }, function(response){
            console.log("Error");
        });
    }

    /* Add or Remove items from the Read/Watch/Play List to be Updated */
    $scope.toggleRemovedItems = function(id){
        var index = $scope.removedItems.indexOf(id);
        if (index > -1){
            $scope.removedItems.splice(index,1);
        }else{
            $scope.removedItems.push(id);
        }
    };

    /* Toggle Read/Watch/Play List of Item */
    $scope.removeFromToDoList = function(id_list){

        for (i = 0; i < id_list.length; i++){
            var id = id_list[i];
            var new_item = { todo_list:0 };
            var url = $scope.variables.put_url + id;

            update_num = 0;
            /* Add the books/movies/games to the read/watch/play list */
            RequestService.post(url, new_item, function(data) {
                console.log($scope.variables.item_type + " was updated.");

                /* Redirect to the Read/Watch/Play list page once all items are updated */
                update_num = update_num + 1;
                if (update_num == id_list.length){
                    location.reload();
                }
            }, function(error, status){
                console.log(error.message);
            });
        }
    };

    $scope.toggleEdit = function(){
        $scope.editOn = !$scope.editOn;
    };

    $scope.user.$promise.then(init);

});
