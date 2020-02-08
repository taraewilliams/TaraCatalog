app.controller('UserUpdateController', function($scope,
    AuthService,
    $http,
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

        $http.get(CONFIG.api + CONFIG.api_routes.get_single_user + $scope.user.id)
        .then(function(response) {
            $scope.user_orig = response.data;
            $scope.user_clone = $scope.clone($scope.user_orig);
        }, function(error){
            $scope.errorMessage(response.data.message, response.data.type);
        });
    }

    $scope.updateUser = function(user_clone){

        var new_user = removeNotUpdatedFields(user_clone, $scope.user_orig);
        var url = CONFIG.api + CONFIG.api_routes.update_user + $scope.user_orig.id;

        if (!$scope.isEmptyObj(new_user)){
            RequestService.post(url, new_user, function(data) {
                $scope.successMessage("User was updated.");
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
            });
        }else{
            $scope.errorMessage("No changes made.", MESSAGE_OPTIONS.info);
        }
    };

    $scope.hasImageChanged = function(image){
        return ($scope.user_orig.image != image);
    };

    var removeNotUpdatedFields = function(user_clone, user){
        var new_user = {};
        for (var prop in user_clone) {
            if(!user_clone.hasOwnProperty(prop)) continue;
            if(user_clone[prop] != user[prop]){
                new_user[prop] = user_clone[prop];
            }
        }
        return new_user;
    };

    $scope.user.$promise.then(init);
});
