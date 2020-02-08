app.controller('ResetPasswordController', function($scope,
    AuthService,
    CONFIG,
    RequestService,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }
    }

    $scope.updateUser = function(new_password, new_password_2){

        var new_user = { password: new_password };
        var url = CONFIG.api + CONFIG.api_routes.update_user + $scope.user.id;

        if ($scope.isEmpty(new_password) || $scope.isEmpty(new_password_2)){
            $scope.errorMessage("Enter new password", MESSAGE_OPTIONS.warning);
        } else if(new_password !== new_password_2){
            $scope.errorMessage("Passwords do not match", MESSAGE_OPTIONS.warning);
        }else{
            RequestService.post(url, new_user, function(data) {
                alert("Password was updated.");
                $scope.goToPath('/profile');
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
            });
        }
    };

    $scope.user.$promise.then(init);
});
