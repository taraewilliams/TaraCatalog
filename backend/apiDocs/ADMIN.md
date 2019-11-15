# Admin Endpoints

## Image Requests

### GET Request

#### admin/images

**Description:** Gets unused media images.

**Input:** none

**Output:** Array of image urls

### DELETE Request

#### admin/images

**Description:** Delete unused media images

**Input:** none

**Output:** Array of image urls

## User Requests

### GET Requests

#### admin/users

**Description:** Gets all active users

**Input:** none

**Output:** User object array

#### admin/users/inactive

**Description:** Gets all inactive users

**Input:** none

**Output:** User object array

### POST Requests

#### admin/users

**Description:** Create a user

**Input:** (required) username, password, email
        (optional) first_name, last_name, color_scheme, role

**Output:** User object

#### admin/users/{id}

**Description:** Update user admin field

**Input:** (required) id (user ID), is_admin

**Output:** User object

### DELETE Request

#### admin/users/{id}

**Description:** Delete a user

**Input:** (required) id (user ID)

**Output:** true or false (success or failure)
