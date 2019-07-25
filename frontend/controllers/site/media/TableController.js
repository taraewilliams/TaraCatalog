app.controller('TableController', function($scope, $routeParams, CONFIG, $http, RequestService, AuthService)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        $scope.is_todo_list = false;
        $scope.sortVal = "none";

        if($scope.isActive(['/books_table'])){
            $scope.variables = {
                item_type:"book",
                path: "/books_table/",
                get_url: CONFIG.api + '/books',
                get_url_order:CONFIG.api + '/books/order_by/',
                get_url_filter:CONFIG.api + '/books/filter',
                put_url: CONFIG.api + '/books/',
                delete_text: "Delete this book?"
            };
        }else if ($scope.isActive(['/movies_table'])){
            $scope.variables = {
                item_type:"movie",
                path: "/movies_table/",
                get_url: CONFIG.api + '/movies',
                get_url_order: CONFIG.api + "/movies/order_by/",
                get_url_filter:CONFIG.api + '/movies/filter',
                put_url: CONFIG.api + '/movies/',
                delete_text: "Delete this movie?"
            };
        }else{
            $scope.variables = {
                item_type:"game",
                path: "/games_table/",
                get_url: CONFIG.api + '/games',
                get_url_order: CONFIG.api + "/games/order_by/",
                get_url_filter:CONFIG.api + '/games/filter',
                put_url: CONFIG.api + '/games/',
                delete_text: "Delete this game?"
            };
        }

        $scope.filter = getFilter();
        $scope.showFilter = false;

        /* Get the items to display in the table */
        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.items = response.data;
            items_clone = $scope.clone($scope.items);
            $scope.item_length = items_clone.length;
            $scope.items = $scope.addLettersToTitles($scope.items);
            $scope.items_resolved = true;
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


    /* Delete item */
    $scope.deleteItem = function(bookID){

        if (confirm($scope.variables.delete_text)){
            var url = $scope.variables.put_url + bookID;

            $http.delete(url)
            .then(function(response) {
                alert("The item was deleted.");
            }, function(response){
                console.log("Error");
            });
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
                    $scope.items = $scope.addLettersToTitles($scope.items);
                }
            }, function(response){
                console.log("Error");
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
                    $scope.items = $scope.addLettersToTitles($scope.items);
                }
            }, function(error, status){
                console.log(error.message);
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

    var removeEmptyFields = function(obj){
        for (var propName in obj) {
            if (obj[propName] === null || obj[propName] === undefined || obj[propName] == '') {
                delete obj[propName];
            }
        }
        return obj;
    };

    var getFilter = function(){
        if ($scope.variables.item_type == "book"){
            return {
                title:"",
                author:"",
                old_author:"",
                volume:null,
                isbn:"",
                cover_type:"",
                content_type:"",
                location:""
            };
        }else if ($scope.variables.item_type == "movie"){
            return {
                title:"",
                edition:"",
                season:"",
                format:"",
                content_type:"",
                mpaa_rating:"",
                location:""
            };
        }else{
            return {
                title:"",
                platform:"",
                esrb_rating:"",
                location:""
            };
        }
    };

    $scope.user.$promise.then(init);

});
