app.controller('ListController', function($scope,
    $routeParams,
    CONFIG,
    $http,
    AuthService,
    $route,
    messageCenterService,
    MESSAGE_OPTIONS)
{
    /* The maximum number of pages to show in the pagination bar */
    var maxPages = 5;
    var currentPage = 0;

    /* The valid limits for number of items per page */
    var limitStart = 28;
    $scope.validLimits = [limitStart, limitStart * 2, limitStart * 3, limitStart * 4, "All"];

    var variables = {};

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        /* The limit for 'all' is set to 0 */
        var all_limit = 0;
        var offset = $routeParams.offset;
        var limit = $routeParams.limit;

        /* Get the media type from the URL */
        var media_string = $route.current.originalPath.split("/")[1];
        var media_type = media_string.substring(0, media_string.length - 1);

        /* Set the variables for the books/movies/games */
        $scope.path = "/" + media_type + "s/";

        var variables = {
            media_type:media_type,
            path: "/" + media_type + "s/",
            get_url: CONFIG.api + CONFIG.api_routes["get_" + media_type + "s_limit"] + offset + "/" + limit,
            get_count_url: CONFIG.api + CONFIG.api_routes["get_" + media_type + "_count"],
            delete_url: CONFIG.api + CONFIG.api_routes["delete_" + media_type],
            delete_text: "Delete this " + media_type + "?"
        };

        if (limit == all_limit){
            variables.get_url = CONFIG.api + CONFIG.api_routes["get_" + media_type + "s"];
        }

        $scope.maxPerPage = setMaxPerPage(offset, limit, all_limit);
        $scope.currentOffset = offset;

        /* Get the media items to display on the page */
        $http.get(variables.get_url)
        .then(function(response) {
            $scope.media = response.data;
            $scope.media_resolved = true;
        }, function(response){
            $scope.errorMessage(response.data.message, response.data.type);
        });

        if ($scope.maxPerPage != "All" && $scope.maxPerPage != "invalid")
        {
            /* Count all the media items */
            $http.get(variables.get_count_url)
            .then(function(response) {
                var num_pages = Math.ceil(parseInt(response.data.num)/limit);
                $scope.pages = makePages(num_pages, offset, limit);
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
            });
        }
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

    /* Delete a media item for its ID */
    $scope.deleteMedia = function(mediaID)
    {
        if (confirm(variables.delete_text)){
            var url = variables.delete_url + mediaID;

            $http.delete(url)
            .then(function(response) {
                $scope.successMessage(variables.media_type + " was deleted.");
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
            });
        }
    };

    /* Pagination Functions */

    $scope.switchPage = function(page, path){
        $scope.goToPath(path + page.offset + "/" + page.limit);
    };

    $scope.skipPage = function(skip, pages, path){
        var activePage = getNextPageForOption(skip, pages);
        var page = getPage(activePage, pages);
        $scope.goToPath(path + page.offset + "/" + page.limit);
    };

    $scope.selectMaxPerPage = function(offset, max){
        if (max === "All"){
            $scope.goToPath($scope.path + 0 + "/" + 0);
        }else{
            var new_offset = Math.floor(offset/max) * max;
            $scope.goToPath($scope.path + new_offset + "/" + max);
        }
    };

    /***************************************/
    /********** Private Functions **********/
    /***************************************/

    var getNextPageForOption = function(option, pages){
        var nextPages = {
            forward:(currentPage + 1 >= pages.length) ? pages.length : currentPage + 1,
            backward:(currentPage - 1 <= 1) ? 1 : currentPage - 1,
            forward_final:pages.length,
            backward_final:1
        };
        return nextPages[option];
    };

    var getPage = function(page_num, pages){
        return pages[page_num - 1];
    };

    var makePages = function(num_pages, offset, limit){

        /* Find current page */
        currentPage = getCurrentPage(offset, limit);

        /* Get first and last pages */
        if ((currentPage + maxPages - 1) >= num_pages){
            /* Less than max pages are left from current page to end */
            var first_page = (num_pages - maxPages) + 1;
            var last_page = num_pages;
        }else{
            var first_page = currentPage;
            var last_page = (currentPage + maxPages) - 1;
        }

        var temp_pages = [];
        for(i = 1; i <= num_pages; i++){
            var page = {
                num: i,
                offset: (i - 1) * limit,
                limit: limit,
                active: i === currentPage,
                visible: i >= first_page && i <= last_page,
                firstPage: i === first_page,
                lastPage: i === last_page
            };
            temp_pages.push(page);
        }
        return temp_pages;
    };

    /* Find current page */
    /* Ex: Limit 5, Offset 10, current page = 10/5 + 1 = 3 */
    var getCurrentPage = function(offset, limit){
        return (offset/limit) + 1;
    };

    /* Set the limit per page */
    /* If the limit or offset is invalid, set them to valid values */
    var setMaxPerPage = function(offset, limit, all_limit){
        if (isValidLimit(limit) && isValidOffset(offset, limit)){
            return ((limit === all_limit) ? "All" : limit);
        }else{
            var new_limit = isValidLimit(limit) ? limit : $scope.validLimits[0];
            var new_offset = Math.floor(offset/new_limit) * new_limit;
            $scope.goToPath($scope.path + new_offset + "/" + new_limit);
            return "invalid";
        }
    };

    var isValidLimit = function(limit){
        limit = parseInt(limit);
        return limit === 0 ? true : $scope.validLimits.includes(limit);
    };

    var isValidOffset = function(offset, limit){
        if (limit === 0){
            return offset === 0;
        }else{
            /* If remainder is not 0, offset is not divisible by limit */
            return (offset % limit === 0);
        }
    };

    /***************************************/
    /**************** Init *****************/
    /***************************************/

    $scope.user.$promise.then(init);

});
