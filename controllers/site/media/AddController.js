app.controller('AddController', function($scope,
    CONFIG,
    $http,
    RequestService,
    AuthService,
    $route,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        $scope.addedItems = [];

        /* Set the variables for the books/movies/games */
        var media_string = $route.current.originalPath.split("_")[0];
        var media_type = media_string.substring(1, media_string.length - 1);

        $scope.variables = {
            item_type:media_type,
            get_url:CONFIG.api + CONFIG.api_routes["get_" + media_type + "s_todo"] + "0",
            put_url: CONFIG.api + CONFIG.api_routes["update_" + media_type],
            redirect_url: '/' + media_type + "s_table/"
        };
        $scope.variables.redirect_url += (media_type == "book") ? "read" : ((media_type == "movie") ? "watch" : "play");

        /* Get the items to display in the table */
        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.items = response.data;
            $scope.items_resolved = true;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
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
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }
    };

    $scope.user.$promise.then(init);

});
