# Book Endpoints

## GET Requests

### books/{id}

**Description:** Gets a single book for a user for its ID and user ID.

**Input:** id (book ID)

**Output:** Book object

### books

**Description:** Gets all books for a user for the user ID.

**Input:** none

**Output:** Book object array

### books/todo/list/{todo}

**Description:** Gets all books on the todo list or not on the todo list for a user.

**Input:** todo (0 or 1)

**Output:** Book object array

### books/limit/{offset}/{limit}

**Description:** Gets a set number of books with a limit and an offset for a user.

**Input:** offset, limit

**Output:** Book object array

### books/order_by/{order}

**Description:** Gets all books ordered by a specific field for a user.

**Input:** order (the field to order by)

**Output:** Book object array

### books/filter

**Description:** Gets books that match the filter options for each field for a user.

**Input:** (optional) title, author, volume, isbn, cover_type, content_type, location, genre

**Output:** Book object array

### books/filter/{order}

**Description:** Gets books that match the filter options for each field ordered by a specific field for a user.

**Input:** (required) order
        (optional) title, author, volume, isbn, cover_type, content_type, location, genre

**Output:** Book object array

### books/count/all

**Description:** Gets the count of all books for a user.

**Input:** none

**Output:** Book count

### books/column_count/{column}

**Description:** Gets the count of all books grouped by distinct column for a user.

**Input:** column name

**Output:** Book counts

### books/column_values/{column}

**Description:** Gets all distinct column values from all books for a user.

**Input:** column name

**Output:** Array of column values

## POST Requests

### books

**Description:** Creates a new book.

**Input:** (required) title
        (optional) author, volume, isbn, cover_type, content_type, notes, location, todo_list, image, genre

**Output:** Book object

### books/{id}

**Description:** Updates a book.

**Input:** (required) id (book ID)
        (optional) title, author, volume, isbn, cover_type, content_type, notes, location, todo_list, image, genre

**Output:** true or false (success or failure)


## DELETE Requests

### books/{id}

**Description:** Deletes a book.

**Input:** id (book ID)

**Output:** true or false (success or failure)
