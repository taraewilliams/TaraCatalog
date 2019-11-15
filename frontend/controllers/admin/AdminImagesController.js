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

    $scope.user.$promise.then(init);

});
