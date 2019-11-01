# Viewer Endpoints

## GET Requests

### viewers/list/{status}

**Description:** Gets all viewers for a creator for a specific status (approved, pending, rejected).

**Input:** status

**Output:** Viewer object array (with viewer and creator usernames and images)

### viewers/view/list/{status}

**Description:** Gets all a creator can view for a specific status (approved, pending rejected).

**Input:** status

**Output:** Viewer object array (with viewer and creator usernames and images)

### viewers/{creator_id}

**Description:** Gets a single viewer for creator ID and viewer ID.

**Input:** creator_id

**Output:** Viewer object

## POST Requests

### viewers

**Description:** Creates a new viewer and sets the status based on whether the user ID is a creator ID (approved) or viewer ID (pending).

**Input:** creator_id, viewer_id

**Output:** Viewer object

### viewers/{id}

**Description:** Updates a viewer.

**Input:** id (viewer object ID), status

**Output:** true or false (success or failure)

## DELETE Requests

### viewers/{viewer_id}/{creator_id}

**Description:** Deletes a viewer for the viewer ID and creator ID. Either a viewer or creator can delete the relationship.

**Input:** viewer_id, creator_id

**Output:** true or false (success or failure)
