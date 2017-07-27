<?php

/*
 Copyright 2015-2017 Cédric Levieux, Parti Pirate

This file is part of Congressus.

Congressus is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Congressus is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

class MySQLQuery {
    var $selects = array();
	var $froms = array();
	var $joins = array();
	var $wheres = array();
	var $orderBys = array();
	var $sets = array();
	var $request = null;
	var $distinct = false;

	function insert($table = null) {
	    $this->request = "INSERT";

        if ($table) {
            $this->setTable($table);
        }

	    return $this;
	}

	function update($table = null) {
	    $this->request = "UPDATE";

        if ($table) {
            $this->setTable($table);
        }

	    return $this;
	}

	function delete($table = null) {
	    $this->request = "DELETE";

        if ($table) {
            $this->setTable($table);
        }

	    return $this;
	}

    function select($table = null, $as = null) {
	    $this->request = "SELECT";

        if ($table) {
            $this->from($table, $as);
        }

	    return $this;
    }
    
    function setDistinct($distinct = true) {
    	$this->distinct = $distinct;
    }

	function set($column, $value) {
		$this->sets[] = array("column" => $column, "value" => $value);
	}

    /**
     * $column column or "*"
     * $as
     */
	function addSelect($column, $as = null) {
	    $this->select();
	    $this->selects[] = array("column" => $column, "as" => $as);

	    return $this;
	}

	function setTable($table) {
		$this->froms[]  = array("table" => $table, "as" => null);

	    return $this;
	}

	function from($table, $as = null) {
	    $this->froms[] = array("table" => $table, "as" => $as);

	    return $this;
	}

	function join($table, $join, $as = null, $leftRightNone = null) {
	    if (is_string($leftRightNone)) {
    	    switch(strtoupper($leftRightNone)) {
                case "LEFT":
                    $leftRightNone = "LEFT";
                    break;
                case "RIGHT":
                    $leftRightNone = "RIGHT";
                    break;
    	    }
	    }

	    $this->joins[] = array("table" => $table, "as" => $as, "join" => $join, "leftRightNone" => $leftRightNone);

	    return $this;
	}

	function where($where) {
	    $this->wheres[] = array("where" => $where);

	    return $this;
	}

	function orderASCBy($column) {
	    $this->orderBy($column, true);

	    return $this;
	}

	function orderDESCBy($column) {
	    $this->orderBy($column, false);

	    return $this;
	}

	function orderBy($column, $ascending = true) {
	    if (is_string($ascending)) {
    	    switch(strtoupper($ascending)) {
                case "ASC":
                    $ascending = true;
                    break;
                case "DESC":
                    $ascending = false;
                    break;
    	    }
	    }

	    $this->orderBys[] = array("column" => $column, "ascending" => $ascending);

	    return $this;
	}

	function constructRequest() {
	    $query = "";

	    switch($this->request) {
	        case "SELECT":
	            $query = $this->constructSelectRequest();
	            break;
	        case "INSERT":
	            $query = $this->constructInsertRequest();
	            break;
	        case "UPDATE":
	            $query = $this->constructUpdateRequest();
	            break;
	        case "DELETE":
	            $query = $this->constructDeleteRequest();
	            break;
	    }

	    return $query;
	}

	function constructSelectRequest() {
        $query = "";

	    $separator = "SELECT ";
	    foreach($this->selects as $select) {
	        $query .= $separator;

//            print_r($select);

	        $query .= $select["column"];
	        
	        if (isset($select["as"]) && $select["as"]) {
    	        $query .= " AS ";
    	        $query .= $select["as"];
	        }

	        $separator = ", ";
	    }

	    $separator = "\nFROM ";

	    foreach($this->froms as $from) {
	        $query .= $separator;

//            print_r($from);

	        $query .= $from["table"];
	        
	        if (isset($from["as"]) && $from["as"]) {
    	        $query .= " AS ";
    	        $query .= $from["as"];
	        }

	        $separator = ", ";
	    }

	    $separator = "\n";

	    foreach($this->joins as $join) {
	        $query .= $separator;

//            print_r($join);

            switch ($join["leftRightNone"]) {
                case "LEFT":
                    $query .= "LEFT JOIN ";
                    break;
                case "RIGHT":
                    $query .= "RIGHT JOIN ";
                    break;
                default:                    
                    $query .= "JOIN ";
                    break;
            }

	        $query .= $join["table"];
	        
	        if (isset($join["as"]) && $join["as"]) {
    	        $query .= " ";
    	        $query .= $join["as"];
	        }

	        if (isset($join["join"]) && $join["join"]) {
    	        $query .= " ON ";
    	        $query .= $join["join"];
	        }

	        $separator = "\n";
	    }

		$query .= $this->addWhereClauseInQuery();
/*		
	    $separator = "\nWHERE 1 = 1 \nAND ";


	    foreach($this->wheres as $where) {
	        $query .= $separator;

//            print_r($where);

            $query .= $where["where"];

	        $separator = "\nAND ";
	    }
*/

	    $separator = "\nORDER BY ";

	    foreach($this->orderBys as $orderBy) {
	        $query .= $separator;

//            print_r($orderBy);

            $query .= $orderBy["column"];
            
            if ($orderBy["ascending"]) {
                $query .= " ASC";
            }
            else {
                $query .= " DESC";
            }

	        $separator = ", ";
	    }

        return $query;
	}

	function constructInsertRequest() {
		$query = "INSERT INTO " . $this->froms[0]["table"] . "\n(";

		$separator = "";
		foreach($this->sets as $set) {
			$query .= $separator;
			$query .= $set["column"];
			$separator = ", ";
		}

		$query .= ")\nVALUES\n(";

		$separator = "";
		foreach($this->sets as $set) {
			$query .= $separator;
			$query .= $set["value"];
			$separator = ", ";
		}

		$query .= ")\n";

		return $query;
	}

	function constructUpdateRequest() {
		$query = "UPDATE " . $this->froms[0]["table"] ." SET\n";

		$separator = "";
		foreach($this->sets as $set) {
			$query .= $separator;
			$query .= $set["column"];
			$query .= " = ";
			$query .= $set["value"];
			$separator = ",\n";
		}

		$query .= $this->addWhereClauseInQuery();

		return $query;
	}

	function constructDeleteRequest() {
		$query = "DELETE FROM " . $this->froms[0]["table"];

		$query .= $this->addWhereClauseInQuery();

		return $query;
	}

	private function addWhereClauseInQuery() {
		$query = "";

	    $separator = "\nWHERE 1 = 1 \nAND ";

	    foreach($this->wheres as $where) {
	        $query .= $separator;

//            print_r($where);

            $query .= $where["where"];

	        $separator = "\nAND ";
	    }
	    
	    return $query;
	}

}

?>