app.controller('RegisterController', function($scope,
    AuthService,
    RequestService,
    CONFIG,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    function init() {

        if( AuthService.redirectOnAuthorized() ) {
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

        var url = CONFIG.api + CONFIG.api_routes.create_user;

        RequestService.post(url, new_user, function(data) {
            messageCenterService.add(MESSAGE_OPTIONS.success, "Profile was created.", { timeout: CONFIG.messageTimeout });
            $scope.goToPath('/login');

        }, function(response) {
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });

    };

    init();

});
