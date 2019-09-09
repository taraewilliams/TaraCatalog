app.controller('ViewerListController', function($scope, RequestService, CONFIG, AuthService, $http)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }

        var status_appr = "approved";
        var status_pend = "pending";

        if($scope.isActive('/viewer_list')){
            $scope.variables = {
                get_url:CONFIG.api + CONFIG.api_routes.get_viewers_status,
                put_url:CONFIG.api + CONFIG.api_routes.update_viewer,
                delete_url:CONFIG.api + CONFIG.api_routes.delete_viewer,
                delete_text:"Delete this viewer?"
            };
        }else if ($scope.isActive('/view_list')){
            $scope.variables = {
                get_url:CONFIG.api + CONFIG.api_routes.get_can_view_status,
                put_url:CONFIG.api + CONFIG.api_routes.update_viewer,
                delete_url:CONFIG.api + CONFIG.api_routes.delete_viewer,
                delete_text:"Stop viewing this catalog?"
            };
        }

        if($scope.isActive('/viewer_list') || $scope.isActive('/view_list')){

            var approv_vars = {
                status:status_appr
            };

            RequestService.post($scope.variables.get_url, approv_vars, function(response) {
                $scope.viewers = response.data;
            }, function(response){
                console.log(response.data.message);
            });

            var pend_vars = {
                status:status_pend,
                requested_by:'creator'
            };

            RequestService.post($scope.variables.get_url, pend_vars, function(response) {
                $scope.pending_viewers_creator = response.data;
                console.log($scope.pending_viewers_creator.length);
            }, function(response){
                console.log(response.data.message);
            });

            pend_vars.requested_by = 'viewer';

            RequestService.post($scope.variables.get_url, pend_vars, function(response) {
                $scope.pending_viewers_viewer = response.data;
            }, function(response){
                console.log(response.data.message);
            });
        }
    }

    /* Delete viewer */
    $scope.deleteViewer = function(viewerID, creatorID)
    {
        if (confirm($scope.variables.delete_text)){
            var url = $scope.variables.delete_url + viewerID + "/" + creatorID;
            $http.delete(url)
            .then(function(response) {
                alert("The viewer was deleted.");
                location.reload();
            }, function(response){
                console.log("Error");
            });
        }
    };

    /* Update viewer */
    $scope.updateViewer = function(id, requested_by, status)
    {
        if ((requested_by == 'viewer' && $scope.isActive('/viewer_list')) || (requested_by == 'creator' && $scope.isActive('/view_list'))){
            var url = $scope.variables.put_url + id;
            var new_viewer = { status: status };

            RequestService.post(url, new_viewer, function(data) {
                location.reload();
            }, function(response){
                console.log(response);
            });
        }else{
            alert("You don't have permission to update.")
        }
    };

    $scope.user.$promise.then(init);

});
