# Movie Endpoints

## GET Requests

### movies/{id}

**Description:** Gets a single movie for a user for its ID and user ID.

**Input:** id (movie ID)

**Output:** Movie object

### movies

**Description:** Gets all movies for a user for the user ID.

**Input:** none

**Output:** Movie object array

### movies/todo/list/{todo}

**Description:** Gets all movies on the todo list or not on the todo list for a user.

**Input:** todo (0 or 1)

**Output:** Movie object array

### movies/limit/{offset}/{limit}

**Description:** Gets a set number of movies with a limit and an offset for a user.

**Input:** offset, limit

**Output:** Movie object array

### movies/order_by/{order}

**Description:** Gets all movies ordered by a specific field for a user.

**Input:** order (the field to order by)

**Output:** Movie object array

### movies/filter

**Description:** Gets movies that match the filter options for each field for a user.

**Input:** (optional) title, format, edition, content_type, mpaa_rating, location, season

**Output:** Movie object array

### movies/filter/{order}

**Description:** Gets movies that match the filter options for each field ordered by a specific field for a user.

**Input:** (required) order
        (optional) title, format, edition, content_type, mpaa_rating, location, season

**Output:** Movie object array

### movies/count/all

**Description:** Gets the count of all movies for a user.

**Input:** none

**Output:** Movie count

### movies/column_count/{column}

**Description:** Gets the count of all movies grouped by distinct column for a user.

**Input:** column name

**Output:** Movie counts

### movies/mpaa_rating_grouped/count

**Description:** Gets the count of all movies grouped by MPAA ratings under and over PG.

**Input:** none

**Output:** Movie counts

### movies/column_values/{column}

**Description:** Gets all distinct column values from all movies for a user.

**Input:** column name

**Output:** Array of column values

### movies/running_time/total

**Description:** Gets the total running time of all movies.

**Input:** none

**Output:** Total running time and time in hours

## POST Requests

### movies

**Description:** Creates a new movie.

**Input:** (required) title, format
        (optional) edition, content_type, mpaa_rating, location, season, todo_list, notes, image

**Output:** Movie object

### movies/{id}

**Description:** Updates a movie.

**Input:** (required) id (movie ID)
        (optional) title, format, edition, content_type, mpaa_rating, location, season, todo_list, notes, image

**Output:** true or false (success or failure)

## DELETE Requests

### movies/{id}

**Description:** Deletes a movie.

**Input:** id (movie ID)

**Output:** true or false (success or failure)
