<?php

class Database {

    private $_db;

    private function connect() {
        if (DEBUG_LEVEL > 0)
            echo 'Connecting to database ' . DB_NAME . "<br />";
        $this -> _db = new SQLite3(DB_PATH . DB_NAME . '.db');
        $this -> _db -> exec('CREATE TABLE IF NOT EXISTS actions (id INTEGER PRIMARY KEY, name STRING, description STRING)');
        $this -> _db -> exec('CREATE TABLE IF NOT EXISTS pages (id INTEGER PRIMARY KEY, title STRING, content STRING, icon STRING, link STRING, actions STRING)');
    }

    private function disconnect() {
        $this -> _db -> close();
        if (DEBUG_LEVEL > 0)
            echo 'Disconnected from database ' . DB_NAME . "<br />";
    }

    /**
     * Save an object to the SQLite database.
     * @param: $object : the object to save.
     * 			Possible objects : Action | Page
     * Throws an exception if the input object is not supported.
     */
    public function saveToBase($object) {

        $this -> connect();
        //The input is an Action Object
        if ($object instanceof Action) {
            $this -> _db -> exec("INSERT INTO actions (id, name, description) VALUES ('" . $object -> _id . "', '" . $object -> _name . "', '" . $object -> _description . "')");
            if (DEBUG_LEVEL > 2)
                echo "Insertion into actions<br />";
            //TODO : User Log action success
            //The input is an Page Object
        } else if ($object instanceof Page) {
            $this -> _db -> exec("INSERT INTO pages (id, title, content, icon, link, actions) VALUES ('" . $object -> _id . "', '" . $object -> _title . "', '" . $object -> _content . "', '" . $object -> _icon . "', '" . $object -> _link . "' . $object -> actionsToString() . ')");
            if (DEBUG_LEVEL > 2)
                echo "Insertion into pages<br />";
            //TODO : User Log action success
        } else {
            throw new Exception('Wrong input object');
        }

        $this -> disconnect();

        if (DEBUG_LEVEL > 1)
            echo "Insertion into database!<br />";
    }

    /**
     * Load the data from the SQLite database.
     * @param: $type : the type of the object we want the max ID.
     * 			Possible types : action | page
     */
    public function loadFromBase($type) {

        $this -> connect();

        $type = strtolower($type);
        $result = null;
        switch($type) {
            case "action" :
            case "actions" :
                //Get all the Actions
                $result = $this -> _db -> query('SELECT * FROM actions');
                $actions = array();

                while ($res = $result -> fetchArray(SQLITE3_NUM)) {
                    if (!isset($res[0]))
                        continue;

                    $action = new Action($res[1], $res[2], $res[0]);
                    array_push($actions, $action);
                }
                $result = $actions;
                if (DEBUG_LEVEL > 2)
                    echo var_dump($result);
                break;
            case "page" :
            case "pages" :
                //Get all the Pages
                $result = $this -> _db -> query('SELECT * FROM pages');
                $pages = array();

                while ($res = $result -> fetchArray(SQLITE3_ASSOC)) {
                    if (!isset($res['title']))
                        continue;
                    $page = new Page($res['title'], $res['content'], $res['icon'], $res['link'], $res['id']);
                    array_push($pages, $page);
                }
                $result = $pages;
                if (DEBUG_LEVEL > 2)
                    var_dump($result);
                break;
        }

        $this -> disconnect();

        if (DEBUG_LEVEL > 1)
            echo 'Loading elements from database <br />';

        return $result;
    }

    public function removeObject($type, $id) {

        $this -> connect();

        $type = strtolower($type);
        $result = null;
        switch($type) {
            case "action" :
                $this -> _db -> exec('DELETE FROM actions WHERE id = ' . $id . ')');
                break;
            case "page" :
                $this -> _db -> exec('DELETE FROM pages WHERE id = ' . $id . ')');
                break;
        }

        $this -> disconnect();
    }

    /**
     * Return the last used ID + 1.
     * @param: $type : the type of the object we want the max ID.
     * 			Possible types : action | page
     */
    public function getNextAvailableID($type) {

        $this -> connect();

        $type = strtolower($type);
        $result = null;
        switch($type) {
            case "action" :
                $result = $this -> _db -> querySingle('SELECT MAX(id) from actions');
                break;
            case "page" :
                $result = $this -> _db -> querySingle('SELECT MAX(id) from pages');
                break;
        }

        $this -> disconnect();

        if ($result == null)
            $result = 0;

        if (DEBUG_LEVEL > 1) {
            echo 'Last id for ' . $type . ' is ' . ($result) . ' <br />';
            echo 'Next available id for ' . $type . ' is ' . ($result + 1) . ' <br />';
        }

        return $result + 1;
    }

}
?>