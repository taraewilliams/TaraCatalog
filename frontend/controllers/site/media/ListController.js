app.controller('ListController', function($scope,
    $routeParams,
    CONFIG,
    $http,
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

        /* The maximum number of pages to show in the pagination bar */
        $scope.maxPages = 5;

        /* The valid limits for number of items per page */
        $scope.validLimits = [28, 56, 84, 112, "All"];

        /* The limit for 'all' is set to 0 */
        var all_limit = 0;
        var offset = $routeParams.offset;
        var limit = $routeParams.limit;

        /* Set the variables for the books/movies/games */
        var media_string = $route.current.originalPath.split("/")[1];
        var media_type = media_string.substring(0, media_string.length - 1);

        $scope.variables = {
            media_type:media_type,
            path: "/" + media_type + "s/",
            get_url: CONFIG.api + CONFIG.api_routes["get_" + media_type + "s_limit"] + offset + "/" + limit,
            get_count_url: CONFIG.api + CONFIG.api_routes["get_" + media_type + "_count"],
            delete_url: CONFIG.api + CONFIG.api_routes["delete_" + media_type],
            delete_text: "Delete this " + media_type + "?"
        };

        if (limit == all_limit){
            $scope.variables.get_url = CONFIG.api + CONFIG.api_routes["get_" + media_type + "s"];
        }

        $scope.maxPerPage = setMaxPerPage(offset, limit, all_limit);
        $scope.currentOffset = offset;

        /* Get the media items to display on the page */
        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.media = response.data;
            $scope.media_resolved = true;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });

        if ($scope.maxPerPage != "All" && $scope.maxPerPage != "invalid")
        {
            /* Count all the media items */
            $http.get($scope.variables.get_count_url)
            .then(function(response) {
                var num_pages = Math.ceil(parseInt(response.data.num)/limit);
                $scope.pages = makePages(num_pages, offset, limit);
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }
    }

    /* Delete a media item for its ID */

    $scope.deleteMedia = function(mediaID)
    {
        if (confirm($scope.variables.delete_text)){
            var url = $scope.variables.delete_url + mediaID;

            $http.delete(url)
            .then(function(response) {
                messageCenterService.add(MESSAGE_OPTIONS.success, $scope.variables.media_type + " was deleted.", { timeout: CONFIG.messageTimeout });
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }
    };

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

    /* Pagination Functions */

    $scope.switchPage = function(page, path){
        $scope.goToPath(path + page.offset + "/" + page.limit);
    };

    $scope.skipPage = function(skip, pages, path){
        if(skip == "forward"){
            active_page = ($scope.current_page + 1 >= pages.length) ? pages.length : $scope.current_page + 1;
        }else if (skip == "backward"){
            active_page = ($scope.current_page - 1 <= 1) ? 1 : $scope.current_page - 1;
        }else if (skip == "forward_final"){
            active_page = pages.length;
        }else{
            active_page = 1;
        }
        var page = getPage(active_page, pages);
        $scope.goToPath(path + page.offset + "/" + page.limit);
    };

    $scope.selectMaxPerPage = function(offset, max){
        if (max == "All"){
            $scope.goToPath($scope.variables.path + 0 + "/" + 0);
        }else{
            var new_offset = Math.floor(offset/max) * max;
            $scope.goToPath($scope.variables.path + new_offset + "/" + max);
        }
    };

    /* Private Functions */

    var getPage = function(page_num, pages){
        return pages[page_num - 1];
    };

    var makePages = function(num_pages, offset, limit){

        $scope.current_page = (offset/limit) + 1;
        if (($scope.current_page + $scope.maxPages - 1) >= num_pages){
            var last_page = num_pages;
            var first_page = (num_pages - $scope.maxPages) + 1;
        }else{
            var first_page = $scope.current_page;
            var last_page = ($scope.current_page + $scope.maxPages) - 1;
        }

        var temp_pages = [];
        for(i = 1; i <= num_pages; i++){
            var page = {
                num: i,
                offset: (i - 1) * limit,
                limit: limit,
                active: i == $scope.current_page,
                visible: i >= first_page && i <= last_page,
                firstPage: i == first_page,
                lastPage: i == last_page
            };
            temp_pages.push(page);
        }
        return temp_pages;
    };

    var setMaxPerPage = function(offset, limit, all_limit){
        if (isValidLimit(limit) && isValidOffset(offset, limit)){
            if (limit == all_limit){
                return "All";
            }else{
                return limit;
            }
        }else{
            var new_limit = isValidLimit(limit) ? limit : 28;
            var new_offset = Math.floor(offset/new_limit) * new_limit;
            $scope.goToPath($scope.variables.path + new_offset + "/" + new_limit);
            return "invalid";
        }
    };

    var isValidLimit = function(limit){
        limit = parseInt(limit);
        return limit == 0 ? true : $scope.validLimits.includes(limit);
    };

    var isValidOffset = function(offset, limit){
        if (limit == 0){
            return offset == 0;
        }else{
            return (offset % limit == 0);
        }
    };

    $scope.user.$promise.then(init);

});
