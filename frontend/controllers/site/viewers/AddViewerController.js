app.controller('AddViewerController', function($scope, RequestService, CONFIG, AuthService, $http)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }

        $scope.addedUsers = [];

        if($scope.isActive('/add_viewers')){
            $scope.variables = {
                get_url:CONFIG.api + CONFIG.api_routes.get_users_not_viewing,
                post_url: CONFIG.api + CONFIG.api_routes.create_viewer,
                redirect_url: "/viewer_list"
            };
        }else{
            $scope.variables = {
                get_url:CONFIG.api + CONFIG.api_routes.get_users_cant_view,
                post_url:CONFIG.api + CONFIG.api_routes.create_viewer,
                redirect_url: "/view_list"
            };
        }

        $http.get($scope.variables.get_url)
        .then(function(response) {
            $scope.users = response.data;
        });
    }


    /* Add or remove users from list to update */
    $scope.toggleAddedUsers = function(new_id){
        var id = parseInt(new_id);
        var index = $scope.addedUsers.indexOf(id);
        if (index > -1){
            $scope.addedUsers.splice(index,1);
        }else{
            $scope.addedUsers.push(id);
        }
    };

    /* Add users to view list */
    $scope.addToViewers = function(id_list){

        for (i = 0; i < id_list.length; i++){
            var id = id_list[i];

            update_num = 0;

            if($scope.isActive('/add_viewers')){
                var new_viewer = {
                    creator_id:$scope.user.id,
                    viewer_id:id
                };
            }else{
                var new_viewer = {
                    creator_id:id,
                    viewer_id:$scope.user.id
                };
            }

            RequestService.post($scope.variables.post_url, new_viewer, function(data) {
                /* Redirect to the viewers page once all viewers are updated */
                update_num = update_num + 1;
                if (update_num == id_list.length){
                    $scope.goToPath($scope.variables.redirect_url);
                }
            }, function(error, status){
                console.log(error.message);
            });
        }
    };

    $scope.user.$promise.then(init);

});
