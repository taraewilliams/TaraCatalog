app.controller('LogoutController', function($location, AuthService)
{
    function init() {
        AuthService.logout();
    }
    init();

});
