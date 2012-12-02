Sudoku Solver
=============

This PHP script was created to solve any kind of Sudoku 9x9 puzzle. From tests made using Intel Core I3 computer, it took the script 0.2723 seconds and 55 interactions to solve an easy puzzle, and less then 2 minutes to solve an extreme 

puzzle.

The script uses some Artificial Intelligence to find the solution. First it looks for all possible answers in each empty slot, then it will start guessing the number for each slot according to previously created possibilities. After 

guessing a slot, the script checks if any available square will have no possible answer, going back if needed.

How to use
==========

The first thing to note is the necessity to set the max_execution_time of the PHP to 0, allowing it to think and solve in any amout of time:

    ini_set ('max_execution_time', 0);

To solve a puzzle, just call the Sudoku class using two parameters:
 * $puzzle: an array (explained bellow) containing the puzzle to be solved
 * $start: rather if the array starts at element 0 or 1 (or any other number)

    new Sudoku ($puzzle, $start);

The $puzzle must be a two level array, where the first level represents each row, and each value inside the second level array represents the column value. For slots without any number, if must be set to null. Inside the code there is 

an example using the following Sudoku puzzle, and next the solved problem:

`... 3 . 2 | ....... 9 | ... 4 ...`

`....... 5 | ....... 1 | 2...... 7`

`......... | 7 . 4 ... | ... 8 . 5`

`---------------------------------`

`....... 8 | ... 5 . 6 | 9 ..... 1`

`6 .......  | ......... | ....... 4`

`9 ..... 1 | 2 . 7 ... | 5 .......`

`---------------------------------`

`3 . 6 ... | ... 9 . 5 | .........`

`8 ..... 7 | 3 ....... | 6 .......`

`... 2 ... | 8 ....... | 4 . 1 ...`

Solution found (55 interactions and 0.2687 seconds): 

`7 3 2 | 5 8 9 | 1 4 6`

`4 8 5 | 6 3 1 | 2 9 7`

`1 9 6 | 7 4 2 | 3 8 5`

`---------------------`

`2 7 8 | 4 5 6 | 9 3 1`

`6 5 3 | 9 1 8 | 7 2 4`

`9 4 1 | 2 7 3 | 5 6 8`

`---------------------`

`3 6 4 | 1 9 5 | 8 7 2`

`8 1 7 | 3 2 4 | 6 5 9`

`5 2 9 | 8 6 7 | 4 1 3`
