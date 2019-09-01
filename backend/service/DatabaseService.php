<?php

namespace TaraCatalog\Service;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;

class DatabaseService
{
    /* Create Function */
    public static function create($table, $data)
    {
        $database = Database::instance();

        $data['created'] = $data['created']->format('Y-m-d H:i:s');
        $data['updated'] = $data['updated']->format('Y-m-d H:i:s');

        $question_marks = array_fill(0, count($data), '?');
        $sql = "INSERT INTO " . $table . " (" . implode(array_keys($data), ',' ) .") VALUES (" . implode($question_marks, ',') . ")";
        $sql = str_replace("  ", " ", $sql);
        $params = self::build_query_params($data);
        $query = $database->prepare($sql);
        $query->execute($params);
        $query->closeCursor();
        $id = $database->lastInsertId();
        return $id;
    }

    /* Get Function */
    public static function get($table, $where = null, $include_inactive = false)
    {
        $database = Database::instance();

        $where_sql = self::where_sql_string($where);
        $inactive_sql = $include_inactive ? "" : ($where_sql == "" ? " WHERE active = 1" : " AND active = 1"); // Nested ternary
        $sql = "SELECT * FROM " . $table . $where_sql . $inactive_sql;
        $sql = str_replace("  ", " ", $sql);
        $params = self::build_query_params($where);

        $query = $database->prepare($sql);
        $query->execute($params);
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();

        if($result === false) {
            return false;
        }
        return $result;
    }

    /* Get with where, order, and limit */
    public static function get_where_order_limit($table, $where = null, $order_by = null, $limit = null){
        $database = Database::instance();
        $where_sql = is_null($where) ? "" : " " . $where;
        $order_by_sql = is_null($order_by) ? "" : " " . $order_by;
        $limit_sql = is_null($limit) ? "" : " " . $limit;
        $sql = "SELECT *, @curRow := @curRow + 1 AS row_number FROM " . $table . " JOIN(SELECT @curRow := 0) r". $where_sql . $order_by_sql . $limit_sql;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $result;
    }

    /* Update Function */
    public static function update($table, $id, $data)
    {
        $database = Database::instance();

        if($data === null || count($data) === 0) {
            return false;
        }
        $data['updated'] = (new \DateTime('now'))->format('Y-m-d H:i:s');

        // Check if object with ID exists
        $check_result = DatabaseService::get($table, array("id" => $id));
        if( $check_result === false || $check_result === null) {
            return null;
        }
        $sql = self::build_update_sql($table, $data);
        $params = self::build_query_params($data, $id);

        $query = $database->prepare($sql);
        $query->execute($params);
        $query->closeCursor();
        return ($query->rowCount() > 0);
    }

    /* Set Active Function */
    public static function set_active($table, $id, $active, $where = null)
    {
        $active = ($active == true) ? 1 : 0;
        $check_active = ($active == 1) ? 0 : 1;
        $updated_at = (new \DateTime('now'))->format('Y-m-d H:i:s');

        $database = Database::instance();

        // Check if object with ID and active status exist
        $check_result = DatabaseService::get($table, array("id" => $id), $include_inactive = true);
        if( $check_result === false || $check_result === null) {
            return null;
        }

        $where_sql = self::where_sql_string($where);
        $active_sql = $where_sql == "" ? " WHERE id = ? AND active = ?" : " AND id = ? AND active = ?";
        $sql = "UPDATE " . $table . " SET active = ? AND updated = ?" . $where_sql . $active_sql;

        $active_params = array($active, $updated_at);
        $where_params = self::build_query_params($where);
        $params = array_merge($active_params, $where_params, array($id, $check_active));

        $query = $database->prepare($sql);
        $query->execute($params);
        $query->closeCursor();
        return ($query->rowCount() > 0);
    }

    /* Delete Function */
    public static function delete($table, $where)
    {
        $database = Database::instance();

        if( is_array($where) && count($where) == 0 ) {
            return false;
        }
        if($where == null || $where == "") {
            return false;
        }
        $where_sql = self::where_sql_string($where);
        $sql = "DELETE FROM " . $table . $where_sql;
        $params = self::build_query_params($where);

        $query = $database->prepare($sql);
        $query->execute($params);
        $query->closeCursor();
        return ($query->rowCount() > 0);
    }

    /* ============================================================= *
     * Private
     * ============================================================= */

    /* EX: $where = array("firstName" => "John, "lastName" => "Doe"); */
    private static function where_sql_string($where)
    {
        $where_sql = "";
        if(!is_null($where))
        {
            if(is_object($where) || is_array($where))
            {
                foreach ($where as $key => $value) {
                    if ($where_sql === ""){
                        $where_sql .= " WHERE $key = ?";
                    } else {
                        $where_sql .= " AND $key = ?";
                    }
                }
            }
        }
        return $where_sql;
    }

    private static function build_query_params($data, $id = null)
    {
        $params = array();
        if($data !== null){
            foreach($data as $key => $value) {
                if($value === null || $value === "null" || $value === NULL || $value === "NULL"){
                    $value = NULL;
                }
                $params[] = $value;
            }
        }

        if ($id !== null){
            $params[] = $id;
        }
        return $params;
    }

    private static function build_update_sql($table, $data){
        $sql = "UPDATE $table SET ";
        $i = 0;
        foreach($data as $key => $value) {
            $sql .= "$key = ";
            if( $value === false) {
                $sql .= "0";
            } else{
                $sql .= "?";
            }
            $sql .= ($i !== count($data) - 1 ? ", " : "");
            $i++;
        }
        $sql .= " WHERE id = ?";
        return $sql;
    }
}
