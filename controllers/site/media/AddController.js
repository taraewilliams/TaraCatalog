app.controller('AddController', function($scope,
    CONFIG,
    $http,
    RequestService,
    AuthService,
    $route,
    messageCenterService,
    MESSAGE_OPTIONS)
{
    var addedItems = [];
    var variables = {};

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        $scope.areAllChecked = false;

        /* Get the media type from the URL */
        var media_string = $route.current.originalPath.split("_")[0];
        var media_type = media_string.substring(1, media_string.length - 1);

        /* Set the variables for the books/movies/games */
        variables = {
            item_type:media_type,
            get_url:CONFIG.api + CONFIG.api_routes["get_" + media_type + "s_todo"] + "0",
            put_url: CONFIG.api + CONFIG.api_routes["update_" + media_type],
            redirect_url: '/' + media_type + "s_table/" + getRedirectUrl(media_type)
        };

        /* Get the items to display in the table */
        $http.get(variables.get_url)
        .then(function(response) {
            $scope.items = response.data;
            $scope.items_resolved = true;
        }, function(response){
            messageCenterService.add(response.data.type, response.data.message, { timeout: CONFIG.messageTimeout });
        });
    }

    /***************************************/
    /********** Public Functions ***********/
    /***************************************/

    /* Add or Remove all items from the Read/Watch/Play List */
    $scope.toggleAllItems = function(checked, items){
        if (checked){
            items.forEach(item => {
                if(!addedItems.includes(item.id)){
                    addedItems.push(item.id);
                    document.getElementById("toggle" + item.id).checked = true;
                }
            });
        }else{
            addedItems = [];
            items.forEach(item => {
                document.getElementById("toggle" + item.id).checked = false;
            });
        }
    };

    /* Add or Remove items from the Read/Watch/Play List to be Updated */
    $scope.toggleAddedItems = function(id){
        var nullIndex = -1;
        var index = addedItems.indexOf(id);
        if (index > nullIndex){
            // Remove item
            addedItems.splice(index,1);
        }else{
            // Add item
            addedItems.push(id);
        }
    };

    /* Toggle Read/Watch/Play List of Item */
    $scope.addToReadList = function(){

        for (i = 0; i < addedItems.length; i++){
            var id = addedItems[i];
            var new_item = { todo_list:1 };
            var url = variables.put_url + id;

            update_num = 0;

            /* Add the books/movies/games to the read/watch/play list */
            RequestService.post(url, new_item, function(data) {
                console.log(variables.item_type + " was updated.");

                /* Redirect to the Read/Watch/Play list page once all items are updated */
                update_num++;
                if (update_num == addedItems.length){
                    $scope.goToPath(variables.redirect_url);
                }
            }, function(response){
                messageCenterService.add(response.data.type, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }
    };

    /***************************************/
    /********** Private Functions **********/
    /***************************************/

    var getRedirectUrl = function(media_type){
        var redirects = {
            book:"read",
            movie:"watch",
            game:"play"
        };
        return redirects[media_type];
    };

    /***************************************/
    /**************** Init *****************/
    /***************************************/

    $scope.user.$promise.then(init);

});
