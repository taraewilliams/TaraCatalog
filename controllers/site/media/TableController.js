app.controller('TableController', function($scope,
    $routeParams,
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

        $scope.is_todo_list = false;
        $scope.sortVal = "none";

        /* Set the variables for the books/movies/games */
        var media_string = $route.current.originalPath.split("_")[0];
        var media_type = media_string.substring(1, media_string.length - 1);

        $scope.variables = {
            item_type:media_type,
            path: "/" + media_type + "s_table/",
            get_url: CONFIG.api + CONFIG.api_routes["get_" + media_type + "s"],
            get_url_order:CONFIG.api + CONFIG.api_routes["get_" + media_type + "s_order"],
            get_url_filter:CONFIG.api + CONFIG.api_routes["get_" + media_type + "s_filter"],
            put_url: CONFIG.api + CONFIG.api_routes["update_" + media_type],
            delete_text: "Delete this " + media_type + "?"
        };

        $scope.filter = getFilter();
        $scope.showFilter = false;

        /* Get the items to display in the table */
        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.items = response.data;
            items_clone = $scope.clone($scope.items);
            $scope.item_length = items_clone.length;
            $scope.items = $scope.addLettersToTitles($scope.items, "none");
            $scope.items_resolved = true;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });
    }

    $scope.getDisplayTitle = function(media){

        var displayTitle = "";

        if (!$scope.isEmpty(media.series) && (media.series != media.title)){
            displayTitle = displayTitle + media.series + ": ";
        }

        displayTitle = displayTitle + media.title;

        if (!$scope.isEmpty(media.volume)){
            displayTitle = displayTitle + ", Volume " + media.volume;
        }

        return displayTitle;
    };

    /* Toggle Read/Watch List of Item */
    $scope.toggleReadList = function(id,toggle){

        var new_item = { todo_list:toggle };
        var url = $scope.variables.put_url + id;

        RequestService.post(url, new_item, function(data) {
            messageCenterService.add(MESSAGE_OPTIONS.success, $scope.variables.item_type + " was updated.", { timeout: CONFIG.messageTimeout });
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });
    };

    /* Delete item */
    $scope.deleteItem = function(bookID){

        if (confirm($scope.variables.delete_text)){
            var url = $scope.variables.put_url + bookID;

            $http.delete(url)
            .then(function(response) {
                messageCenterService.add(MESSAGE_OPTIONS.success, $scope.variables.item_type + " was deleted.", { timeout: CONFIG.messageTimeout });
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }
    };

    $scope.updateMedia = function(item){

        delete item.editOn;
        var new_media = removeNotUpdatedFields(item, item.orig);
        var url = $scope.variables.put_url + item.id;
        delete new_media.orig;

        if (!$scope.isEmptyObj(new_media)){
            RequestService.post(url, new_media, function(data) {
                messageCenterService.add(MESSAGE_OPTIONS.success, $scope.variables.item_type + " was updated.", { timeout: CONFIG.messageTimeout });
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }
    };

    $scope.updateForSelectValue = function(old_item, field){
        $scope.media_clone[field] = old_item;
    };

    $scope.hasChanged = function(image){
        return ($scope.media.image != image);
    }

    $scope.printPage = function(){
        window.print();
    };

    $scope.toggleEdit = function(item){
        item.editOn = !item.editOn;
        if (item.editOn){
            item.orig = $scope.clone(item);
        }
    };

    /* Sort table items by a specific field */
    $scope.sortBy = function(sortVal, filter){

        $scope.sortVal = sortVal;
        var filter_items = removeEmptyFields(filter);

        if($scope.isEmptyObj(filter_items)){
            if (sortVal == "none"){
                var get_url = $scope.variables.get_url;
            }else{
                var get_url = $scope.variables.get_url_order + sortVal;
            }
            $http.get(get_url)
            .then(function(response) {
                $scope.items = response.data;
                items_clone = $scope.clone($scope.items);
                $scope.item_length = items_clone.length;
                if(sortVal=="none" || sortVal == "title"){
                    $scope.items = $scope.addLettersToTitles($scope.items, sortVal);
                }
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }else{
            if (sortVal == "none"){
                var get_url = $scope.variables.get_url_filter;
            }else{
                var get_url = $scope.variables.get_url_filter + "/" + sortVal;
            }
            RequestService.post(get_url, filter_items, function(response) {
                $scope.items = response.data;
                items_clone = $scope.clone($scope.items);
                $scope.item_length = items_clone.length;
                if(sortVal=="none" || sortVal == "title"){
                    $scope.items = $scope.addLettersToTitles($scope.items, sortVal);
                }
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }
    };

    /* Filter functions */
    $scope.clearFilter = function(sortVal, filter){
        $scope.filter = getFilter();
        $scope.sortBy(sortVal, $scope.filter);
    };

    $scope.toggleFilter = function(){
        $scope.showFilter = !$scope.showFilter;
    };

    //* Private Functions *//

    var removeNotUpdatedFields = function(media_clone, media){

        var new_media = {};

        for (var prop in media_clone) {

            if(!media_clone.hasOwnProperty(prop)) continue;

            if(media_clone[prop] != media[prop]){
                new_media[prop] = media_clone[prop];
            }
        }

        return new_media;
    };

    var removeEmptyFields = function(obj){
        for (var propName in obj) {
            if (obj[propName] === null || obj[propName] === undefined || obj[propName] == '') {
                delete obj[propName];
            }
        }
        return obj;
    };

    var getFilter = function(){

        var filters = {
            book: {
                title:"",
                series:"",
                author:"",
                old_author:"",
                volume:null,
                isbn:"",
                cover_type:"",
                content_type:"",
                location:"",
                genre:"",
                complete_series:""
            },
            movie: {
                title:"",
                edition:"",
                season:"",
                format:"",
                content_type:"",
                mpaa_rating:"",
                location:"",
                genre:"",
                running_time:null,
                complete_series:""
            },
            game: {
                title:"",
                platform:"",
                esrb_rating:"",
                location:"",
                genre:"",
                complete_series:""
            }
        };
        return filters[$scope.variables.item_type];
    };

    $scope.user.$promise.then(init);

});
