app.controller('AdminRegisterController', function($scope,
    AuthService,
    RequestService,
    CONFIG,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    function init() {

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnNonAdmin() ) {
            return;
        }

        $scope.new_user = {
          username:'',
          password:'',
          email:'',
          first_name:'',
          last_name:'',
          color_scheme:'red',
          role:'viewer',
          image:''
        };
    }

    $scope.createUser = function(new_user) {

        var url = CONFIG.api + CONFIG.api_routes.create_user_admin;

        RequestService.post(url, new_user, function(data) {
            messageCenterService.add(MESSAGE_OPTIONS.success, "User was created.", { timeout: CONFIG.messageTimeout });
        }, function(response) {
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });

    };

    $scope.user.$promise.then(init);

});
