<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;
use TaraCatalog\Service\APIService;

class Media
{

    /* ========================================================== *
    * GET
    * ========================================================== */

    /* ========================================================== *
    * GET MEDIA
    * ========================================================== */

    /* Get a single media item */
    public static function get_from_id($user_id, $id, $table)
    {
        $where = array("id" => $id, "user_id" => $user_id);
        $result = DatabaseService::get($table, $where);
        if($result === false || $result === null || count($result) === 0) {
            APIService::response_fail("There was a problem getting the media.", 500);
        }
        return Media::build_media_item($table, $result[0]);
    }

    /* Get all media items */
    public static function get_all($user_id, $table, $order_by="")
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $result = DatabaseService::get_where_order_limit($table, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the media.", 500);
        }else{
            return Media::build_media_array($table, $result);
        }
    }

    /* Get all media items on the to do list */
    public static function get_all_on_todo_list($user_id, $todo, $table, $order_by="")
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id . " AND todo_list = " . $todo;
        $result = DatabaseService::get_where_order_limit($table, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the media.", 500);
        }else{
            return Media::build_media_array($table, $result);
        }
    }

    /* Get a set number of media items */
    public static function get_all_with_limit($user_id, $table, $order_by="", $offset = 0, $limit = 50)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $limit_sql = "LIMIT " . $offset . ", " . $limit;
        $result = DatabaseService::get_where_order_limit($table, $where, $order_by, $limit_sql);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the media.", 500);
        }else{
            return Media::build_media_array($table, $result);
        }
    }

    /* Get all media items ordered by a specific field */
    public static function get_all_with_order($user_id, $table, $order)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $order_by = "ORDER BY ". $order;
        $result = DatabaseService::get_where_order_limit($table, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the media.", 500);
        }else{
            return Media::build_media_array($table, $result);
        }
    }

    /* Get media for search parameters */
    /* AND for filter by specific column */
    /* OR for search all columns */
    public static function get_for_search($user_id, $table, $data, $order_by="", $enum_keys=array(), $conj="AND")
    {
        $where = "WHERE (";
        $iter = 1;
        foreach ($data as $key => $value) {
            $conj_full = ($iter == 1) ? "" : " " . $conj . " ";
            $equality = in_array($key, $enum_keys) ? " = '" . $data[$key] . "'" : " LIKE '%" . $data[$key] . "%'";
            $where = $where . (isset($data[$key]) ? $conj_full . $key . $equality : "");
            $iter += 1;
        }

        $where = $where . ") AND active = 1 AND user_id = " . $user_id;
        $result = DatabaseService::get_where_order_limit($table, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the media.", 500);
        }else{
            return Media::build_media_array($table, $result);
        }
    }

    /* ========================================================== *
    * GET MEDIA COUNTS
    * ========================================================== */

    /* Count all media */
    public static function count_media($user_id, $table)
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num FROM " . $table . " WHERE active = 1 AND user_id = " . $user_id;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem counting the media.", 500);
        }else{
            return $result;
        }
    }

    /* Get counts grouped by a column */
    public static function get_counts_for_column($table, $user_id, $column_name, $header = "counts")
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num, " . $column_name . " as type FROM " . $table . " WHERE active = 1 AND " . $column_name . " IS NOT NULL AND " . $column_name . " <> '' AND user_id = " . $user_id . " GROUP BY " . $column_name;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false){
            return false;
        }else{
            return array($header => $result);
        }
    }

    /* Count all media locations (grouped by location) */
    public static function get_all_media_location_counts($user_id){
        $column_name = "location";
        $header = "media_locations";

        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num, " . $column_name . " as type, 'book' as media FROM " . CONFIG::DBTables()->book . " WHERE active = 1 AND " . $column_name . " IS NOT NULL AND user_id = " . $user_id . " GROUP BY " . $column_name;
        $sql .= " UNION SELECT COUNT(*) as num, " . $column_name . " as type, 'movie' as media FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 AND " . $column_name . " IS NOT NULL AND user_id = " . $user_id . " GROUP BY " . $column_name;
        $sql .= " UNION SELECT COUNT(*) as num, " . $column_name . " as type, 'game' as media FROM " . CONFIG::DBTables()->game . " WHERE active = 1 AND " . $column_name . " IS NOT NULL AND user_id = " . $user_id . " GROUP BY " . $column_name;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false || $result === null){
            APIService::response_fail("There was a problem getting the counts.", 500);
        }else{
            return array($header => $result);
        }
    }

    /* ========================================================== *
    * GET ALL DISTINCT VALUES FOR A COLUMN
    * ========================================================== */

    /* Get all distinct values for a column name */
    public static function get_distinct_for_column($user_id, $table, $column_name)
    {
        $database = Database::instance();
        $sql = "SELECT DISTINCT " . $column_name . " FROM " . $table . " WHERE active = 1 AND user_id = " . $user_id . " AND " . $column_name . " IS NOT NULL AND " . $column_name . " <> ''" . " ORDER BY " . $column_name;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the " . $column_name . "s.", 500);
        }else{
            return $result;
        }
    }

    /* ========================================================== *
    * GET A SINGLE VALUE FOR A COLUMN
    * ========================================================== */

    /* Get a single value for a column for its ID */
    public static function get_column_value_for_id($user_id, $id, $table, $column_name)
    {
        $database = Database::instance();
        $sql = "SELECT " . $column_name . " FROM " . $table . " WHERE active = 1 AND user_id = " . $user_id . " AND id = " . $id;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the " . $column_name . ".", 500);
        }else{
            return $result[$column_name];
        }
    }


    /* ========================================================== *
    * UPDATE
    * ========================================================== */

    /* Update a media item */
    public static function update($user_id, $id, $data, $table)
    {
        /* If new image is set, delete the old image */
        if (isset($data["image"])){
            $database = Database::instance();
            $sql = "SELECT image FROM " . $table . " WHERE active = 1 AND id = " . $id . " AND user_id = " . $user_id;
            $query = $database->prepare($sql);
            $query->execute();
            $result = $query->fetch(\PDO::FETCH_ASSOC);
            $query->closeCursor();
            if( $result !== false && $result !== null && $result !== "" ) {
                $old_image = $result["image"];
                FileService::delete_file($old_image);
            }
        }

        /* Update media item */
        $result = DatabaseService::update($table, $id, $data);
        if ($result === false || $result === null){
            APIService::response_fail("Update failed.", 500);
        }
        return $result ? (Media::get_from_id($user_id, $id, $table)) : false;
    }

    /* ========================================================== *
    * DELETE
    * ========================================================== */

    /* Delete a media item */
    public static function set_active($id, $active, $table)
    {
        $result = DatabaseService::set_active($table, $id, $active);
        if( $result === false || $result === null) {
            APIService::response_fail("There was an error deleting the " . $table, 500);
        }
        return $result;
    }


    /* ===================================================== *
    * Public Functions
    * ===================================================== */

    /* Check if an enum value to be set is valid */
    public static is_valid_enum($enum, $value){
        foreach ($enum as $key => $enum_value) {
            if ($enum_value == $value){
                return true;
            }
        }
        return false;
    }

    /* Set image */
    public static function set_image($files, $file_prefix, $directory)
    {
        $dir = FileService::MAIN_DIR . $directory;
        $file_name = FileService::upload_file($files['image'], $dir, $file_prefix);
        if(!$file_name) {
            APIService::response_fail("There was an error saving the picture.");
        }
        return $file_name;
    }

    /* Get unused images */
    public static function get_unused_images(){

        $books = Media::get_unused_images_for_table(CONFIG::DBTables()->book);
        $movies = Media::get_unused_images_for_table(CONFIG::DBTables()->movie);
        $games = Media::get_unused_images_for_table(CONFIG::DBTables()->game);
        $users = Media::get_unused_images_for_table(CONFIG::DBTables()->user);

        $images = array(
            "books"     => $books,
            "movies"    => $movies,
            "games"     => $games,
            "users"     => $users
        );

        return $images;
    }

    /* Get unused images for table */
    public static function get_unused_images_for_table($table)
    {
        /* Get media images in database */
        $media_images = Media::get_media_images();

        /* Get media images currently stored */
        $path = FileService::MAIN_DIR . '/' . $table . 's';
        $files = array_diff(scandir($path), array('.', '..'));

        /* Get images stored that are not in database */
        $images = array();
        foreach( $files as $file ) {
            $full_file = $path . "/" . $file;

            if (!in_array($full_file,$media_images)){
                array_push($images, $full_file);
            }
        }
        return $images;
    }

    /* Delete unused images */
    public static function delete_unused_images(){

        $deleted_books = Media::delete_unused_images_for_table(CONFIG::DBTables()->book);
        $deleted_movies = Media::delete_unused_images_for_table(CONFIG::DBTables()->movie);
        $deleted_games = Media::delete_unused_images_for_table(CONFIG::DBTables()->game);
        $deleted_users = Media::delete_unused_images_for_table(CONFIG::DBTables()->user);

        $deleted_images = array(
            "books"     => $deleted_books,
            "movies"    => $deleted_movies,
            "games"     => $deleted_games,
            "users"     => $deleted_users
        );

        return $deleted_images;
    }

    /* Delete unused images for table */
    public static function delete_unused_images_for_table($table)
    {
        /* Get media images in database */
        $media_images = Media::get_media_images();

        /* Get media images currently stored */
        $path = FileService::MAIN_DIR . '/' . $table . 's';
        $files = array_diff(scandir($path), array('.', '..'));

        /* Delete images stored that are not in database */
        $deleted_images = array();
        foreach( $files as $file ) {
            $full_file = $path . "/" . $file;

            if (!in_array($full_file,$media_images)){
                array_push($deleted_images, $full_file);
                FileService::delete_file($full_file);
            }
        }
        return $deleted_images;
    }

    /* Get all media images */
    public static function get_media_images(){
        $database = Database::instance();
        $sql = "SELECT DISTINCT image FROM " . $table . " WHERE image IS NOT NULL";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if( $result === false || $result === null) {
            APIService::response_fail("There was an error getting images.", 500);
        }
        $media_images = array();
        foreach ($result as $image){
            array_push($media_images, $image["image"]);
        }
        return $media_images;
    }

    /* Sort all media by title */
    public static function sort_all($a, $b){
        if($a->type == "book" && $b->type == "book"){
            if (!empty($a->series)){
                if (!empty($b->series)){
                    return (strtolower($a->series) == strtolower($b->series)) ? ($a->volume > $b->volume) : strtolower($a->series) > strtolower($b->series);
                }else{
                    return (strtolower($a->series) == strtolower($b->title)) ? ($a->volume > $b->volume) : strtolower($a->series) > strtolower($b->title);
                }
            }else{
                if (!empty($b->series)){
                    return (strtolower($a->title) == strtolower($b->series)) ? ($a->volume > $b->volume) : strtolower($a->title) > strtolower($b->series);
                }else{
                    return (strtolower($a->title) == strtolower($b->title)) ? ($a->volume > $b->volume) : strtolower($a->title) > strtolower($b->title);
                }
            }
        }else if ($a->type == "movie" && $b->type == "movie"){
            return (strtolower($a->title) == strtolower($b->title)) ? ($a->season > $b->season) : strtolower($a->title) > strtolower($b->title);
        }else{
            return strtolower($a->title) > strtolower($b->title);
        }
    }

    /* ========================================================== *
    * PRIVATE FUNCTIONS
    * ========================================================== */

    private static function build_media_item($table, $result){
        $media = ($table == "book") ? new Book($result) : (($table == "movie") ? new Movie($result) : new Game($result));
        return $media;
    }

    private static function build_media_array($table, $result){
        $media = array();
        foreach( $result as $row ) {
            $media[] = ($table == "book") ? new Book($row) : (($table == "movie") ? new Movie($row) : new Game($row));
        }
        return $media;
    }

}
