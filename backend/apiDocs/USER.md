# User Endpoints

## GET Requests

### users/{id}

**Description:** Gets a single user for its ID.

**Input:** id (user ID)

**Output:** User object

### users/{username}/{password}

**Description:** Gets a single user for its username and password.

**Input:** username, password

**Output:** User object

### users/non/viewers/all

**Description:** Gets users that are not viewing a creator's catalog.

**Input:** none

**Output:** User object array (with id, username, and image)

### users/non/views/all

**Description:** Gets users whose catalogs a creator can't view.

**Input:** none

**Output:** User object array (with id, username, and image)

## POST Requests

### users

**Description:** Creates a new user.

**Input:** (required) username, password, email
        (optional) first_name, last_name, color_scheme, role, image

**Output:** User object

### users/{id}

**Description:** Updates a user.

**Input:** (required) id (user ID)
        (optional) username, password, email, first_name, last_name, color_scheme, role, image

**Output:** true or false (success or failure)

## DELETE Requests

### users/{id}

**Description:** Deletes a user.

**Input:** id (user ID)

**Output:** true or false (success or failure)
