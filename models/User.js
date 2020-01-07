app.factory('User', function($resource, CONFIG)
{
    var resource = CONFIG.api + '/users/:id';
    return $resource(resource, { id: '@id' });
});
