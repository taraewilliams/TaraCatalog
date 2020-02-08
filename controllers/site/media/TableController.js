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

    var variables = {};
    var addLetterColumns = ["none", "title"];

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        $scope.sortVal = "none";
        $scope.filter = getFilter();
        $scope.showFilter = false;

        /* Get the media type from the URL */
        var media_string = $route.current.originalPath.split("_")[0];
        var media_type = media_string.substring(1, media_string.length - 1);

        /* Set the variables for the books/movies/games */
        variables = {
            item_type:media_type,
            path: "/" + media_type + "s_table/",
            get_url: CONFIG.api + CONFIG.api_routes["get_" + media_type + "s"],
            get_url_order:CONFIG.api + CONFIG.api_routes["get_" + media_type + "s_order"],
            get_url_filter:CONFIG.api + CONFIG.api_routes["get_" + media_type + "s_filter"],
            put_url: CONFIG.api + CONFIG.api_routes["update_" + media_type],
            delete_text: "Delete this " + media_type + "?"
        };

        /* Get the items to display in the table */
        $http.get(variables.get_url)
        .then(function(response) {
            handleGetMedia(response, $scope.sortVal);
        }, function(response){
            $scope.errorMessage(response.data.message, response.data.type);
        });
    }

    /***************************************/
    /********** Public Functions ***********/
    /***************************************/

    $scope.getDisplayTitle = function(media)
    {
        var displayTitle = "";
        displayTitle += (!$scope.isEmpty(media.series) && (media.series != media.title))
            ? (media.series + ": ") : "";
        displayTitle += media.title;
        displayTitle += !$scope.isEmpty(media.volume) ? (", Volume " + media.volume) : "";
        displayTitle += !$scope.isEmpty(media.season) ? (", " + media.season) : "";
        return displayTitle;
    };

    /* Toggle Read/Watch List of Item */
    $scope.toggleReadList = function(id, toggle){
        var new_item = { todo_list:toggle };
        var url = variables.put_url + id;

        RequestService.post(url, new_item, function(data) {
            $scope.successMessage(variables.item_type + " was updated.");
        }, function(response){
            $scope.errorMessage(response.data.message, response.data.type);
        });
    };

    /* Delete item */
    $scope.deleteMedia = function(itemID){
        if (confirm(variables.delete_text)){
            var url = variables.put_url + itemID;

            $http.delete(url)
            .then(function(response) {
                $scope.successMessage(variables.item_type + " was deleted.");
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
            });
        }
    };

    $scope.updateMedia = function(item){
        delete item.editOn;
        var new_media = removeNotUpdatedFields(item, item.orig);
        var url = variables.put_url + item.id;

        if (!$scope.isEmptyObj(new_media)){
            RequestService.post(url, new_media, function(data) {
                $scope.successMessage(variables.item_type + " was updated.");
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
            });
        }
    };

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

        $scope.items_resolved = false;
        $scope.sortVal = sortVal;
        $scope.filter = filter;

        var filter_items = removeEmptyFields(filter);

        if($scope.isEmptyObj(filter_items)){

            var get_url = ($scope.sortVal === "none") ? variables.get_url :
                variables.get_url_order + $scope.sortVal;

            $http.get(get_url)
            .then(function(response) {
                handleGetMedia(response, $scope.sortVal);
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
            });
        }else{
            var get_url = ($scope.sortVal === "none") ? variables.get_url_filter :
                variables.get_url_filter + "/" + $scope.sortVal;

            RequestService.post(get_url, filter_items, function(response) {
                handleGetMedia(response, $scope.sortVal);
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
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

    /***************************************/
    /********** Private Functions **********/
    /***************************************/

    var handleGetMedia = function(response, sortVal){
        $scope.items = response.data;
        $scope.items_length = $scope.items.length;
        if (addLetterColumns.includes(sortVal)){
            $scope.items = $scope.addLettersToTitles($scope.items, sortVal);
        }
        $scope.items_resolved = true;
    };

    var removeNotUpdatedFields = function(media_clone, media){
        var new_media = {};
        delete media_clone.orig;

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
        if (variables.item_type === 'book'){
            return {
                title:"",
                series:"",
                author:"",
                volume:null,
                isbn:"",
                cover_type:"",
                content_type:"",
                location:"",
                genre:"",
                complete_series:""
            };
        }else if (variables.item_type === 'movie'){
            return {
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
            };
        }else{
            return {
                title:"",
                platform:"",
                esrb_rating:"",
                location:"",
                genre:"",
                complete_series:""
            };
        }
    };

    /***************************************/
    /**************** Init *****************/
    /***************************************/

    $scope.user.$promise.then(init);

});
