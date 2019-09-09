app.controller('AddController', function($scope, $routeParams, CONFIG, $http, RequestService, AuthService)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        $scope.addedItems = [];

        /* Set the variables for books/movies/games */
        if($scope.isActive('/books_table/read_add')){
            $scope.variables = {
                item_type:"book",
                get_url:CONFIG.api + CONFIG.api_routes.get_books_todo + '0',
                put_url: CONFIG.api + CONFIG.api_routes.update_book,
                redirect_url: "/books_table/read"
            };
        }else if ($scope.isActive('/movies_table/watch_add')){
            $scope.variables = {
                item_type:"movie",
                get_url:CONFIG.api + CONFIG.api_routes.get_movies_todo + '0',
                put_url:CONFIG.api + CONFIG.api_routes.update_movie,
                redirect_url: "/movies_table/watch"
            };
        }else{
            $scope.variables = {
                item_type:"game",
                get_url:CONFIG.api + CONFIG.api_routes.get_games_todo + '0',
                put_url:CONFIG.api + CONFIG.api_routes.update_game,
                redirect_url: "/games_table/play"
            };
        }

        /* Get the items to display in the table */
        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.items = response.data;
            $scope.items_resolved = true;
        }, function(response){
            console.log("Error");
        });
    }

    /* Add or Remove items from the Read/Watch/Play List to be Updated */
    $scope.toggleAddedItems = function(id){
        var index = $scope.addedItems.indexOf(id);
        if (index > -1){
            $scope.addedItems.splice(index,1);
        }else{
            $scope.addedItems.push(id);
        }
    };

    /* Toggle Read/Watch/Play List of Item */
    $scope.addToReadList = function(id_list){

        for (i = 0; i < id_list.length; i++){
            var id = id_list[i];
            var new_item = { todo_list:1 };
            var url = $scope.variables.put_url + id;

            update_num = 0;
            /* Add the books/movies/games to the read/watch/play list */
            RequestService.post(url, new_item, function(data) {
                console.log($scope.variables.item_type + " was updated.");

                /* Redirect to the Read/Watch/Play list page once all items are updated */
                update_num = update_num + 1;
                if (update_num == id_list.length){
                    $scope.goToPath($scope.variables.redirect_url);
                }
            }, function(response){
                console.log(response);
            });
        }
    };

    $scope.user.$promise.then(init);

});
