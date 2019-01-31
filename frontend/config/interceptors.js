// app.config(function($httpProvider) {
//     $httpProvider.interceptors.push(function($rootScope, $q, AUTH_EVENTS, Session)
//     {
//         var interceptor = {};
//
//         interceptor.request = function(config) {
//             if( !!Session.id ) {
//                 if (config.method == 'GET' || config.method == 'DELETE') {
//                     config.params = {};
//                     config.params.session_id = Session.id;
//                     config.params.session_token = Session.token;
//                 } else {
//                     config.params = {};
//                     config.params.session_id = Session.id;
//                     config.params.session_token = Session.token;
//
//                     config.data.session_id = Session.id;
//                     config.data.session_token = Session.token;
//                 }
//             }
//             return config;
//         };
//
//         interceptor.requestError = function(rejection) {
//             return $q.reject(rejection);
//         };
//
//         interceptor.response = function(response) {
//             return response;
//         };
//
//         interceptor.responseError = function(rejection) {
//             // Unauthorized
//             if(rejection.status === 401) {
//                 $rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
//             }
//
//             return $q.reject(rejection);
//         };
//
//         return interceptor;
//     });
// });
