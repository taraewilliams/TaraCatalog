app.controller('ResetPasswordController', function($scope, AuthService, Session, $http, CONFIG, RequestService)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }

        $scope.user_clone = angular.copy($scope.user);

    }

    $scope.updateUser = function(new_password, new_password_2){

        var new_user = {password: new_password};
        var url = CONFIG.api + CONFIG.api_routes.update_user + $scope.user.id;

        if ($scope.isEmpty(new_password) || $scope.isEmpty(new_password_2)){
            alert("Enter new password");
        } else if(new_password !== new_password_2){
            alert("Passwords do not match");
        }else{
            RequestService.post(url, new_user, function(data) {
                alert("Password was updated.");
                $scope.goToPath('/profile');
            }, function(response){
                alert(response.data.message);
            });

        }
    };

    $scope.user.$promise.then(init);
});
