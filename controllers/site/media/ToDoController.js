app.controller('ToDoController', function($scope,
    CONFIG,
    $http,
    RequestService,
    AuthService,
    $route,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    var variables = {};
    var removedItems = [];

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        $scope.editOn = false;
        $scope.areAllChecked = false;

        /* Get the media type from the URL */
        var media_string = $route.current.originalPath.split("_")[0];
        var media_type = media_string.substring(1, media_string.length - 1);

        /* Set the variables for the books/movies/games */
        variables = {
            item_type:media_type,
            get_url:CONFIG.api + CONFIG.api_routes["get_" + media_type + "s_todo"] + '1',
            put_url: CONFIG.api + CONFIG.api_routes["update_" + media_type]
        };

        /* Get the items to display in the table */
        $http.get(variables.get_url)
        .then(function(response) {
            $scope.items = response.data;
            $scope.items_length = $scope.items.length;
            $scope.items = $scope.addLettersToTitles($scope.items, "none");
            $scope.items_resolved = true;
        }, function(response){
            $scope.errorMessage(response.data.message, response.data.type);
        });
    }

    /***************************************/
    /********** Public Functions ***********/
    /***************************************/

    /* Add or Remove all items from the Read/Watch/Play List */
    $scope.toggleAllItems = function(checked, items){
        if (checked){
            items.forEach(item => {
                if (!item.isHeader){
                    if(!removedItems.includes(item.id)){
                        removedItems.push(item.id);
                        document.getElementById("toggle" + item.id).checked = true;
                    }
                }
            });
        }else{
            removedItems = [];
            items.forEach(item => {
                if (!item.isHeader){
                    document.getElementById("toggle" + item.id).checked = false;
                }
            });
        }
    };

    /* Add or Remove items from the Read/Watch/Play List to be Updated */
    $scope.toggleRemovedItems = function(id){
        var index = removedItems.indexOf(id);
        if (index > -1){
            // Remove item
            removedItems.splice(index,1);
        }else{
            // Add item
            removedItems.push(id);
        }
    };

    /* Toggle Read/Watch/Play List of Item */
    $scope.removeFromToDoList = function(id_list){

        for (i = 0; i < id_list.length; i++){
            var id = id_list[i];
            var new_item = { todo_list:0 };
            var url = variables.put_url + id;

            update_num = 0;
            /* Add the books/movies/games to the read/watch/play list */
            RequestService.post(url, new_item, function(data) {
                /* Redirect to the Read/Watch/Play list page once all items are updated */
                update_num = update_num + 1;
                if (update_num == id_list.length){
                    location.reload();
                }
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
            });
        }
    };

    $scope.toggleEdit = function(){
        $scope.editOn = !$scope.editOn;
    };

    /***************************************/
    /**************** Init *****************/
    /***************************************/

    $scope.user.$promise.then(init);

});
