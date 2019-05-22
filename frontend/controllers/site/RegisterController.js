app.controller('RegisterController', function($scope, AuthService, $location, RequestService, CONFIG)
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

        var url = CONFIG.api + "/users";

        RequestService.post(url, new_user, function(data) {
            alert("Profile was created.");
            $scope.goToPath('/login');

        }, function(response) {
            alert(response.data.message);
        });

    };

    init();

});
