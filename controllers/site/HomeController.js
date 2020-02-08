app.controller('HomeController', function($scope,
    RequestService,
    CONFIG,
    AuthService,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }

        $scope.searchTerm = "";
    }

    $scope.search = function(searchTerm){

        var search = { search: searchTerm };

        RequestService.post(CONFIG.api + CONFIG.api_routes.get_media_search, search, function(response) {
            $scope.items = response.data;
        }, function(response){
            $scope.errorMessage(response.data.message, response.data.type);
        });
    };

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

    $scope.getImageAlt = function(itemType){
        var alts = {
            book:"Book Cover",
            movie:"Movie Cover",
            game:"Game Cover"
        };
        return alts[itemType];
    };

    $scope.getImageLink = function(itemType){
        var srcs = {
            book: '/TaraCatalog/assets/images/book_saver.jpg',
            movie: '/TaraCatalog/assets/images/movie_saver.jpg',
            game: '/TaraCatalog/assets/images/game_saver.jpg'
        };
        return srcs[itemType];
    };

    $scope.user.$promise.then(init);
});
