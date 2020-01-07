app.constant('CONFIG', {
    api: '/TaraCatalog/backend/public/api/v1',
    homeTemplate: 'views/site/home.html',
    messageTimeout: 5000,
    api_routes: {
        /* admin */
        get_unused_images: '/admin/images',
        delete_unused_images: '/admin/images',
        get_users: '/admin/users',
        get_inactive_users: '/admin/users/inactive',
        create_user_admin: '/admin/users',
        update_user_admin: '/admin/users/',
        delete_user_admin: '/admin/users/',
        /* auth */
        login: '/auth/login',
        logout: '/auth/logout',
        /* viewer media */
        get_books_viewer: '/book_viewers/',
        get_movies_viewer: '/movie_viewers/',
        get_games_viewer: '/game_viewers/',
        /* books */
        get_single_book: '/books/',
        get_books: '/books',
        get_books_todo: '/books/todo/list/',
        get_books_limit: '/books/limit/',
        get_books_order: '/books/order_by/',
        get_books_filter: '/books/filter',
        get_books_filter_order: '/books/filter/',
        get_book_count: '/books/count/all',
        get_book_column_count: '/books/column_count/',
        get_book_column_value_count: '/books/value_count/',
        get_book_column_values: '/books/column_values/',
        create_book: '/books',
        update_book: '/books/',
        delete_book: '/books/',
        /* movies */
        get_single_movie: '/movies/',
        get_movies: '/movies',
        get_movies_todo: '/movies/todo/list/',
        get_movies_limit: '/movies/limit/',
        get_movies_order: '/movies/order_by/',
        get_movies_filter: '/movies/filter',
        get_movies_filter_order: '/movies/filter/',
        get_movie_count: '/movies/count/all',
        get_movie_column_count: '/movies/column_count/',
        get_movie_column_values: '/movies/column_values/',
        get_movie_mpaa_count_grouped: '/movies/mpaa_rating_grouped/count',
        get_movie_running_time_total: '/movies/running_time/total',
        create_movie: '/movies',
        update_movie: '/movies/',
        delete_movie: '/movies/',
        /* games */
        get_single_game: '/games/',
        get_games: '/games',
        get_games_todo: '/games/todo/list/',
        get_games_limit: '/games/limit/',
        get_games_order: '/games/order_by/',
        get_games_filter: '/games/filter',
        get_games_filter_order: '/games/filter/',
        get_game_count: '/games/count/all',
        get_game_column_count: '/games/column_count/',
        get_game_column_values: '/games/column_values/',
        create_game: '/games',
        update_game: '/games/',
        delete_game: '/games/',
        /* media */
        get_media_location_count: '/media/location/count',
        /* search */
        get_media_search: '/search',
        get_media_search_viewer: '/search/',
        /* user */
        get_single_user: '/users/',
        get_single_user_username_password: '/users/',
        get_users_not_viewing: '/users/non/viewers/all',
        get_users_cant_view: '/users/non/views/all',
        update_user: '/users/',
        delete_user: '/users/',
        /* viewers */
        get_viewers_status: '/viewers/list',
        get_can_view_status: '/viewers/view_list',
        get_single_viewer: '/viewers/',
        create_viewer: '/viewers',
        update_viewer: '/viewers/',
        delete_viewer: '/viewers/'
    }
});