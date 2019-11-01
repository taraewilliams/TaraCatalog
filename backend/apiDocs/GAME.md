# Game Endpoints

## GET Requests

### games/{id}

**Description:** Gets a single game for a user for its ID and user ID.

**Input:** id (game ID)

**Output:** Game object

### games

**Description:** Gets all games for a user for the user ID.

**Input:** none

**Output:** Game object array

### games/todo/list/{todo}

**Description:** Gets all games on the todo list or not on the todo list for a user.

**Input:** todo (0 or 1)

**Output:** Game object array

### games/limit/{offset}/{limit}

**Description:** Gets a set number of games with a limit and an offset for a user.

**Input:** offset, limit

**Output:** Game object array

### games/order_by/{order}

**Description:** Gets all games ordered by a specific field for a user.

**Input:** order (the field to order by)

**Output:** Game object array

### games/filter

**Description:** Gets games that match the filter options for each field for a user.

**Input:** (optional) title, platform, location, esrb_rating, genre

**Output:** Game object array

### games/filter/{order}

**Description:** Gets games that match the filter options for each field ordered by a specific field for a user.

**Input:** (required) order
        (optional) title, platform, location, esrb_rating, genre

**Output:** Game object array

### games/count/all

**Description:** Gets the count of all games for a user.

**Input:** none

**Output:** Game count

### games/column_count/{column}

**Description:** Gets the count of all games grouped by distinct column for a user.

**Input:** column name

**Output:** Game counts

### games/column_values/{column}

**Description:** Gets all distinct column values from all games for a user.

**Input:** column name

**Output:** Array of column values


## POST Requests

### games

**Description:** Creates a new game.

**Input:** (required) title
        (optional) platform, location, todo_list, esrb_rating, notes, image, genre

**Output:** Game object

### games/{id}

**Description:** Updates a game.

**Input:** (required) id (game ID)
        (optional) title, platform, location, todo_list, esrb_rating, notes, image, genre

**Output:** true or false (success or failure)


## DELETE Requests

### games/{id}

**Description:** Deletes a game.

**Input:** id (game ID)

**Output:** true or false (success or failure)
