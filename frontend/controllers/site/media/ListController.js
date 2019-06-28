app.controller('ListController', function($scope, $routeParams, RequestService, CONFIG, $http, AuthService)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        /* The maximum number of pages to show in the pagination bar */
        $scope.maxPages = 5;

        /* The limit for 'all' is set to 0 */
        var all_limit = 0;
        var offset = $routeParams.offset;
        var limit = $routeParams.limit;

        /* Set the variables for the books/movies/games */
        if ($scope.isActive('/books/:offset/:limit')){
            $scope.variables = {
                path: "/books/",
                get_url: CONFIG.api + '/books/limit/' + offset + "/" + limit,
                get_count_url: CONFIG.api + '/books/count/all',
                delete_url: CONFIG.api + '/books/',
                delete_text: "Delete this book?"
            };
            if (limit == all_limit){
                $scope.variables.get_url = CONFIG.api + '/books';
            }
        } else if ($scope.isActive('/movies/:offset/:limit')) {
            $scope.variables = {
                path: "/movies/",
                get_url: CONFIG.api + '/movies/limit/' + offset + "/" + limit,
                get_count_url: CONFIG.api + '/movies/count/all',
                delete_url: CONFIG.api + '/movies/',
                delete_text: "Delete this movie?"
            };
            if (limit == all_limit){
                $scope.variables.get_url = CONFIG.api + '/movies';
            }
        }else{
            $scope.variables = {
                path: "/games/",
                get_url: CONFIG.api + '/games/limit/' + offset + "/" + limit,
                get_count_url: CONFIG.api + '/games/count/all',
                delete_url: CONFIG.api + '/games/',
                delete_text: "Delete this game?"
            };
            if (limit == all_limit){
                $scope.variables.get_url = CONFIG.api + '/games';
            }
        }

        $scope.maxPerPage = setMaxPerPage(offset, limit, all_limit);
        $scope.currentOffset = offset;

        /* Get the media items to display on the page */
        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.media = response.data;
        });

        if ($scope.maxPerPage != "all" && $scope.maxPerPage != "invalid"){
            /* Count all the media items */
            $http.get($scope.variables.get_count_url)
            .then(function(response) {
                var num_pages = Math.ceil(parseInt(response.data.num)/limit);
                $scope.pages = makePages(num_pages, offset, limit);
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
                console.log("Success");
            }, function(response){
                console.log("Error");
            });
        }
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
        if (max == "all"){
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
                return "all";
            }else{
                return limit;
            }
        }else{
            var new_limit = 25;
            var new_offset = Math.floor(offset/new_limit) * new_limit;
            $scope.goToPath($scope.variables.path + new_offset + "/" + new_limit);
            return "invalid";
        }
    };

    var isValidLimit = function(limit){
        return (limit == 0 || limit == 25 || limit == 50 || limit == 75 || limit == 100);
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
