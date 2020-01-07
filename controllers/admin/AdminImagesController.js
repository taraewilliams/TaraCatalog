app.controller('AdminImagesController', function($scope,
    $http,
    CONFIG,
    AuthService,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnNonAdmin() ) {
            return;
        }

        $http.get(CONFIG.api + CONFIG.api_routes.get_unused_images)
        .then(function(response) {
            $scope.images = response.data;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });
    }

    /* Delete unused images */
    $scope.deleteImages = function(){

        if (confirm("Delete all unused images?")){

            var url = CONFIG.api + CONFIG.api_routes.delete_unused_images;

            $http.delete(url)
            .then(function(response) {
                messageCenterService.add(MESSAGE_OPTIONS.success, "Images were deleted.", { timeout: CONFIG.messageTimeout });
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }
    };

    $scope.user.$promise.then(init);

});
