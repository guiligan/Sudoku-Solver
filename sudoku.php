<?php

class Sudoku {
    private $_answer        = false;
    private $_interactions  = 0;
    private $_possibilities = false;
    private $_puzzle        = null; // Holds the initial state of the puzzle
    private $_start         = 0;
    private $_time          = 0;
    private $_validated     = false;
    
    public function __construct($puzzle, $start = 0) {
        $time = microtime();
        $time = explode(' ', $time);
        $this->_time = $time[1] + $time[0];
        
        $this->_start = $start;
        $this->_puzzle = $puzzle;
        
        if (!is_array ($puzzle))
            throw new Exception ('Puzzle must be an array');
        
        if (sizeof ($this->_puzzle) != 9)
            throw new Exception ('Puzzle must contain 9 rows');
        else {
            for ($i = $this->_start; $i < 9+$this->_start; $i++) {
                if (sizeof ($this->_puzzle[$i]) != 9)
                    throw new Exception ('Puzzle must contain 9 columns');
                else {
                    for ($x = $this->_start; $x < 9+$this->_start; $x++) {
                        if (is_array ($this->_puzzle[$i][$x]) && isset ($this->_puzzle[$i][$x]['possible']) && isset ($this->_puzzle[$i][$x]['value']))
                            continue;
                        else if (is_array ($this->_puzzle[$i][$x]) && !isset ($this->_puzzle[$i][$x]['possible']) && isset ($this->_puzzle[$i][$x]['value']))
                            $this->_possibilities = true;
                        else if (is_int ($this->_puzzle[$i][$x]) && $this->_puzzle[$i][$x] >= 1 && $this->_puzzle[$i][$x] <= 9)
                            $this->_possibilities = true;
                        else if (is_null ($this->_puzzle[$i][$x]))
                            $this->_possibilities = true;
                        else
                            throw new Exception ('Each square must contain an array with possible moves and value, or must be an integer');
                    }
                }
            }
        }
        
        $this->_validated = true;
        if ($this->_possibilities === true)
            $this->_puzzle = $this->_buildPossibilities($this->_puzzle);
        
        $this->_printSudoku($this->_puzzle, -1, -1);
        if ($this->_validatePossibilities ($this->_puzzle) === false)
            throw new Exception ('Fatal error iniciating the puzzle possibilities');
        
        $this->_solve ($this->_puzzle);
    }
    
    private function _solve ($state, $row = null, $column = null) {
        if ($this->_answer !== false)
            return false;
        
        if ($row === null)
            $row = $this->_start;
        
        if ($column === null)
            $column = $this->_start;
        
        if ($row >= 9+$this->_start || $column >= 9+$this->_start)
            return false;
        
        if (is_int ($state[$row][$column]['value']) === true)
            $this->_solve ($state, $this->_getNext('row', $row, $column), $this->_getNext('column', $row, $column));
        else {
            $new_state = $state;
            foreach ($state[$row][$column]['possible'] as $move) {
                $this->_interactions++;
                $new_state[$row][$column]['value'] = $move;
                $new_state = $this->_buildPossibilities($new_state);
                
                if ($this->_validatePossibilities ($this->_puzzle) === false)
                    continue;
                
                if ($this->_checkAnswer($new_state) === true) {
                    $this->_answer = $new_state;
                    $this->_printPuzzle();
                }
                else
                    $this->_solve ($new_state, $this->_getNext('row', $row, $column), $this->_getNext('column', $row, $column));
            }
        }
    }
    
    private function _checkAnswer ($state) {
        for ($i = $this->_start; $i < 9+$this->_start; $i++)
            for ($x = $this->_start; $x < 9+$this->_start; $x++)
                if (is_null ($state[$i][$x]['value']))
                    return false;
        
        return true;
    }
    
    private function _buildPossibilities($state) {
        if ($this->_validated === false)
            throw new Exception ('Puzzle must be validated before continuing');
        
        for ($i = $this->_start; $i < 9+$this->_start; $i++) {
            for ($x = $this->_start; $x < 9+$this->_start; $x++) {
                if (is_int ($state[$i][$x]) === true || is_null ($state[$i][$x]) === true)
                    $state[$i][$x] = array ('value' => $state[$i][$x], 'possible' => array());
                else if (!isset ($state[$i][$x]['possible']) || !is_array ($state[$i][$x]['possible']))
                    $state[$i][$x]['possible'] = array();
                
                if (is_null ($state[$i][$x]['value']) === true)
                    $state[$i][$x]['possible'] = $this->_getPossibilities ($state, $i, $x);
                else
                    $state[$i][$x]['possible'] = array();
            }
        }
        
        return $state;
    }
    
