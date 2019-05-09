app.controller('ListController', function($scope, $routeParams, RequestService, CONFIG, $http, AuthService)
{
    /* Redirect if not logged in */
    if( AuthService.redirectOnUnauthorized() ) {
        return;
    }

    /* The maximum number of pages to show in the pagination bar */
    $scope.maxPages = 5;

    function init(){

        var offset = $routeParams.offset;
        var limit = $routeParams.limit;

        /* Set the maximum number of items per page */
        if (limit == 0){
            $scope.maxPerPage = "all";
        }else{
            $scope.maxPerPage = limit;
        }
        /* Set the current page offset */
        $scope.currentOffset = offset;

        /* Set the variables for the books/movies/games */
        if ($scope.isActive('/books/:offset/:limit')){
            $scope.variables = {
                path: "/books/",
                get_url: CONFIG.api + '/books/limit/' + offset + "/" + limit,
                get_count_url: CONFIG.api + '/books/count/all',
                delete_url: CONFIG.api + '/books/',
                delete_text: "Delete this book?"
            };
            if (offset == 0 && limit == 0){
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
            if (offset == 0 && limit == 0){
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
            if (offset == 0 && limit == 0){
                $scope.variables.get_url = CONFIG.api + '/games';
            }
        }

        /* Get the items to display on the page */
        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.items = response.data;
        });

        if ($scope.maxPerPage != "all"){

            /* Count all the items */
            $http.get($scope.variables.get_count_url)
            .then(function(response) {
                var num_items = parseInt(response.data.num);
                var num_pages = Math.ceil(num_items/limit);

                var current_page = (offset/limit) + 1;
                if ((current_page + $scope.maxPages - 1) >= num_pages){
                    var last_page = num_pages;
                    var first_page = (num_pages - $scope.maxPages) + 1;
                }else{
                    var first_page = current_page;
                    var last_page = (current_page + $scope.maxPages) - 1;
                }

                $scope.pages = [];
                for(i = 1; i <= num_pages; i++){
                    var page = {
                        num: i,
                        offset: (i - 1) * limit,
                        limit: limit,
                        active: i == current_page,
                        current: i >= first_page && i <= last_page,
                        firstPage: i == first_page,
                        lastPage: i == last_page
                    };
                    $scope.pages.push(page);
                }
            });
        }
    }

    /* Delete an item for its ID */
    $scope.deleteItem = function(itemID){

        if (confirm($scope.variables.delete_text)){
            var url = $scope.variables.delete_url + itemID;

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

    $scope.skipOnePage = function(skip, pages, path){
        var active_page = getActivePage(pages);

        if(skip == "forward"){
            active_page = (active_page + 1 >= pages.length) ? pages.length : active_page + 1;
        }else{
            active_page = (active_page - 1 <= 1) ? 1 : active_page - 1;
        }
        var page = getPage(active_page, pages);
        $scope.goToPath(path + page.offset + "/" + page.limit);
    };

    $scope.skipToFinalPage = function(skip, pages, path){
        if(skip == "forward"){
            var page = getPage(pages.length, pages);
        }else{
            var page = getPage(1, pages);
        }
        $scope.goToPath(path + page.offset + "/" + page.limit);
    };

    $scope.selectMaxPerPage = function(offset, max){
        if (max == "all"){
            $scope.goToPath($scope.variables.path + 0 + "/" + 0);
        }else{
            $scope.goToPath($scope.variables.path + offset + "/" + max);
        }
    };

    /* Private Functions */

    var getActivePage = function(pages){
        for (i = 0; i < pages.length; i++){
            var page = pages[i];
            if (page.active){
                return page.num;
            }
        }
    };

    var getPage = function(page_num, pages){
        for (i = 0; i < pages.length; i++){
            var page = pages[i];
            if (page.num == page_num){
                return page;
            }
        }
    };

    init();

});
