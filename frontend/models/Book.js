app.factory('Book', function($resource, CONFIG)
{
    var resource = CONFIG.api + '/books/:id';

    return $resource(resource, { id: '@id' }, {
        update: {
            method: 'PUT'
        }
    });
});