    private function _getPossibilities ($state, $row, $column) {
        $_impossible_moves = array ();
        $_possible_moves = array ();
        
        for ($i = $this->_start; $i < 9+$this->_start; $i++) {
            if (!is_null ($state[$i][$column]['value']) && $i != $row)
                $_impossible_moves[] = $state[$i][$column]['value'];
            else if (!is_array ($state[$i][$column]) && !is_null ($state[$i][$column]) && $i != $row)
                $_impossible_moves[] = $state[$i][$column];
            
            if (!is_null ($state[$row][$i]['value']) && $i != $column)
                $_impossible_moves[] = $state[$row][$i]['value'];
            else if (!is_array ($state[$row][$i]) && !is_null ($state[$row][$i]) && $i != $column)
                $_impossible_moves[] = $state[$row][$i];
        }
        
        $square = $this->_getSquare ($row, $column);
        for ($i = $square['row']['start'] + $this->_start; $i <= $square['row']['end'] + $this->_start; $i++) {
            for ($x = $square['column']['start'] + $this->_start; $x <= $square['column']['end'] + $this->_start; $x++) {
                if (!is_null ($state[$i][$x]['value']) && ($i != $row || $x != $column))
                    $_impossible_moves[] = $state[$i][$x]['value'];
                else if (!is_array ($state[$i][$x]) && !is_null ($state[$i][$x]) && ($i != $row || $x != $column))
                    $_impossible_moves[] = $state[$i][$x];
            }
        }
        
        for ($i = 1; $i <= 9; $i++) {
            if (in_array ($i, $_impossible_moves) === false)
                $_possible_moves[] = $i;
        }
        
        return $_possible_moves;
    }
    
    private function _getSquare ($row, $column) {
        $a = $this->_smallSwitch (floor (($row - $this->_start)/3) + 1);
        $b = $this->_smallSwitch (floor (($column - $this->_start)/3) + 1);
        
        return array (
            'row' => array ('start' => $a[0], 'end' => $a[1]),
            'column' => array ('start' => $b[0], 'end' => $b[1])
        );
    }
    
    private function _smallSwitch ($n) {
        if ($n == 1)
            return array (0, 2);
        else if ($n == 2)
            return array (3, 5);
        else if ($n == 3)
            return array (6, 8);
    }
    
    private function _validatePossibilities ($state) {
        for ($i = $this->_start; $i < 9+$this->_start; $i++) {
            for ($x = $this->_start; $x < 9+$this->_start; $x++) {
                if (is_null ($state[$i][$x]['value']) && sizeof ($state[$i][$x]['possible']) == 0) {
                    return false;
                }
            }
        }
    }

    private function _printSudoku ($puzzle, $row, $column) {
        for ($i = $this->_start; $i < 9+$this->_start; $i++) {
            for ($x = $this->_start; $x < 9+$this->_start; $x++) {
                if ($i == $row && $x == $column)
                    echo '<b style="color: #ff0000">' . (is_int ($puzzle[$i][$x]['value']) ? $puzzle[$i][$x]['value'] : '&nbsp;&nbsp;') . ' &nbsp;&nbsp;</b>';
                else
                    echo (is_int ($puzzle[$i][$x]['value']) ? $puzzle[$i][$x]['value'] : '&nbsp;&nbsp;') . ' &nbsp;&nbsp;';
                
                if ($x == 2+$this->_start || $x == 5+$this->_start)
                    echo '| ';
            }
            echo '<br />';
            if ($i == 2+$this->_start || $i == 5+$this->_start)
                echo '--------------------------------<br />';
        }
        echo '<br /><br /><br />';
    }
    
    private function _printPuzzle () {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $this->_time), 4);
        
        if ($this->_answer === false)
            throw new Exception ('No answer found after ' . $this->_interactions . ' interactions in ' . $total_time . ' seconds.');
        
        echo 'Solution found (' . $this->_interactions . ' interactions and ' . $total_time . ' seconds): <br />';
        $this->_printSudoku ($this->_answer, -1, -1);
        exit;
    }
    
    private function _getNext($type, $row, $column) {
        $column++;
        if ($column >= 9 + $this->_start) {
            $row++;
            $column = $this->_start;
        }
        
        return ($type == 'column') ? $column : $row;
    }
}

$puzzle = array (
    1 => array (
        1 => null,
        2 => 3,
        3 => 2,
        4 => null,
        5 => null,
        6 => 9,
        7 => null,
        8 => 4,
        9 => null
    ),
    2 => array (
        1 => null,
        2 => null,
        3 => 5,
        4 => null,
        5 => null,
        6 => 1,
        7 => 2,
        8 => null,
        9 => 7
    ),
    3 => array (
        1 => null,
        2 => null,
        3 => null,
        4 => 7,
        5 => 4,
        6 => null,
        7 => null,
        8 => 8,
        9 => 5
    ),
    4 => array (
        1 => null,
        2 => null,
        3 => 8,
        4 => null,
        5 => 5,
        6 => 6,
        7 => 9,
        8 => null,
        9 => 1
    ),
    5 => array (
        1 => 6,
        2 => null,
        3 => null,
        4 => null,
        5 => null,
        6 => null,
        7 => null,
        8 => null,
        9 => 4
    ),
    6 => array (
        1 => 9,
        2 => null,
        3 => 1,
        4 => 2,
        5 => 7,
        6 => null,
        7 => 5,
        8 => null,
        9 => null
    ),
    7 => array (
        1 => 3,
        2 => 6,
        3 => null,
        4 => null,
        5 => 9,
        6 => 5,
        7 => null,
        8 => null,
        9 => null
    ),
    8 => array (
        1 => 8,
        2 => null,
        3 => 7,
        4 => 3,
        5 => null,
        6 => null,
        7 => 6,
        8 => null,
        9 => null
    ),
    9 => array (
        1 => null,
        2 => 2,
        3 => null,
        4 => 8,
        5 => null,
        6 => null,
        7 => 4,
        8 => 1,
        9 => null
    )
);


ini_set ('max_execution_time', 0);
new Sudoku ($puzzle, 1);
/**/
?>