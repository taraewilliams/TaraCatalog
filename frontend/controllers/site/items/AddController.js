app.controller('AddController', function($scope, $routeParams, CONFIG, $http, RequestService, AuthService)
{
    /* Redirect if not logged in */
    if( AuthService.redirectOnUnauthorized() ) {
        return;
    }

    function init(){

        $scope.addedItems = [];

        /* Set the variables for books/movies/games */
        if($scope.isActive('/books_table/read_add')){
            $scope.variables = {
                item_type:"book",
                get_url:CONFIG.api + '/books/read/list/0',
                put_url: CONFIG.api + '/books/',
                redirect_url: "/books_table/read"
            };
        }else if ($scope.isActive('/movies_table/watch_add')){
            $scope.variables = {
                item_type:"movie",
                get_url:CONFIG.api + '/movies/watch/list/0',
                put_url:CONFIG.api + "/movies/",
                redirect_url: "/movies_table/watch"
            };
        }else{
            $scope.variables = {
                item_type:"game",
                get_url:CONFIG.api + '/games/play/list/0',
                put_url:CONFIG.api + '/games/',
                redirect_url: "/games_table/play"
            };
        }

        /* Get the items to display in the table */
        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.items = response.data;
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

            if ($scope.variables.item_type == "book"){
                var new_item = { read_list:1 };
            }else if ($scope.variables.item_type == "movie"){
                var new_item = { watch_list:1 };
            }else{
                var new_item = { play_list:1 };
            }

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
            }, function(error, status){
                console.log(error.message);
            });
        }
    };

    init();

});
