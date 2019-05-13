app.controller('ProfileController', function($scope, AuthService, Session, $http, CONFIG, RequestService)
{
    /* Redirect if not logged in */
    if( AuthService.redirectOnUnauthorized() ) {
        return;
    }

    function init(){

        $http.get(CONFIG.api + '/users/' + Session.userID)
        .then(function(response) {
            $scope.user = response.data;
            $scope.user_clone = $scope.clone($scope.user);
        });

        $http.get(CONFIG.api + '/viewers')
        .then(function(response) {
            $scope.viewers = response.data;
        });

        $http.get(CONFIG.api + '/viewers/view/list')
        .then(function(response) {
            $scope.views = response.data;
        });

    }

    /* Delete item */
    $scope.deleteUser = function(userID){

        if (confirm("Delete your profile?")){
            var url = CONFIG.api + "/users/" + userID;

            $http.delete(url)
            .then(function(response) {
                alert("The user was deleted.");
                $scope.goToPath("/login");
            }, function(response){
                console.log("Error");
            });
        }
    };

    init();
});
