# Auth Endpoints

## POST Requests

### auth/login

**Description:** Logs a user in to the website.

**Input:** username, password

**Output:** Session object


### auth/logout

**Description:** Logs a user out of the website by destroying the session.

**Input:** session_id

**Output:** true or false (success or failure)
