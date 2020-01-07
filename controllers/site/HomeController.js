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
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });

    };

    $scope.getDisplayTitle = function(media){

        var displayTitle = "";

        if (media.type == 'book'){
            if (!$scope.isEmpty(media.series) && (media.series != media.title)){
                displayTitle = displayTitle + media.series + ": ";
            }

            displayTitle = displayTitle + media.title;

            if (!$scope.isEmpty(media.volume)){
                displayTitle = displayTitle + ", Volume " + media.volume;
            }
        }else if (media.type == 'movie'){
            if (!$scope.isEmpty(media.season)){
                displayTitle = displayTitle + media.title + ", " + media.season;
            }else{
                displayTitle = displayTitle + media.title;
            }
        }else{
            displayTitle = displayTitle + media.title;
        }

        return displayTitle;
    };

    init();

});
